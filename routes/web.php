<?php

use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\Offer;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('/terms', 'legal.terms')->name('terms.page');
Route::view('/privacy', 'legal.privacy')->name('privacy.page');
Route::view('/help', 'legal.help')->name('help.page');

Route::middleware('guest')->group(function () {
    Route::get('/signup', [RegisteredUserController::class, 'create'])->name('signup.page');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login.page');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::view('/dashboard', 'dashboard.index')->name('dashboard.page');
    Route::get('/admin/transactions', function () {
        abort_unless(auth()->user()?->hasAdminAccess(), 403);

        return view('admin.transactions');
    })->name('admin.transactions.page');
    Route::view('/offers/create', 'dashboard.offer-create')->name('offers.create');
    Route::get('/offers/{offer}/start', function (Offer $offer) {
        abort_unless(
            auth()->user()?->hasAdminAccess()
            || in_array(auth()->id(), [$offer->creator_id, $offer->buyer_id, $offer->seller_id], true),
            403
        );

        return view('dashboard.offer-start', [
            'offerId' => $offer->id,
            'offerKey' => $offer->uuid,
        ]);
    })->name('offers.start');
    Route::get('/offers/{offer}', function (Offer $offer) {
        abort_unless(
            auth()->user()?->hasAdminAccess()
            || in_array(auth()->id(), [$offer->creator_id, $offer->buyer_id, $offer->seller_id], true),
            403
        );

        return view('dashboard.offer', [
            'offerId' => $offer->id,
            'offerKey' => $offer->uuid,
        ]);
    })->name('offers.show');
    Route::get('/transactions/{transaction}', function (Transaction $transaction) {
        abort_unless(
            auth()->user()?->hasAdminAccess()
            || in_array(auth()->id(), [$transaction->buyer_id, $transaction->seller_id], true),
            403
        );

        return view('dashboard.transaction', [
            'transactionId' => $transaction->id,
            'transactionKey' => $transaction->uuid,
        ]);
    })->name('transactions.show');
    Route::get('/transactions/{transaction}/payment', function (Transaction $transaction) {
        abort_unless(
            auth()->user()?->hasAdminAccess()
            || in_array(auth()->id(), [$transaction->buyer_id, $transaction->seller_id], true),
            403
        );

        return view('dashboard.transaction-payment', [
            'transactionId' => $transaction->id,
            'transactionKey' => $transaction->uuid,
        ]);
    })->name('transactions.payment');
    Route::get('/transactions/{transaction}/created', function (Transaction $transaction) {
        abort_unless(
            auth()->user()?->hasAdminAccess()
            || in_array(auth()->id(), [$transaction->buyer_id, $transaction->seller_id], true),
            403
        );

        return view('dashboard.transaction-created', [
            'transactionId' => $transaction->id,
            'transactionKey' => $transaction->uuid,
        ]);
    })->name('transactions.created');

    Route::prefix('api')->group(function () {
        Route::get('/me', CurrentUserController::class);
        Route::get('/dashboard', DashboardController::class);

        Route::get('/offers', [OfferController::class, 'index']);
        Route::post('/offers', [OfferController::class, 'store']);
        Route::get('/offers/{offer}', [OfferController::class, 'show']);
        Route::patch('/offers/{offer}', [OfferController::class, 'update']);
        Route::post('/offers/{offer}/accept', [OfferController::class, 'accept']);

        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/admin/transactions', [TransactionController::class, 'adminIndex']);
        Route::get('/admin/transactions/{transaction}', [TransactionController::class, 'adminShow']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::patch('/transactions/{transaction}', [TransactionController::class, 'update']);
        Route::post('/transactions/{transaction}/disbursement', [TransactionController::class, 'saveDisbursement']);
        Route::post('/transactions/{transaction}/wallet', [TransactionController::class, 'createWallet']);
        Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve']);
        Route::post('/transactions/{transaction}/confirm-payment', [TransactionController::class, 'confirmPayment']);
        Route::post('/transactions/{transaction}/release', [TransactionController::class, 'release']);

        Route::post('/transactions/{transaction}/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    });
});
