<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderItemResource;
use App\Filament\Resources\PaymentResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ... actions yang sudah ada ...

            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->fillForm()),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Mutate data before fill (saat load)
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['customer_type'])) {
            $data['customer_type'] = !empty($data['customer_id']) ? 'member' : 'guest';
        }
        return $data;
    }

    /**
     * Mutate data before save
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Track changes
        $oldStatus = $this->record->status;
        $newStatus = $data['status'] ?? $oldStatus;

        if ($oldStatus !== $newStatus) {
            $data['_status_changed'] = true;
            $data['_old_status'] = $oldStatus;
            $data['_new_status'] = $newStatus;
        }

        $oldPaymentStatus = $this->record->payment_status;
        $newPaymentStatus = $data['payment_status'] ?? $oldPaymentStatus;

        if ($oldPaymentStatus !== $newPaymentStatus) {
            $data['_payment_status_changed'] = true;
        }

        // Clean up customer data
        if (($data['customer_type'] ?? 'member') === 'guest') {
            $data['customer_id'] = null;
        } else {
            $data['guest_name'] = null;
            $data['guest_phone'] = null;
            $data['guest_address'] = null;
        }

        return $data;
    }

    /**
     * CRITICAL: After save, recalculate prices
     */
    protected function afterSave(): void
    {
        $order = $this->record;
        $data = $this->data;

        // PENTING: Tunggu dan refresh order dengan relations
        sleep(1);
        $order = $order->fresh(['orderItems', 'customer', 'outlet', 'service']);

        if (!$order) {
            return;
        }

        // Recalculate prices berdasarkan order items terbaru
        $this->recalculateOrderPrices($order);

        // Status change notifications
        if (isset($data['_status_changed'])) {
            if ($data['_new_status'] === 'completed' && $order->payment_status === 'pending') {
                Notification::make()
                    ->title('⚠️ Payment Status Alert')
                    ->body('Order is marked as completed but payment is still pending!')
                    ->warning()
                    ->persistent()
                    ->send();
            }

            Notification::make()
                ->title('Status Updated')
                ->body("Order status changed from {$data['_old_status']} to {$data['_new_status']}")
                ->info()
                ->send();
        }

        // Auto-create payment if needed
        if (isset($data['_payment_status_changed']) && $order->payment_status === 'paid') {
            $existingPayment = $order->payments()->where('status', 'success')->first();

            if (!$existingPayment) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->final_price,
                    'gateway' => $order->payment_gateway,
                    'status' => 'success',
                    'paid_at' => now(),
                    'transaction_id' => 'TRX-' . str_pad($order->id, 8, '0', STR_PAD_LEFT) . '-' . time(),
                    'notes' => 'Auto-created when order payment status changed to paid',
                ]);

                Notification::make()
                    ->title('✅ Payment Record Created')
                    ->body('Payment record has been automatically created')
                    ->success()
                    ->send();
            }
        }

        // Courier reminder
        if (in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']) && !$order->courier_id) {
            Notification::make()
                ->title('📋 Reminder')
                ->body('This order requires courier assignment')
                ->warning()
                ->send();
        }

        // Reward notification
        if ($order->wasChanged('status') && $order->status === 'completed' && 
            $order->customer_id && $order->customer_type === 'member' && 
            !$order->is_free_service) {
            
            $customer = $order->customer;
            if ($customer) {
                Notification::make()
                    ->title('🎁 Reward System Active')
                    ->body("Customer {$customer->name} has {$customer->available_coupons} stamp(s)")
                    ->info()
                    ->send();
            }
        }
    }

    /**
     * Recalculate semua harga order
     */
    protected function recalculateOrderPrices($order): void
    {
        // 1. Hitung base price dari order items
        $basePrice = 0;
        $totalWeight = 0;

        foreach ($order->orderItems as $item) {
            $basePrice += $item->subtotal ?? 0;
            $totalWeight += $item->weight ?? 0;
        }

        // 2. Apply multipliers
        $speedMultiplier = match($order->service_speed) {
            'express' => 1.5,
            'same_day' => 2.0,
            default => 1.0,
        };

        $deliveryMultiplier = match($order->delivery_method) {
            'pickup' => 1.2,
            'delivery' => 1.2,
            'pickup_delivery' => 1.4,
            default => 1.0,
        };

        $totalPrice = $basePrice * $speedMultiplier * $deliveryMultiplier;

        // 3. Apply discount
        $discountAmount = 0;
        $discountType = null;
        $finalPrice = $totalPrice;

        if ($order->is_free_service) {
            $discountAmount = $totalPrice;
            $finalPrice = 0;
            $discountType = 'free_service';
        } elseif ($order->coupon_id) {
            $coupon = \App\Models\Coupon::find($order->coupon_id);
            if ($coupon && method_exists($coupon, 'calculateDiscount')) {
                $discountAmount = $coupon->calculateDiscount($totalPrice);
                $finalPrice = max(0, $totalPrice - $discountAmount);
                $discountType = 'coupon';
            }
        }

        // 4. Update order
        $order->updateQuietly([
            'total_weight' => $totalWeight,
            'base_price' => round($basePrice, 2),
            'total_price' => round($totalPrice, 2),
            'discount_amount' => round($discountAmount, 2),
            'discount_type' => $discountType,
            'final_price' => round($finalPrice, 2),
        ]);

        $order->refresh();

        \Log::info("Order #{$order->id} prices updated", [
            'base_price' => $basePrice,
            'total_price' => $totalPrice,
            'final_price' => $finalPrice,
        ]);
    }

    /**
     * Redirect untuk refresh table
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Order Updated')
            ->body('Order and prices have been recalculated successfully.');
    }
}