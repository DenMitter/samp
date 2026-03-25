<?php

namespace Tests\Feature;

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
            ->assertJsonPath('transaction.payment_status', 'paid');

        $this->withToken($buyerLogin['token'])
            ->postJson("/api/transactions/{$transactionId}/approve")
            ->assertOk()
            ->assertJsonPath('transaction.status', 'approved');

        $this->withToken($sellerLogin['token'])
            ->postJson("/api/transactions/{$transactionId}/release")
            ->assertOk()
            ->assertJsonPath('transaction.payment_status', 'released');

        $this->withToken($buyerLogin['token'])
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('stats.offers_total', 1)
            ->assertJsonPath('stats.transactions_total', 1)
            ->assertJsonPath('stats.payments_total', 1);
    }
}
