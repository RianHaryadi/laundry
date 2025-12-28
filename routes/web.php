<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;

Route::redirect('/', '/admin/login');
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
        ->name('orders.invoice');
    Route::get('/orders/{order}/invoice/download', [OrderInvoiceController::class, 'download'])
        ->name('orders.invoice.download');
    Route::get('/orders/{order}/invoice/print', [OrderInvoiceController::class, 'print'])
        ->name('orders.invoice.print');
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
Route::post('/tracking/search', [TrackingController::class, 'search'])->name('tracking.search');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/booking', [BookingController::class, 'index'])->name('booking');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
