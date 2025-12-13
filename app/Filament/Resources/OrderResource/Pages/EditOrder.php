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
            Actions\Action::make('view_items')
                ->label('View Items')
                ->icon('heroicon-m-list-bullet')
                ->color('info')
                ->badge(fn () => $this->record->orderItems()->count())
                ->url(fn () => OrderItemResource::getUrl('index'))
                ->openUrlInNewTab(),

            Actions\Action::make('view_payments')
                ->label('View Payments')
                ->icon('heroicon-m-credit-card')
                ->color('success')
                ->badge(fn () => $this->record->payments()->count())
                ->url(fn () => PaymentResource::getUrl('index'))
                ->openUrlInNewTab(),

            Actions\Action::make('add_item')
                ->label('Add Item')
                ->icon('heroicon-m-plus-circle')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('service_id')
                        ->label('Service')
                        ->options(fn () => Service::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if ($state) {
                                $service = Service::find($state);
                                if ($service) {
                                    $set('price', $service->base_price);
                                }
                            }
                        }),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->minValue(1)
                        ->reactive()
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                            if ($state && $get('price')) {
                                $set('subtotal', $state * $get('price'));
                            }
                        }),

                    Forms\Components\TextInput::make('weight')
                        ->label('Weight (kg)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix('kg')
                        ->minValue(0),

                    Forms\Components\TextInput::make('price')
                        ->label('Unit Price')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                            if ($state && $get('quantity')) {
                                $set('subtotal', $state * $get('quantity'));
                            }
                        }),

                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->placeholder('Optional notes...'),
                ])
                ->action(function (array $data) {
                    OrderItem::create([
                        'order_id' => $this->record->id,
                        'service_id' => $data['service_id'],
                        'quantity' => $data['quantity'],
                        'weight' => $data['weight'] ?? null,
                        'price' => $data['price'],
                        'subtotal' => $data['subtotal'],
                        'notes' => $data['notes'] ?? null,
                    ]);

                    $this->updateOrderTotals();

                    Notification::make()
                        ->title('Item Added Successfully')
                        ->body("Item added to order #{$this->record->id}")
                        ->success()
                        ->send();
                })
                ->modalWidth('xl'),

            Actions\Action::make('create_payment')
                ->label('Create Payment')
                ->icon('heroicon-m-banknotes')
                ->color('warning')
                ->visible(fn () => $this->record->payment_status !== 'paid')
                ->form([
                    Forms\Components\Select::make('gateway')
                        ->label('Payment Method')
                        ->options([
                            'cash' => '💵 Cash',
                            'bank_transfer' => '🏦 Bank Transfer',
                            'credit_card' => '💳 Credit Card',
                            'debit_card' => '💳 Debit Card',
                            'e_wallet' => '📱 E-Wallet',
                            'qris' => '📲 QRIS',
                            'midtrans' => '🌐 Midtrans',
                            'xendit' => '🌐 Xendit',
                            'paypal' => '🌐 PayPal',
                            'other' => '❓ Other',
                        ])
                        ->default($this->record->payment_gateway)
                        ->required()
                        ->native(false)
                        ->searchable(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Payment Amount')
                        ->numeric()
                        ->prefix('Rp')
                        ->default($this->record->total_price)
                        ->required()
                        ->minValue(0),

                    Forms\Components\Select::make('status')
                        ->label('Payment Status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'success' => 'Success',
                            'failed' => 'Failed',
                        ])
                        ->default('success')
                        ->required(),

                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label('Payment Date')
                        ->default(now())
                        ->seconds(false),

                    Forms\Components\Textarea::make('notes')
                        ->label('Payment Notes')
                        ->rows(3)
                        ->placeholder('Additional payment notes...'),
                ])
                ->action(function (array $data) {
                    Payment::create([
                        'order_id' => $this->record->id,
                        'amount' => $data['amount'],
                        'gateway' => $data['gateway'],
                        'status' => $data['status'],
                        'paid_at' => $data['paid_at'] ?? null,
                        'transaction_id' => 'TRX-' . str_pad($this->record->id, 8, '0', STR_PAD_LEFT) . '-' . time(),
                        'notes' => $data['notes'] ?? null,
                    ]);

                    if ($data['status'] === 'success') {
                        $this->record->update(['payment_status' => 'paid']);
                    }

                    Notification::make()
                        ->title('Payment Created Successfully')
                        ->body("Payment record created for order #{$this->record->id}")
                        ->success()
                        ->send();
                })
                ->modalWidth('lg'),

            Actions\ViewAction::make(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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

        return $data;
    }

    protected function afterSave(): void
    {
        $order = $this->record;
        $data = $this->data;

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

        if (isset($data['_payment_status_changed']) && $order->payment_status === 'paid') {
            $existingPayment = $order->payments()->where('status', 'success')->first();

            if (!$existingPayment) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
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

        if (in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']) && !$order->courier_id) {
            Notification::make()
                ->title('📋 Reminder')
                ->body('This order requires courier assignment')
                ->warning()
                ->send();
        }

        // ========================================
        // 🔥 Reward Coupon Logic (1 order = 1 stamp)
        // ========================================
        if ($order->status === 'completed' && $order->payment_status === 'paid') {
            $this->handleRewardCoupon();
        }
    }

    protected function updateOrderTotals(): void
    {
        $order = $this->record;

        $orderItems = $order->orderItems;

        $totalWeight = $orderItems->sum('weight') ?: $order->total_weight;
        $itemsSubtotal = $orderItems->sum('subtotal');

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

        if ($order->service) {
            $basePrice = $totalWeight * $order->service->price_per_kg;
        } else {
            $basePrice = $itemsSubtotal;
        }

        $totalPrice = $basePrice * $speedMultiplier * $deliveryMultiplier;

        $order->update([
            'total_weight' => $totalWeight,
            'base_price' => round($basePrice),
            'total_price' => round($totalPrice),
        ]);

        Notification::make()
            ->title('Order Totals Updated')
            ->body("New total: Rp " . number_format($totalPrice, 0, ',', '.'))
            ->info()
            ->send();
    }

    // ===========================
    // 🎁 REWARD COUPON FUNCTION
    // ===========================
    protected function handleRewardCoupon()
    {
        $order = $this->record;

        if (!$order->customer_id) {
            return;
        }

        if ($order->coupon_earned) {
            return;
        }

        $paidCount = Order::where('customer_id', $order->customer_id)
            ->where('payment_status', 'paid')
            ->where('status', 'completed')
            ->count();

        // Tandai order ini sudah dapat reward
        $order->update(['coupon_earned' => true]);

        // Jika mencapai 6 stamp → buat kupon gratis
        if ($paidCount % 6 === 0) {
            $coupon = \App\Models\Coupon::create([
                'customer_id' => $order->customer_id,
                'code' => 'FREE-' . strtoupper(uniqid()),
                'discount_type' => 'percent',
                'discount_value' => 100,
                'max_usage' => 1,
                'is_active' => true,
                'expires_at' => now()->addMonths(3),
            ]);

            Notification::make()
                ->title('🎉 Reward Unlocked!')
                ->body("Customer mendapatkan kupon FREE LAUNDRY: {$coupon->code}")
                ->success()
                ->send();
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Order updated successfully';
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Order updated')
            ->body('The order has been saved successfully.');
    }
}
