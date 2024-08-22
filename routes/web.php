<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/stripe-intent', [PaymentController::class, 'stripeIntent'])->name('stripe-intent');
Route::post('/submit-intent', [PaymentController::class, 'submitIntent'])->name('submit-intent');





// actual implementation
Route::get('/stripe-payment', [PaymentController::class, 'showPaymentForm'])->name('stripe.payment.form');
Route::post('/stripe-payment', [PaymentController::class, 'initiatePayment'])->name('stripe.payment');



Route::post('/payment/stripe-hosted', [PaymentController::class, 'stripeHosted'])->name('stripe.hosted.payment');
Route::get('/stripe-payment/confirm', [PaymentController::class, 'confirmPayment'])->name('stripe.payment.confirm');
Route::get('/stripe-payment/failed', [PaymentController::class, 'failedPayment'])->name('stripe.payment.failed');


Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])->name('stripe.webhook');

require __DIR__.'/auth.php';
