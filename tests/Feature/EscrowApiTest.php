<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_basic_escrow_flow(): void
    {
        $buyer = User::factory()->create([
            'email' => 'buyer@test.local',
            'password' => 'password123',
        ]);

        $seller = User::factory()->create([
            'email' => 'seller@test.local',
            'password' => 'password123',
        ]);

        $buyerLogin = $this->postJson('/api/login', [
            'email' => $buyer->email,
            'password' => 'password123',
        ])->assertOk()->json();

        $sellerLogin = $this->postJson('/api/login', [
            'email' => $seller->email,
            'password' => 'password123',
        ])->assertOk()->json();

        $offer = $this->withToken($buyerLogin['token'])
            ->postJson('/api/offers', [
                'title' => 'Example domain sale',
                'description' => 'Escrow transaction',
                'asset_type' => 'domain',
                'currency' => 'USD',
                'amount' => 800,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'meta' => [
                    'seller_email' => $seller->email,
                ],
            ])
            ->assertCreated()
            ->json();

        $accept = $this->withToken($sellerLogin['token'])
            ->postJson("/api/offers/{$offer['id']}/accept")
            ->assertOk()
            ->json();

        $transactionId = $accept['transaction']['id'];

        $this->withToken($buyerLogin['token'])
            ->postJson("/api/transactions/{$transactionId}/payments", [
                'amount' => 800,
                'provider' => 'manual',
                'external_reference' => 'PAY-001',
            ])
            ->assertCreated()
            ->assertJsonPath('transaction.payment_status', 'pending_confirmation');

        $this->withToken($buyerLogin['token'])
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('stats.offers_total', 1)
            ->assertJsonPath('stats.transactions_total', 1)
            ->assertJsonPath('stats.payments_total', 1);
    }

    public function test_login_sets_auth_cookie_for_web_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'member@test.local',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $login->assertOk()
            ->assertCookie('escrow_mvp_auth');

        $token = $login->json('token');

        $this->withCookie('escrow_mvp_auth', $token)
            ->get('/login')
            ->assertRedirect('/dashboard');

        $this->withCookie('escrow_mvp_auth', $token)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_transaction_api_route_binding_supports_uuid(): void
    {
        $buyer = User::factory()->create([
            'email' => 'buyer-uuid@test.local',
            'password' => 'password123',
        ]);

        $seller = User::factory()->create([
            'email' => 'seller-uuid@test.local',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $buyer->email,
            'password' => 'password123',
        ])->assertOk();

        $transaction = Transaction::query()->create([
            'uuid' => '11111111-2222-3333-4444-555555555555',
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'reference' => 'TX-TESTUUID1',
            'currency' => 'USD',
            'amount' => 800,
            'inspection_period_days' => 3,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'meta' => [],
        ]);

        $this->withToken($login->json('token'))
            ->getJson("/api/transactions/{$transaction->uuid}")
            ->assertOk()
            ->assertJsonPath('id', $transaction->id)
            ->assertJsonPath('uuid', $transaction->uuid);
    }
}
