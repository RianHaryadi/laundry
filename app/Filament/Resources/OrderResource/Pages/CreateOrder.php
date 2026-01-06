<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * Mutate data SEBELUM create
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-detect customer type
        if (empty($data['customer_type'])) {
            $data['customer_type'] = !empty($data['customer_id']) ? 'member' : 'guest';
        }

        // Clean up berdasarkan customer type
        if ($data['customer_type'] === 'guest') {
            $data['customer_id'] = null;
        } else {
            $data['guest_name'] = null;
            $data['guest_phone'] = null;
            $data['guest_address'] = null;
        }

        return $data;
    }

    /**
     * CRITICAL: Setelah order dan items dibuat, recalculate SEMUA harga
     */
    protected function afterCreate(): void
    {
        $order = $this->record;

        // Tunggu sampai semua order items tersimpan
        sleep(1); // Delay 1 detik untuk memastikan relationships sudah ready

        // Refresh order dengan relations
        $order = $order->fresh(['orderItems', 'customer', 'outlet', 'service']);

        if (!$order) {
            return;
        }

        // PENTING: Hitung ulang SEMUA harga berdasarkan order items yang sudah ada
        $this->recalculateOrderPrices($order);

        Notification::make()
            ->success()
            ->title('Order Created Successfully')
            ->body("Order #{$order->id} | Total: Rp " . number_format($order->final_price, 0, ',', '.'))
            ->send();
    }

    /**
     * Recalculate semua harga order berdasarkan order items
     */
    protected function recalculateOrderPrices($order): void
    {
        // 1. Hitung base price dari semua order items
        $basePrice = 0;
        $totalWeight = 0;

        foreach ($order->orderItems as $item) {
            $basePrice += $item->subtotal ?? 0;
            $totalWeight += $item->weight ?? 0;
        }

        // 2. Apply multipliers (speed & delivery)
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

        // 3. Apply discount (free service atau coupon)
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

        // 4. Update order dengan harga yang benar
        $order->updateQuietly([
            'total_weight' => $totalWeight,
            'base_price' => round($basePrice, 2),
            'total_price' => round($totalPrice, 2),
            'discount_amount' => round($discountAmount, 2),
            'discount_type' => $discountType,
            'final_price' => round($finalPrice, 2),
        ]);

        // 5. Refresh order lagi untuk memastikan data tersimpan
        $order->refresh();

        \Log::info("Order #{$order->id} prices recalculated", [
            'base_price' => $basePrice,
            'total_price' => $totalPrice,
            'final_price' => $finalPrice,
        ]);
    }

    /**
     * Redirect ke index setelah create agar table refresh
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null; // Sudah ada di afterCreate
    }
}