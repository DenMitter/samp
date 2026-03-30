<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $offers = Offer::query()
            ->where(fn ($query) => $query
                ->where('creator_id', $request->user()->id)
                ->orWhere('buyer_id', $request->user()->id)
                ->orWhere('seller_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json($offers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'asset_type' => ['required', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:1'],
            'buyer_id' => ['nullable', 'integer', 'exists:users,id'],
            'seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'expires_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ]);

        if (isset($data['seller_id'])) {
            $this->resolveSellerById((int) $data['seller_id']);
        }

        $offer = Offer::create([
            ...$data,
            'uuid' => (string) Str::uuid(),
            'creator_id' => $request->user()->id,
            'status' => 'draft',
        ]);

        return response()->json($offer, 201);
    }

    public function show(Request $request, Offer $offer)
    {
        abort_unless($this->canAccess($request, $offer), 403);

        return response()->json($offer->load(['creator', 'buyer', 'seller', 'transaction']));
    }

    public function update(Request $request, Offer $offer)
    {
        abort_unless($this->canAccess($request, $offer), 403);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'asset_type' => ['sometimes', 'required', 'string', 'max:100'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:1'],
            'meta' => ['sometimes', 'array'],
            'meta.seller_email' => ['nullable', 'email'],
            'meta.seller_phone' => ['nullable', 'string', 'max:50'],
        ]);

        if (array_key_exists('meta', $data)) {
            $data['meta'] = array_filter([
                ...($offer->meta ?? []),
                ...($data['meta'] ?? []),
            ], static fn ($value) => $value !== null);
        }

        $sellerEmail = mb_strtolower(trim((string) data_get($data, 'meta.seller_email', '')));

        if ($sellerEmail !== '') {
            if ($sellerEmail === mb_strtolower((string) $request->user()->email)) {
                throw ValidationException::withMessages([
                    'seller_email' => 'Нельзя предложить сделку самому себе.',
                ]);
            }

            $data['seller_id'] = $this->resolveSellerByEmail($sellerEmail)->id;
        }

        $offer->update($data);

        return response()->json($offer->fresh()->load(['creator', 'buyer', 'seller', 'transaction']));
    }

    public function accept(Request $request, Offer $offer)
    {
        abort_unless($this->canAccess($request, $offer), 403);

        if ($offer->status === 'accepted') {
            return response()->json([
                'message' => 'Offer already accepted.',
                'offer' => $offer->load('transaction'),
            ]);
        }

        $sellerEmail = mb_strtolower(trim((string) data_get($offer->meta, 'seller_email', '')));
        $seller = null;

        if ($sellerEmail !== '') {
            $seller = $this->resolveSellerByEmail($sellerEmail, false);
        }

        if (! $seller) {
            throw ValidationException::withMessages([
                'seller_email' => 'Укажите существующий аккаунт продавца перед запуском сделки.',
            ]);
        }

        if ($seller->id === $offer->creator_id) {
            throw ValidationException::withMessages([
                'seller_email' => 'Нельзя предложить сделку самому себе.',
            ]);
        }

        $transaction = DB::transaction(function () use ($offer, $seller) {
            $offer->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'buyer_id' => $offer->creator_id,
                'seller_id' => $seller->id,
            ]);

            return Transaction::create([
                'uuid' => (string) Str::uuid(),
                'offer_id' => $offer->id,
                'buyer_id' => $offer->creator_id,
                'seller_id' => $seller->id,
                'reference' => 'TX-'.strtoupper(Str::random(10)),
                'currency' => $offer->currency,
                'amount' => $offer->amount,
                'inspection_period_days' => (int) data_get($offer->meta, 'inspection_period_days', 3),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'meta' => $offer->meta,
            ]);
        });

        return response()->json([
            'message' => 'Offer accepted and transaction created.',
            'offer' => $offer->fresh(),
            'transaction' => $transaction,
        ]);
    }

    private function canAccess(Request $request, Offer $offer): bool
    {
        return in_array($request->user()->id, [
            $offer->creator_id,
            $offer->buyer_id,
            $offer->seller_id,
        ], true);
    }

    private function resolveSellerByEmail(string $email, bool $failWhenMissing = true): ?User
    {
        $seller = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();

        if (! $seller || ! $seller->hasSellerAccount()) {
            if (! $failWhenMissing) {
                return null;
            }

            throw ValidationException::withMessages([
                'seller_email' => 'Аккаунт продавца с таким email не найден.',
            ]);
        }

        return $seller;
    }

    private function resolveSellerById(int $sellerId): User
    {
        $seller = User::query()->findOrFail($sellerId);

        if (! $seller->hasSellerAccount()) {
            throw ValidationException::withMessages([
                'seller_id' => 'Выбранный пользователь не является продавцом.',
            ]);
        }

        return $seller;
    }
}
