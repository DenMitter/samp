<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        abort_unless($this->canAccess($request, $transaction), 403);

        abort_unless((int) $request->user()->id === (int) $transaction->buyer_id, 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'provider' => ['nullable', 'string', 'max:50'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ]);

        $provider = $data['provider'] ?? 'manual';
        $paymentMeta = $data['meta'] ?? [];
        $externalReference = $data['external_reference'] ?? null;

        if ($provider === 'escrow_wallet') {
            $wallet = data_get($transaction->meta, 'escrow_wallet');

            if (! is_array($wallet) || empty($wallet['address'])) {
                throw ValidationException::withMessages([
                    'payment' => 'Сначала создайте кошелёк Escrow.',
                ]);
            }

            $requiredAmount = $this->calculateRequiredWalletAmount($transaction);
            $quote = $this->buildUsdtRequirement($requiredAmount);
            $balanceUnits = $this->fetchUsdtBalanceUnits((string) $wallet['address']);

            if (bccomp($balanceUnits, $quote['required_units'], 0) < 0) {
                throw ValidationException::withMessages([
                    'payment' => sprintf(
                        'На кошелёк ещё не поступила достаточная сумма. Требуется не менее %s USDT.',
                        $quote['required_usdt']
                    ),
                ]);
            }

            $paymentMeta = [
                ...$paymentMeta,
                'wallet_address' => $wallet['address'],
                'asset' => 'USDT',
                'network' => 'ethereum',
                'token_contract' => $quote['token_contract'],
                'token_decimals' => $quote['token_decimals'],
                'required_usdt' => $quote['required_usdt'],
                'required_units' => $quote['required_units'],
                'detected_balance_usdt' => $this->tokenUnitsToDecimal($balanceUnits, $quote['token_decimals']),
                'detected_balance_units' => $balanceUnits,
                'checked_at' => now()->toIso8601String(),
            ];

            $externalReference = (string) $wallet['address'];
        }

        $paymentAmount = $provider === 'escrow_wallet'
            ? $this->calculateRequiredWalletAmount($transaction)
            : $data['amount'];

        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payer_id' => $request->user()->id,
            'amount' => $paymentAmount,
            'currency' => $transaction->currency,
            'provider' => $provider,
            'external_reference' => $externalReference,
            'status' => 'pending_confirmation',
            'paid_at' => now(),
            'meta' => $paymentMeta ?: null,
        ]);

        $transaction->update([
            'payment_status' => 'pending_confirmation',
            'status' => 'approved',
            'meta' => array_filter([
                ...($transaction->meta ?? []),
                'payment_submitted_at' => now()->toIso8601String(),
                'payment_submitted_by_user_id' => $request->user()->id,
                'payment_confirmation_required_from_user_id' => $transaction->seller_id,
            ], static fn ($value) => $value !== null && $value !== ''),
        ]);

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => $payment,
            'transaction' => $transaction->fresh(),
        ], 201);
    }

    public function show(Request $request, Payment $payment)
    {
        abort_unless(
            $payment->payer_id === $request->user()->id || $this->canAccess($request, $payment->transaction),
            403
        );

        return response()->json($payment->load('transaction'));
    }

    private function canAccess(Request $request, Transaction $transaction): bool
    {
        return in_array($request->user()->id, [
            $transaction->buyer_id,
            $transaction->seller_id,
        ], true);
    }

    private function calculateRequiredWalletAmount(Transaction $transaction): string
    {
        $amount = (float) $transaction->amount;
        $escrowFee = max($amount * 0.024, 25);
        $processingFee = 25.0;
        $feePaidBy = strtolower((string) data_get($transaction->meta, 'fee_paid_by', 'buyer'));

        $total = $amount + $processingFee;

        if ($feePaidBy === 'buyer') {
            $total += $escrowFee;
        } elseif ($feePaidBy === 'split') {
            $total += $escrowFee / 2;
        }

        return number_format($total, 2, '.', '');
    }

    private function buildUsdtRequirement(string $usdAmount): array
    {
        $tokenDecimals = (int) env('USDT_DECIMALS', 6);
        $requiredUsdt = number_format((float) $usdAmount, 2, '.', '');
        $requiredUnits = $this->decimalToTokenUnits($requiredUsdt, $tokenDecimals);

        return [
            'token_contract' => (string) env('USDT_CONTRACT_ADDRESS', '0xdAC17F958D2ee523a2206206994597C13D831ec7'),
            'token_decimals' => $tokenDecimals,
            'required_usdt' => $requiredUsdt,
            'required_units' => $requiredUnits,
        ];
    }

    private function fetchUsdtBalanceUnits(string $address): string
    {
        $rpcUrl = (string) env('EVM_RPC_URL', 'https://ethereum-rpc.publicnode.com');
        $contract = (string) env('USDT_CONTRACT_ADDRESS', '0xdAC17F958D2ee523a2206206994597C13D831ec7');
        $encodedAddress = str_pad(strtolower(ltrim($address, '0x')), 64, '0', STR_PAD_LEFT);
        $callData = '0x70a08231'.$encodedAddress;

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_call',
                'params' => [[
                    'to' => $contract,
                    'data' => $callData,
                ], 'latest'],
                'id' => 1,
            ]);

        $result = data_get($response->json(), 'result');

        if (! $response->successful() || ! is_string($result) || ! str_starts_with($result, '0x')) {
            throw ValidationException::withMessages([
                'payment' => 'Не удалось проверить поступление средств на кошелёк.',
            ]);
        }

        $hex = substr($result, 2);
        $hex = $hex === '' ? '0' : $hex;

        return $this->hexToDecimalString($hex);
    }

    private function decimalToTokenUnits(string $amount, int $decimals): string
    {
        return bcmul($amount, bcpow('10', (string) $decimals, 0), 0);
    }

    private function tokenUnitsToDecimal(string $units, int $decimals): string
    {
        return bcdiv($units, bcpow('10', (string) $decimals, 0), $decimals);
    }

    private function hexToDecimalString(string $hex): string
    {
        $normalized = strtolower(ltrim($hex, '0'));

        if ($normalized === '') {
            return '0';
        }

        if (function_exists('gmp_init') && function_exists('gmp_strval')) {
            return gmp_strval(gmp_init($normalized, 16), 10);
        }

        $decimal = '0';

        foreach (str_split($normalized) as $character) {
            $value = (string) hexdec($character);
            $decimal = bcadd(bcmul($decimal, '16', 0), $value, 0);
        }

        return $decimal;
    }
}
