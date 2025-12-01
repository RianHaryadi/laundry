<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Service; // Pastikan model Service di-import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Order Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Selection')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                '#' . str_pad($record->id, 6, '0', STR_PAD_LEFT) . 
                                ' - ' . ($record->customer?->name ?? 'Unknown Customer')
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $order = Order::with(['service', 'customer'])->find($state);
                                    if ($order) {
                                        // Auto-fill service jika ada di order utama
                                        $set('service_id', $order->service_id);
                                        
                                        // Panggil fungsi hitung ulang (bawah) untuk update harga
                                        self::updateTotals($set, 1, $order->service?->base_price ?? 0);

                                        Notification::make()
                                            ->title('Order Information')
                                            ->body("Customer: " . ($order->customer?->name ?? '-') . " | Service: " . ($order->service?->name ?? '-'))
                                            ->info()
                                            ->send();
                                    }
                                }
                            }),

                        Forms\Components\Placeholder::make('order_info')
                            ->label('Order Details')
                            ->content(function (Forms\Get $get): string {
                                if ($get('order_id')) {
                                    $order = Order::with(['customer', 'service', 'outlet'])->find($get('order_id'));
                                    if ($order) {
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="space-y-1 text-sm">' .
                                            '<div><strong>Customer:</strong> ' . ($order->customer?->name ?? '-') . '</div>' .
                                            '<div><strong>Service:</strong> ' . ($order->service?->name ?? '-') . '</div>' .
                                            '<div><strong>Total Order:</strong> Rp ' . number_format($order->total_price ?? 0, 0, ',', '.') . '</div>' .
                                            '</div>'
                                        );
                                    }
                                }
                                return 'Select an order to see details';
                            })
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Service & Pricing')
                    ->schema([
                        // SERVICE SELECTION
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                if ($state) {
                                    $service = Service::find($state);
                                    if ($service) {
                                        $set('price', $service->base_price);
                                        // Hitung ulang subtotal saat service berubah
                                        self::updateTotals($set, $get('quantity'), $service->base_price);
                                    }
                                }
                            }),

                        // QUANTITY
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->live(onBlur: true) // Gunakan live agar langsung update
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                self::updateTotals($set, $state, $get('price'));
                            }),

                        // WEIGHT
                        Forms\Components\TextInput::make('weight')
                            ->label('Weight (kg)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('kg'),

                        // PRICE
                        Forms\Components\TextInput::make('price')
                            ->label('Unit Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                self::updateTotals($set, $get('quantity'), $state);
                            }),

                        // SUBTOTAL (Fix: Dehydrated agar tersimpan ke database)
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly() // ReadOnly lebih baik daripada disabled untuk data yg dikirim
                            ->dehydrated() // WAJIB: agar nilai dikirim ke controller/database
                            ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                                // Hitung ulang saat form edit dibuka agar tidak kosong
                                self::updateTotals($set, $get('quantity'), $get('price'));
                            }),
                    ])->columns(2),
            ]);
    }

    // Fungsi Pembantu untuk menghitung Subtotal secara konsisten
    protected static function updateTotals(Forms\Set $set, $quantity, $price)
    {
        $qty = (int) ($quantity ?? 1);
        $prc = (float) ($price ?? 0);
        $set('subtotal', $qty * $prc);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('order.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No Customer'),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->badge()
                    ->color('info')
                    ->placeholder('No Service'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->placeholder('Rp 0'), // Menangani jika kosong di tabel

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}