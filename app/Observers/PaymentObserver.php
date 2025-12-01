<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "saved" event.
     * This runs after create or update.
     */
    public function saved(Payment $payment): void
    {
        // Panggil method sinkronisasi di model Order
        // Pastikan order ter-load sebelum dipanggil
        if ($payment->order) {
            $payment->order->syncPaymentStatus();
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        // Sinkronisasi ulang status Order setelah Payment dihapus
        if ($payment->order) {
            $payment->order->syncPaymentStatus();
        }
    }
    
    // Anda juga bisa menambahkan restored dan forceDeleted jika diperlukan.
}