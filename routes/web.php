<?php

use Illuminate\Support\Facades\Route;
use Greelogix\KPayment\Http\Controllers\ResponseController;

// Ensure routes are loaded within web middleware group
Route::middleware('web')->group(function () {
    // Payment response route (CSRF exempt)
    Route::post('kpayment/response', [ResponseController::class, 'handle'])
        ->name('kpayment.response')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    Route::get('kpayment/response', [ResponseController::class, 'handle'])
        ->name('kpayment.response.get')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    // Payment success and error pages
    Route::get('payment/success', function () {
        return view('kpayment::payment.success');
    })->name('kpayment.success');

    Route::get('payment/error', function () {
        return view('kpayment::payment.error');
    })->name('kpayment.error');
});

