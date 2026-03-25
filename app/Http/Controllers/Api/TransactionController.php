<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('seller_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json($transactions);
    }

    public function adminIndex(Request $request)
    {
        abort_unless($this->isAdminRequest($request), 403);

        $transactions = Transaction::query()
            ->with(['offer', 'buyer', 'seller', 'payments'])
            ->latest()
            ->get()
            ->map(fn (Transaction $transaction) => $this->presentTransaction($transaction, $request));

        return response()->json([
            'transactions' => $transactions,
        ]);
    }

    public function adminShow(Request $request, Transaction $transaction)
    {
        abort_unless($this->isAdminRequest($request), 403);

        return response()->json([
            'transaction' => $this->presentTransaction($transaction->load(['offer', 'buyer', 'seller', 'payments']), $request),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'buyer_id' => ['required', 'integer', 'exists:users,id'],
            'seller_id' => ['required', 'integer', 'exists:users,id'],
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:1'],
            'inspection_period_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'meta' => ['nullable', 'array'],
        ]);

        $transaction = Transaction::create([
            ...$data,
            'uuid' => (string) Str::uuid(),
            'reference' => 'TX-'.strtoupper(Str::random(10)),
            'inspection_period_days' => $data['inspection_period_days'] ?? 3,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        return response()->json($transaction, 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction) || $this->isAdminRequest($request), 403);

        return response()->json($this->presentTransaction($transaction->load(['offer', 'buyer', 'seller', 'payments']), $request));
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);

        if ($transaction->status !== 'pending') {
            throw ValidationException::withMessages([
                'transaction' => 'Изменять условия можно только до согласования сделки.',
            ]);
        }

        $data = $request->validate([
            'inspection_period_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'meta' => ['nullable', 'array'],
            'meta.fee_paid_by' => ['nullable', 'string', 'in:buyer,seller,split'],
            'meta.shipping_method' => ['nullable', 'string', 'max:120'],
            'meta.shipping_paid_by' => ['nullable', 'string', 'in:buyer,seller,split'],
            'meta.modification_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $counterpartyId = (int) ($request->user()->id === $transaction->buyer_id
            ? $transaction->seller_id
            : $transaction->buyer_id);

        $meta = [
            ...($transaction->meta ?? []),
            ...($data['meta'] ?? []),
            'modification_pending' => true,
            'last_modified_by_user_id' => $request->user()->id,
            'last_modified_by_email' => $request->user()->email,
            'awaiting_confirmation_from_user_id' => $counterpartyId,
            'modification_updated_at' => now()->toIso8601String(),
        ];

        $transaction->update([
            'inspection_period_days' => $data['inspection_period_days'] ?? $transaction->inspection_period_days,
            'status' => 'pending',
            'approved_at' => null,
            'meta' => array_filter($meta, static fn ($value) => $value !== null && $value !== ''),
        ]);

        if ($transaction->offer) {
            $offerMeta = [
                ...($transaction->offer->meta ?? []),
                ...($data['meta'] ?? []),
                'inspection_period_days' => $data['inspection_period_days'] ?? $transaction->inspection_period_days,
            ];

            $transaction->offer->update([
                'meta' => array_filter($offerMeta, static fn ($value) => $value !== null && $value !== ''),
            ]);
        }

        return response()->json([
            'message' => 'Условия сделки обновлены.',
            'transaction' => $this->presentTransaction($transaction->fresh()->load(['offer', 'buyer', 'seller', 'payments']), $request),
        ]);
    }

    public function saveDisbursement(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);
        abort_unless((int) $request->user()->id === (int) $transaction->seller_id, 403);

        $data = $request->validate([
            'disbursement_method' => ['required', 'string', 'max:120'],
            'disbursement_details' => ['required', 'string', 'max:500'],
        ]);

        $transaction->update([
            'meta' => array_filter([
                ...($transaction->meta ?? []),
                'disbursement_method' => $data['disbursement_method'],
                'disbursement_details' => $data['disbursement_details'],
                'disbursement_saved_at' => now()->toIso8601String(),
                'disbursement_saved_by_user_id' => $request->user()->id,
            ], static fn ($value) => $value !== null && $value !== ''),
        ]);

        return response()->json([
            'message' => 'Способ выплаты сохранён.',
            'transaction' => $this->presentTransaction($transaction->fresh()->load(['offer', 'buyer', 'seller', 'payments']), $request),
        ]);
    }

    public function createWallet(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);
        abort_unless((int) $request->user()->id === (int) $transaction->buyer_id, 403);

        $existingWallet = data_get($transaction->meta, 'escrow_wallet');

        if (! is_array($existingWallet) || empty($existingWallet['address']) || empty($existingWallet['mnemonic_encrypted'])) {
            $wallet = $this->generateRealWallet();

            $transaction->update([
                'meta' => array_filter([
                    ...($transaction->meta ?? []),
                    'escrow_wallet' => $wallet,
                ], static fn ($value) => $value !== null && $value !== ''),
            ]);
        } else {
            $wallet = $existingWallet;
        }

        return response()->json([
            'message' => 'Кошелёк Escrow создан.',
            'wallet' => $this->presentWallet($wallet, $request, $transaction),
            'transaction' => $this->presentTransaction($transaction->fresh()->load(['offer', 'buyer', 'seller', 'payments']), $request),
        ]);
    }

    public function approve(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);

        if ($transaction->buyer_id === $transaction->seller_id) {
            throw ValidationException::withMessages([
                'transaction' => 'Нельзя принять сделку, в которой вы указаны обеими сторонами.',
            ]);
        }

        $awaitingConfirmationFrom = (int) data_get($transaction->meta, 'awaiting_confirmation_from_user_id', 0);
        if ($awaitingConfirmationFrom !== 0 && $awaitingConfirmationFrom !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'transaction' => 'Сейчас подтверждение изменений ожидается от другой стороны сделки.',
            ]);
        }

        $transaction->loadMissing('offer');

        if ($transaction->offer && $transaction->offer->creator_id === $request->user()->id) {
            throw ValidationException::withMessages([
                'transaction' => 'Нельзя принимать сделку, которую вы создали сами.',
            ]);
        }

        $transaction->update([
            'status' => 'approved',
            'approved_at' => now(),
            'meta' => array_filter([
                ...($transaction->meta ?? []),
                'modification_pending' => false,
                'awaiting_confirmation_from_user_id' => null,
                'modification_confirmed_by_user_id' => $request->user()->id,
                'modification_confirmed_at' => now()->toIso8601String(),
            ], static fn ($value) => $value !== null && $value !== ''),
        ]);

        return response()->json([
            'message' => 'Transaction approved.',
            'transaction' => $this->presentTransaction($transaction->fresh(), $request),
        ]);
    }

    public function confirmPayment(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);
        abort_unless((int) $request->user()->id === (int) $transaction->seller_id, 403);

        if (($transaction->payment_status ?? 'unpaid') !== 'pending_confirmation') {
            throw ValidationException::withMessages([
                'transaction' => 'Сейчас нет платежа, ожидающего подтверждения.',
            ]);
        }

        $transaction->payments()
            ->where('status', 'pending_confirmation')
            ->update([
                'status' => 'paid',
            ]);

        $transaction->update([
            'payment_status' => 'paid',
            'status' => 'funded',
            'meta' => array_filter([
                ...($transaction->meta ?? []),
                'payment_confirmed_at' => now()->toIso8601String(),
                'payment_confirmed_by_user_id' => $request->user()->id,
                'payment_confirmation_required_from_user_id' => null,
            ], static fn ($value) => $value !== null && $value !== ''),
        ]);

        return response()->json([
            'message' => 'Платёж подтверждён.',
            'transaction' => $this->presentTransaction($transaction->fresh()->load(['offer', 'buyer', 'seller', 'payments']), $request),
        ]);
    }

    public function release(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);

        $transaction->update([
            'status' => 'released',
            'released_at' => now(),
            'payment_status' => 'released',
        ]);

        return response()->json([
            'message' => 'Funds released.',
            'transaction' => $this->presentTransaction($transaction->fresh(), $request),
        ]);
    }

    private function canAccess(Request $request, Transaction $transaction): bool
    {
        return in_array($request->user()->id, [
            $transaction->buyer_id,
            $transaction->seller_id,
        ], true);
    }

    private function isAdminRequest(Request $request): bool
    {
        $allowedEmails = collect(explode(',', (string) env('ADMIN_EMAILS', 'admin@admin.com')))
            ->map(fn (string $item) => mb_strtolower(trim($item)))
            ->filter()
            ->values();

        return $allowedEmails->contains(mb_strtolower(trim((string) $request->user()->email)));
    }

    private function presentTransaction(Transaction $transaction, Request $request): Transaction
    {
        $meta = $transaction->meta ?? [];

        if (isset($meta['escrow_wallet']) && is_array($meta['escrow_wallet'])) {
            $meta['escrow_wallet'] = $this->presentWallet($meta['escrow_wallet'], $request, $transaction);
            $transaction->setAttribute('meta', $meta);
        }

        return $transaction;
    }

    private function presentWallet(array $wallet, Request $request, Transaction $transaction): array
    {
        $presented = [
            'address' => $wallet['address'] ?? null,
            'created_at' => $wallet['created_at'] ?? null,
            'derivation_path' => $wallet['derivation_path'] ?? "m/44'/60'/0'/0/0",
            'is_real_wallet' => true,
        ];

        $isBuyer = (int) $request->user()->id === (int) $transaction->buyer_id;
        $isAdmin = $this->isAdminRequest($request);

        if (($isBuyer || $isAdmin) && ! empty($wallet['mnemonic_encrypted'])) {
            $mnemonic = Crypt::decryptString($wallet['mnemonic_encrypted']);
            $presented['mnemonic'] = $mnemonic;
            $presented['seed'] = preg_split('/\s+/', trim($mnemonic)) ?: [];
        }

        return array_filter($presented, static fn ($value) => $value !== null && $value !== '');
    }

    private function generateRealWallet(): array
    {
        $scriptPath = base_path('scripts/generate-evm-wallet.mjs');

        $result = Process::path(base_path())
            ->timeout(15)
            ->run(['node', $scriptPath]);

        if (! $result->successful()) {
            throw ValidationException::withMessages([
                'wallet' => 'Не удалось создать реальный кошелёк.',
            ]);
        }

        $payload = json_decode($result->output(), true);

        if (! is_array($payload) || empty($payload['address']) || empty($payload['mnemonic']) || empty($payload['privateKey'])) {
            throw ValidationException::withMessages([
                'wallet' => 'Кошелёк был создан некорректно.',
            ]);
        }

        return [
            'address' => $payload['address'],
            'mnemonic_encrypted' => Crypt::encryptString($payload['mnemonic']),
            'private_key_encrypted' => Crypt::encryptString($payload['privateKey']),
            'derivation_path' => $payload['derivationPath'] ?? "m/44'/60'/0'/0/0",
            'created_at' => now()->toIso8601String(),
        ];
    }
}
