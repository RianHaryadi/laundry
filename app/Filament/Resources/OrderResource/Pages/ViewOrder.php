<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('print_invoice')
                ->label('Print Invoice')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('orders.invoice', $this->record))
                ->openUrlInNewTab(),
            
            Actions\Action::make('send_email')
                ->label('Send Email')
                ->icon('heroicon-m-envelope')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    // Send email logic here
                    \Filament\Notifications\Notification::make()
                        ->title('Email Sent')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Order ID')
                            ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                            ->badge()
                            ->color('primary'),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('d M Y, H:i')
                            ->badge()
                            ->color('gray'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'confirmed' => 'info',
                                'processing' => 'warning',
                                'ready' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'partial' => 'info',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Customer Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Customer Name')
                            ->icon('heroicon-m-user'),
                        
                        Infolists\Components\TextEntry::make('customer.phone')
                            ->label('Phone')
                            ->icon('heroicon-m-phone')
                            ->copyable(),
                        
                        Infolists\Components\TextEntry::make('customer.email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->copyable(),
                        
                        Infolists\Components\TextEntry::make('customer.address')
                            ->label('Address')
                            ->icon('heroicon-m-map-pin')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Service Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('outlet.name')
                            ->label('Outlet')
                            ->icon('heroicon-m-building-storefront'),
                        
                        Infolists\Components\TextEntry::make('service.name')
                            ->label('Service Type')
                            ->badge()
                            ->color('info'),
                        
                        Infolists\Components\TextEntry::make('service_speed')
                            ->label('Service Speed')
                            ->badge()
                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                        
                        Infolists\Components\TextEntry::make('delivery_method')
                            ->label('Delivery Method')
                            ->badge()
                            ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucwords($state, '_'))),
                        
                        Infolists\Components\TextEntry::make('payment_gateway')
                            ->label('Payment Method')
                            ->badge()
                            ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucwords($state, '_'))),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Pricing Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_weight')
                            ->label('Total Weight')
                            ->suffix(' kg')
                            ->icon('heroicon-m-scale'),
                        
                        Infolists\Components\TextEntry::make('base_price')
                            ->label('Base Price')
                            ->money('IDR'),
                        
                        Infolists\Components\TextEntry::make('service_speed')
                            ->label('Speed Charge')
                            ->formatStateUsing(function ($state) {
                                $multiplier = match($state) {
                                    'express' => 1.5,
                                    'same_day' => 2.0,
                                    default => 1.0,
                                };
                                $percentage = ($multiplier - 1) * 100;
                                return $percentage > 0 ? "+{$percentage}%" : 'Standard';
                            })
                            ->badge(),
                        
                        Infolists\Components\TextEntry::make('delivery_method')
                            ->label('Delivery Charge')
                            ->formatStateUsing(function ($state) {
                                $multiplier = match($state) {
                                    'pickup' => 1.2,
                                    'delivery' => 1.2,
                                    'pickup_delivery' => 1.4,
                                    default => 1.0,
                                };
                                $percentage = ($multiplier - 1) * 100;
                                return $percentage > 0 ? "+{$percentage}%" : 'No charge';
                            })
                            ->badge(),
                        
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Total Price')
                            ->money('IDR')
                            ->size('lg')
                            ->weight('bold')
                            ->color('success'),
                    ])
                    ->columns(5),

                Infolists\Components\Section::make('Schedule & Courier')
                    ->schema([
                        Infolists\Components\TextEntry::make('courier.name')
                            ->label('Courier')
                            ->icon('heroicon-m-user-circle')
                            ->placeholder('No courier assigned'),
                        
                        Infolists\Components\TextEntry::make('pickup_time')
                            ->label('Pickup Time')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not scheduled'),
                        
                        Infolists\Components\TextEntry::make('delivery_time')
                            ->label('Delivery Time')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not scheduled'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->delivery_method !== 'walk_in'),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Order Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->visible(fn ($record) => !empty($record->notes)),

                Infolists\Components\Section::make('Order Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('orderItems')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('service.name')
                                    ->label('Service'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('weight')
                                    ->label('Weight')
                                    ->suffix(' kg')
                                    ->placeholder('N/A'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Unit Price')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('IDR')
                                    ->weight('bold'),
                            ])
                            ->columns(5),
                    ])
                    ->collapsed(false),

                Infolists\Components\Section::make('Payment Records')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('payments')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('transaction_id')
                                    ->label('Transaction ID')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('gateway')
                                    ->label('Method')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Amount')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'success' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('paid_at')
                                    ->label('Paid At')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('Not paid'),
                            ])
                            ->columns(5),
                    ])
                    ->collapsed(false)
                    ->visible(fn ($record) => $record->payments()->count() > 0),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d M Y, H:i'),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i')
                            ->since(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}