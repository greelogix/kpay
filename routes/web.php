<?php

use Illuminate\Support\Facades\Route;
use Greelogix\KPay\Http\Controllers\ResponseController;

// Ensure routes are loaded within web middleware group
Route::middleware('web')->group(function () {
    // Payment response route (CSRF exempt)
    Route::post('kpay/response', [ResponseController::class, 'handle'])
        ->name('kpay.response')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    Route::get('kpay/response', [ResponseController::class, 'handle'])
        ->name('kpay.response.get')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    // Payment success and error pages
    Route::get('payment/success', function () {
        return view('kpay::payment.success');
    })->name('kpay.success');

    Route::get('payment/error', function () {
        return view('kpay::payment.error');
    })->name('kpay.error');
});

