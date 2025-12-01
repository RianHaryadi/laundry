<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Order History';

    protected static ?string $recordTitleAttribute = 'order_number';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-shopping-bag')
                    ->copyable()
                    ->copyMessage('Order number copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-storefront')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'processing',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? ucfirst($state) : 'N/A'
                    )
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-x-circle' => 'failed',
                        'heroicon-o-arrow-uturn-left' => 'refunded',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? ucfirst($state) : 'N/A'
                    )
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tax')
                    ->label('Tax')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('IDR')
                    ->sortable()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('semibold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? str_replace('_', ' ', ucwords($state, '_')) : 'N/A'
                    )
                    ->colors([
                        'success' => 'cash',
                        'info' => 'card',
                        'warning' => 'e_wallet',
                        'primary' => 'bank_transfer',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('points_earned')
                    ->label('Points')
                    ->suffix(' pts')
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
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
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'e_wallet' => 'E-Wallet',
                        'bank_transfer' => 'Bank Transfer',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('high_value')
                    ->label('High Value Orders (> Rp 100,000)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('total_amount', '>', 100000)
                    )
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Order')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('view_receipt')
                    ->label('Receipt')
                    ->icon('heroicon-m-document-text')
                    ->color('info')
                    ->url(fn ($record): string => 
                        route('filament.admin.resources.orders.view', $record)
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['payment_status' => 'paid']))
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->payment_status !== 'paid')
                    ->successNotificationTitle('Order marked as paid'),

                Tables\Actions\Action::make('cancel_order')
                    ->label('Cancel')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->action(fn ($record) => $record->update(['status' => 'cancelled']))
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'cancelled' && $record->status !== 'completed')
                    ->successNotificationTitle('Order cancelled'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => 
                            $records->each->update(['payment_status' => 'paid'])
                        )
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('gray')
                        ->action(function ($records) {
                            // Export logic here
                        }),
                ]),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('This customer hasn\'t placed any orders.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}