<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values if not provided
        $data['base_price'] = $data['base_price'] ?? 0;
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Create the main order
        $record = static::getModel()::create($data);

        // Auto-create payment record if payment status is paid
        if ($data['payment_status'] === 'paid') {
            \App\Models\Payment::create([
                'order_id' => $record->id,
                'amount' => $data['total_price'],
                'gateway' => $data['payment_gateway'],
                'status' => 'success',
                'paid_at' => now(),
                'transaction_id' => 'TRX-' . str_pad($record->id, 8, '0', STR_PAD_LEFT) . '-' . time(),
                'notes' => 'Auto-created from order creation',
            ]);

            Notification::make()
                ->title('Payment Record Created')
                ->body('Payment record has been automatically created')
                ->success()
                ->send();
        }

        // Auto-create order item if service is selected
        if (isset($data['service_id']) && isset($data['total_weight'])) {
            $service = \App\Models\Service::find($data['service_id']);
            
            if ($service) {
                \App\Models\OrderItem::create([
                    'order_id' => $record->id,
                    'service_id' => $data['service_id'],
                    'quantity' => 1,
                    'weight' => $data['total_weight'],
                    'price' => $data['base_price'],
                    'subtotal' => $data['base_price'],
                    'notes' => 'Auto-created from order',
                ]);

                Notification::make()
                    ->title('Order Item Created')
                    ->body('Order item has been automatically created')
                    ->success()
                    ->send();
            }
        }

        return $record;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        // Send notification
        $customerName = $order->customer ? $order->customer->name : 'Walk-In Customer';

            Notification::make()
                ->title('Order Created Successfully')
                ->body("Order #{$order->id} for {$customerName} has been created")
                ->success()
                ->duration(5000)
                ->send();

        // Additional notifications based on delivery method
        if (in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
            if (!$order->courier_id) {
                Notification::make()
                    ->title('Reminder: Assign Courier')
                    ->body('This order requires courier assignment')
                    ->warning()
                    ->duration(10000)
                    ->send();
            }
        }

        // Reminder for scheduling
        if (!$order->pickup_time && $order->delivery_method !== 'walk_in') {
            Notification::make()
                ->title('Reminder: Schedule Pickup')
                ->body('Don\'t forget to schedule pickup time')
                ->info()
                ->duration(8000)
                ->send();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Order created';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Order created')
            ->body('The order has been created successfully.');
    }
}