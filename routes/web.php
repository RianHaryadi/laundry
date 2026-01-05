<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [HomeController::class, 'allServices'])->name('services.index');
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
Route::post('/tracking/search', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/track-order', [HomeController::class, 'track'])->name('order.track');

// Guest Routes (Login & Register)
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [CustomerAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.post');
});

// Authenticated Customer Routes (using customer guard)
Route::middleware('auth:customer')->group(function () {
    
    // Logout
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    
    // Customer Home/Dashboard
    Route::get('/customer/home', [CustomerController::class, 'index'])->name('customer.home');
    Route::get('/customer/dashboard', [CustomerController::class, 'index'])->name('customer.dashboard');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Booking/Order Creation
    Route::get('/booking', [BookingController::class, 'create'])->name('booking');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    
    // Orders Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    // Order Invoice
    Route::get('/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
        ->name('orders.invoice');
    Route::get('/orders/{order}/invoice/download', [OrderInvoiceController::class, 'download'])
        ->name('orders.invoice.download');
    Route::get('/orders/{order}/invoice/print', [OrderInvoiceController::class, 'print'])
        ->name('orders.invoice.print');
});