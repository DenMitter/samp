<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('/terms', 'legal.terms')->name('terms.page');
Route::view('/privacy', 'legal.privacy')->name('privacy.page');
Route::view('/help', 'legal.help')->name('help.page');
Route::middleware('api.web.guest')->group(function () {
    Route::view('/signup', 'auth.signup')->name('signup.page');
    Route::view('/login', 'auth.login')->name('login.page');
});

Route::middleware('api.web.auth')->group(function () {
    Route::view('/dashboard', 'dashboard.index')->name('dashboard.page');
    Route::view('/admin/transactions', 'admin.transactions')->name('admin.transactions.page');
    Route::view('/offers/create', 'dashboard.offer-create')->name('offers.create');
    Route::get('/offers/{offer}/start', function (string $offer) {
        return view('dashboard.offer-start', [
            'offerId' => $offer,
        ]);
    })->name('offers.start');
    Route::get('/offers/{offer}', function (string $offer) {
        return view('dashboard.offer', [
            'offerId' => $offer,
        ]);
    })->name('offers.show');
    Route::get('/transactions/{transaction}', function (string $transaction) {
        $model = Transaction::query()
            ->where('uuid', $transaction)
            ->orWhere('id', $transaction)
            ->firstOrFail();

        return view('dashboard.transaction', [
            'transactionId' => $model->id,
            'transactionKey' => $model->uuid,
        ]);
    })->name('transactions.show');
    Route::get('/transactions/{transaction}/payment', function (string $transaction) {
        $model = Transaction::query()
            ->where('uuid', $transaction)
            ->orWhere('id', $transaction)
            ->firstOrFail();

        return view('dashboard.transaction-payment', [
            'transactionId' => $model->id,
            'transactionKey' => $model->uuid,
        ]);
    })->name('transactions.payment');
    Route::get('/transactions/{transaction}/created', function (string $transaction) {
        $model = Transaction::query()
            ->where('uuid', $transaction)
            ->orWhere('id', $transaction)
            ->firstOrFail();

        return view('dashboard.transaction-created', [
            'transactionId' => $model->id,
            'transactionKey' => $model->uuid,
        ]);
    })->name('transactions.created');
});
