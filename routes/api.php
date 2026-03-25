<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
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
