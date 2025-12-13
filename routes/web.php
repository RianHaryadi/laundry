<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderInvoiceController;

Route::redirect('/', '/admin/login');
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
        ->name('orders.invoice');
    Route::get('/orders/{order}/invoice/download', [OrderInvoiceController::class, 'download'])
        ->name('orders.invoice.download');
    Route::get('/orders/{order}/invoice/print', [OrderInvoiceController::class, 'print'])
        ->name('orders.invoice.print');
});