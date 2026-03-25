<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $offers = Offer::query()
            ->where('creator_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $transactions = Transaction::query()
            ->where(fn ($query) => $query
                ->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id))
            ->latest()
            ->take(5)
            ->get();

        $payments = Payment::query()
            ->where('payer_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'offers_total' => Offer::where('creator_id', $user->id)->count(),
                'transactions_total' => Transaction::where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id)
                    ->count(),
                'payments_total' => Payment::where('payer_id', $user->id)->count(),
            ],
            'offers' => $offers,
            'transactions' => $transactions,
            'payments' => $payments,
        ]);
    }
}
