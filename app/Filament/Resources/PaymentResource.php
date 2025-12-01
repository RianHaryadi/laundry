<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Order; // Diperlukan untuk relationship
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $modelLabel = 'Payment';

    protected static ?string $pluralModelLabel = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Order $record) => $record->formatted_id ?? '#' . str_pad($record->id, 6, '0', STR_PAD_LEFT)) // Improved Label
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select order')
                            ->helperText('Select the order for this payment')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                if ($state) {
                                    // Fetch the Order model using Order::find()
                                    $order = Order::find($state); 
                                    // Set amount only if order exists and amount field is empty
                                    if ($order && empty($get('amount'))) { 
                                        $set('amount', $order->final_price);
                                    }
                                }
                            }),
                    ]),

                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('gateway')
                            ->label('Payment Gateway')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'credit_card' => 'Credit Card',
                                'debit_card' => 'Debit Card',
                                'e_wallet' => 'E-Wallet (GoPay, OVO, Dana)',
                                'qris' => 'QRIS',
                                'midtrans' => 'Midtrans',
                                'xendit' => 'Xendit',
                                'paypal' => 'PayPal',
                                'other' => 'Other',
                            ])
                            ->native(false)
                            ->searchable()
                            ->placeholder('Select payment method')
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->maxLength(255)
                            ->placeholder('TRX-20240101-001')
                            ->helperText('External transaction reference ID')
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Payment Amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('100000')
                            ->helperText('Total payment amount')
                            ->live(onBlur: true),
                        
                        // Placeholder to show formatted amount on the fly
                        Forms\Components\Placeholder::make('formatted_amount')
                            ->label('Formatted Amount')
                            ->content(fn (Forms\Get $get): string => 
                                $get('amount') 
                                    ? 'Rp ' . number_format((float)$get('amount'), 0, ',', '.') 
                                    : 'Rp 0'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'success' => 'Success',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?Payment $record) {
                                // Set paid_at only if status becomes success AND it's a new record or paid_at is null
                                if ($state === 'success' && (!$record || $record->paid_at === null)) {
                                    $set('paid_at', now());
                                }
                                // Clear paid_at if status is not success
                                if ($state !== 'success') {
                                    // Commented out to retain previous paid_at date if status temporarily changes
                                    // $set('paid_at', null); 
                                }
                            }),
                        
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->placeholder('Select date and time')
                            ->helperText('Date and time when payment was completed')
                            ->seconds(false)
                            ->native(false)
                            ->nullable(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Payment Notes')
                            ->placeholder('Additional notes about this payment...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable()
                    // Use formatted_id accessor from Order model if available, fallback to str_pad
                    ->formatStateUsing(fn ($state, Payment $record) => $record->order->formatted_id ?? '#' . str_pad($state, 6, '0', STR_PAD_LEFT)) 
                    ->copyable()
                    ->tooltip('Click to copy')
                    // Corrected route access
                    ->url(fn ($record) => $record->order ? OrderResource::getUrl('edit', ['record' => $record->order]) : null)
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('order.customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->placeholder('N/A'),
                
                Tables\Columns\BadgeColumn::make('gateway')
                    ->label('Payment Method')
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['cash', 'paypal']),
                        'info' => fn ($state) => in_array($state, ['bank_transfer', 'qris']),
                        'warning' => fn ($state) => in_array($state, ['credit_card', 'midtrans']),
                        'primary' => fn ($state) => in_array($state, ['debit_card', 'xendit']),
                        'secondary' => fn ($state) => $state === 'e_wallet',
                        'gray' => fn ($state) => $state === 'other',
                    ])
                    ->icons([
                        'heroicon-o-banknotes' => 'cash',
                        'heroicon-o-building-library' => 'bank_transfer',
                        'heroicon-o-credit-card' => fn ($state) => in_array($state, ['credit_card', 'debit_card']),
                        'heroicon-o-device-phone-mobile' => 'e_wallet',
                        'heroicon-o-qr-code' => 'qris',
                        'heroicon-o-globe-alt' => fn ($state) => in_array($state, ['midtrans', 'xendit', 'paypal']),
                        'heroicon-o-ellipsis-horizontal' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Click to copy')
                    ->placeholder('N/A')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight('semibold')
                    ->color('success')
                    ->icon('heroicon-m-currency-dollar'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'success',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                        'info' => 'refunded',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'processing',
                        'heroicon-o-check-circle' => 'success',
                        'heroicon-o-x-circle' => 'failed',
                        'heroicon-o-minus-circle' => 'cancelled',
                        'heroicon-o-arrow-uturn-left' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Not paid yet')
                    ->description(fn ($record): ?string => 
                        $record->paid_at ? $record->paid_at->diffForHumans() : null
                    )
                    ->icon('heroicon-m-check-badge')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple()
                    ->label('Filter by Status'),
                
                Tables\Filters\SelectFilter::make('gateway')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'e_wallet' => 'E-Wallet',
                        'qris' => 'QRIS',
                        'midtrans' => 'Midtrans',
                        'xendit' => 'Xendit',
                        'paypal' => 'PayPal',
                        'other' => 'Other',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount_from')
                                    ->label('Amount from')
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('amount_to')
                                    ->label('Amount to')
                                    ->numeric()
                                    ->prefix('Rp'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['amount_from'] ?? null) {
                            $indicators[] = 'Amount from: Rp ' . number_format($data['amount_from'], 0, ',', '.');
                        }
                        
                        if ($data['amount_to'] ?? null) {
                            $indicators[] = 'Amount to: Rp ' . number_format($data['amount_to'], 0, ',', '.');
                        }
                        
                        return $indicators;
                    }),
                
                Tables\Filters\TernaryFilter::make('paid')
                    ->label('Payment Date Status')
                    ->placeholder('All payments')
                    ->trueLabel('Has Paid Date')
                    ->falseLabel('No Paid Date')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('paid_at'),
                        false: fn (Builder $query) => $query->whereNull('paid_at'),
                    ),
                
                Tables\Filters\Filter::make('paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('paid_from')
                            ->label('Paid from'),
                        Forms\Components\DatePicker::make('paid_until')
                            ->label('Paid until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['paid_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '>=', $date),
                            )
                            ->when(
                                $data['paid_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
               Tables\Actions\Action::make('mark_success')
    ->label('Mark Success')
    ->icon('heroicon-m-check-circle')
    ->color('success')
    ->action(function (Payment $record) {
        // 1. Update Payment Status
        $record->update([
            'status' => 'success',
            'paid_at' => $record->paid_at ?? now(),
        ]);

        // 2. UPDATE ORDER STATUS (LOGIC TAMBAHAN)
        if ($record->order) {
            $record->order->update(['payment_status' => 'paid']);
            
            // Opsional: Notifikasi bahwa Order juga terupdate
            \Filament\Notifications\Notification::make()
                ->title('Payment & Order Updated')
                ->body("Payment success and Order #{$record->order->id} marked as Paid.")
                ->success()
                ->send();
        }
    })
    ->requiresConfirmation()
    ->visible(fn (Payment $record) => !in_array($record->status, ['success', 'refunded'])),
                
                Tables\Actions\Action::make('mark_failed')
                    ->label('Mark Failed')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->action(fn (Payment $record) => $record->update(['status' => 'failed', 'paid_at' => null]))
                    ->requiresConfirmation()
                    // Allow marking as failed if not already success, failed, or refunded
                    ->visible(fn (Payment $record) => !in_array($record->status, ['success', 'failed', 'refunded'])) 
                    ->successNotificationTitle('Payment marked as failed'),
                
                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('refund_reason')
                            ->label('Refund Reason')
                            ->required()
                            ->placeholder('Enter reason for refund...'),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->update([
                            'status' => 'refunded',
                            'paid_at' => null, // Refunded means paid_at is no longer valid
                            'notes' => ($record->notes ? $record->notes . "\n\n" : '') . 
                                        'REFUND: ' . $data['refund_reason'] . ' (' . now()->format('d M Y H:i') . ')',
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record) => $record->status === 'success')
                    ->successNotificationTitle('Payment refunded'),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                   Tables\Actions\BulkAction::make('mark_success')
    ->label('Mark as Success')
    ->icon('heroicon-m-check-circle')
    ->color('success')
    ->action(function ($records) {
        $records->each(function (Payment $record) {
            // 1. Update Payment
            $record->update([
                'status' => 'success',
                'paid_at' => $record->paid_at ?? now(),
            ]);

            // 2. Update Order Terkait
            if ($record->order) {
                $record->order->update(['payment_status' => 'paid']);
            }
        });
    })
    ->deselectRecordsAfterCompletion()
    ->requiresConfirmation()
    ->successNotificationTitle('Payments & Orders updated successfully'),
                    
                    Tables\Actions\BulkAction::make('mark_failed')
                        ->label('Mark as Failed')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['status' => 'failed', 'paid_at' => null]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->successNotificationTitle('Payments marked as failed'),
                    
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export to CSV')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            // Implement CSV export logic here
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Create Payment'),
            ])
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Create your first payment record to get started.')
            ->emptyStateIcon('heroicon-o-credit-card')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    // You need to import the OrderResource to use getUrl()
    public static function getOrderResource()
    {
        // Assuming your OrderResource is at this namespace
        return \App\Filament\Resources\OrderResource::class; 
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        
        return match (true) {
            $count === 0 => 'success',
            $count < 5 => 'warning',
            default => 'danger',
        };
    }

    public static function getWidgets(): array
    {
        return [
            //
        ];
    }
}