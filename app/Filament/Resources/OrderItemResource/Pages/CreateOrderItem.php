<?php

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure subtotal is calculated
        if (isset($data['quantity']) && isset($data['price'])) {
            $data['subtotal'] = $data['quantity'] * $data['price'];
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $orderItem = $this->record;
        $order = $orderItem->order;

        // Recalculate order total weight and price
        $this->updateOrderTotals($order);

        // Send notifications
        Notification::make()
            ->title('Order Item Created')
            ->body("Item added to Order #{$order->id}")
            ->success()
            ->send();

        // Notify about order total update
        Notification::make()
            ->title('Order Updated')
            ->body("Order total has been recalculated: Rp " . number_format($order->fresh()->total_price, 0, ',', '.'))
            ->info()
            ->duration(5000)
            ->send();
    }

    protected function updateOrderTotals($order): void
    {
        // Calculate totals from all order items
        $orderItems = $order->orderItems;
        
        $totalWeight = $orderItems->sum('weight') ?: $order->total_weight;
        $itemsSubtotal = $orderItems->sum('subtotal');

        // Get multipliers from order
        $serviceSpeed = $order->service_speed;
        $deliveryMethod = $order->delivery_method;
        
        $speedMultiplier = match($serviceSpeed) {
            'express' => 1.5,
            'same_day' => 2.0,
            default => 1.0,
        };
        
        $deliveryMultiplier = match($deliveryMethod) {
            'pickup' => 1.2,
            'delivery' => 1.2,
            'pickup_delivery' => 1.4,
            default => 1.0,
        };

        // Calculate base price from service
        if ($order->service) {
            $basePrice = $totalWeight * $order->service->price_per_kg;
        } else {
            $basePrice = $itemsSubtotal;
        }

        // Calculate final total with multipliers
        $totalPrice = $basePrice * $speedMultiplier * $deliveryMultiplier;

        // Update order
        $order->update([
            'total_weight' => $totalWeight,
            'base_price' => round($basePrice),
            'total_price' => round($totalPrice),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Order item created';
    }
}