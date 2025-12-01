<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Order Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $pluralModelLabel = 'Orders';
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ---------------------------
                // Customer & Outlet Section
                // ---------------------------
                Section::make('Customer & Outlet Information 🧑‍💻')
                    ->schema([
                        Radio::make('customer_type')
                            ->label('Customer Type')
                            ->options([
                                'member' => 'Member (Registered)',
                                'guest' => 'Guest (Walk-in)',
                            ])
                            ->default('member')
                            ->inline()
                            ->reactive()
                            ->required()
                            ->helperText('Select customer type'),

                        Select::make('customer_id')
                            ->label('Select Member')
                            ->relationship('customer', 'name')
                            ->searchable(['name', 'email', 'phone'])
                            ->preload()
                            ->required(fn (Forms\Get $get) => $get('customer_type') === 'member')
                            ->visible(fn (Forms\Get $get) => $get('customer_type') === 'member')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $customer = \App\Models\Customer::find($state);
                                    if ($customer) {
                                        Notification::make()
                                            ->title('Customer Selected')
                                            ->body("Customer: {$customer->name} | Phone: {$customer->phone}")
                                            ->success()
                                            ->send();
                                    }
                                }
                            }),

                        TextInput::make('guest_name')
                            ->label('Guest Name')
                            ->required(fn (Forms\Get $get) => $get('customer_type') === 'guest')
                            ->visible(fn (Forms\Get $get) => $get('customer_type') === 'guest'),

                        TextInput::make('guest_phone')
                            ->label('Guest Phone')
                            ->tel()
                            ->required(fn (Forms\Get $get) => $get('customer_type') === 'guest')
                            ->visible(fn (Forms\Get $get) => $get('customer_type') === 'guest'),

                        Textarea::make('guest_address')
                            ->label('Guest Address')
                            ->rows(2)
                            ->visible(fn (Forms\Get $get) => $get('customer_type') === 'guest'),

                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable(['name', 'address'])
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // ---------------------------
                // Repeater: order_items (multi-service)
                // ---------------------------
               Repeater::make('order_items')
    ->label('Services 🧺')
    ->relationship('order_items') // Pastikan relasi di Model Order bernama 'order_items' atau 'items'
    ->reactive()
    ->schema([
        // --- 1. SERVICE SELECTION ---
        Select::make('service_id')
            ->label('Service Type')
            ->relationship('service', 'name')
            ->searchable()
            ->preload()
            ->required()
            ->reactive()
            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                if (!$state) return;
                $service = \App\Models\Service::find($state);
                if (!$service) return;

                // Isi data harga referensi
                $set('pricing_type', $service->pricing_type);
                $set('price_per_kg', $service->price_per_kg);
                $set('price_per_unit', $service->price_per_unit);
                
                // Reset inputan
                $set('weight', null);
                $set('quantity', null);
                
                // Hitung total keseluruhan order
                self::calculateFinalTotalPrice($set, $get);
            })
            ->columnSpan(2), // Biar agak lebar

        // --- 2. HIDDEN FIELDS (REFERENSI) ---
      Hidden::make('pricing_type')->dehydrated(false),
        Hidden::make('price_per_kg')->dehydrated(false),
        Hidden::make('price_per_unit')->dehydrated(false),

        // --- 3. INPUT QUANTITY / WEIGHT ---
        
        // Input Berat (KG)
        TextInput::make('weight')
            ->label('Weight (KG)')
            ->numeric()
            ->minValue(0.1)
            ->visible(fn (Forms\Get $get) => $get('pricing_type') === 'kg')
            ->reactive()
            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::calculateFinalTotalPrice($set, $get)),

        // Input Satuan (Unit)
        TextInput::make('quantity')
            ->label('Qty')
            ->numeric()
            ->minValue(1)
            ->visible(fn (Forms\Get $get) => $get('pricing_type') === 'unit')
            ->reactive()
            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::calculateFinalTotalPrice($set, $get)),

        // --- 4. DATA PENTING YANG DISIMPAN KE DB ---
        
        // HARGA (Price) - Disimpan ke kolom 'price' di tabel order_items
        Hidden::make('price')
            ->dehydrated() // Wajib simpan
            ->default(0),

        // SUBTOTAL - Disimpan ke kolom 'subtotal' di tabel order_items
        // Kita gunakan dehydrateStateUsing untuk menghitung ulang saat tombol Save ditekan
        Hidden::make('subtotal')
            ->dehydrated() // Wajib simpan
            ->default(0),

        // --- 5. TAMPILAN SUBTOTAL (HANYA VISUAL) ---
        Placeholder::make('subtotal_display')
            ->label('Est. Subtotal')
            ->content(function (Forms\Get $get, Forms\Set $set) {
                // Ambil data
                $type = $get('pricing_type');
                $kg = (float) $get('weight');
                $qty = (int) $get('quantity');
                $pKg = (float) $get('price_per_kg');
                $pUnit = (float) $get('price_per_unit');
                
                $subtotal = 0;
                $finalPrice = 0; // Harga satuan yang dipakai

                // Logika Hitung
                if ($type === 'kg' && $kg > 0) {
                    $subtotal = $kg * $pKg;
                    $finalPrice = $pKg; // Harga per kg
                } elseif ($type === 'unit' && $qty > 0) {
                    $subtotal = $qty * $pUnit;
                    $finalPrice = $pUnit; // Harga per unit
                }

                // PENTING: Set nilai ke Hidden Field agar tersimpan ke DB
                $set('subtotal', $subtotal);
                $set('price', $finalPrice); 

                return $subtotal > 0 
                    ? "Rp " . number_format($subtotal, 0, ',', '.') 
                    : "-";
            }),

    ])
    ->columns(4)
    ->minItems(1)
    ->defaultItems(1)
    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
        self::calculateFinalTotalPrice($set, $get);
    })
    ->columnSpanFull(),

                // ---------------------------
                // Service Options at Order Level
                // (affects entire order total)
                // ---------------------------
                Section::make('Service Options ⚡🚚')
                    ->schema([
                        Select::make('service_speed')
                            ->label('Service Speed')
                            ->options([
                                'regular' => 'Regular (2–3 days)',
                                'express' => 'Express (1 day) +50%',
                                'same_day' => 'Same Day +100%',
                            ])
                            ->default('regular')
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::calculateFinalTotalPrice($set, $get)),

                        Select::make('delivery_method')
                            ->label('Delivery Method')
                            ->options([
                                'walk_in' => 'Walk-in (Standard)',
                                'pickup' => 'Pickup +20%',
                                'delivery' => 'Delivery +20%',
                                'pickup_delivery' => 'Both +40%',
                            ])
                            ->default('walk_in')
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::calculateFinalTotalPrice($set, $get)),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // ---------------------------
                // Status & Payment
                // ---------------------------
                Section::make('Status & Payment 💰')
                    ->schema([
                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing',
                                'ready' => 'Ready',
                                'picked_up' => 'Picked Up',
                                'in_delivery' => 'In Delivery',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->reactive(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'partial' => 'Partial',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending'),

                        Select::make('payment_gateway')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'credit_card' => 'Credit Card',
                                'debit_card' => 'Debit Card',
                                'e_wallet' => 'E-Wallet',
                                'qris' => 'QRIS',
                            ])
                            ->default('cash'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // ---------------------------
                // Price Calculation (Base & Total) - FIXED THE BUG HERE
                // ---------------------------
                Section::make('Price Calculation 🧮')
                    ->schema([
                        Placeholder::make('base_price_display')
                            ->label('Base Price (Sum of Items)')
                            ->content(fn (Forms\Get $get) =>
                                'Rp ' . number_format($get('base_price') ?? 0, 0, ',', '.')
                            ),

                        // FIXED: Simplified content logic, removing the extra closure that caused the bug.
                        Placeholder::make('speed_info')
                            ->label('Speed Charge')
                            ->content(fn (Forms\Get $get) =>
                                match ($get('service_speed')) {
                                    'express' => '+50% (1.5x)',
                                    'same_day' => '+100% (2.0x)',
                                    default => 'No extra charge',
                                }
                            ),

                        // FIXED: Simplified content logic, removing the extra closure that caused the bug.
                        Placeholder::make('delivery_info')
                            ->label('Delivery Charge')
                            ->content(fn (Forms\Get $get) =>
                                match ($get('delivery_method')) {
                                    'pickup' => '+20% (1.2x)',
                                    'delivery' => '+20% (1.2x)',
                                    'pickup_delivery' => '+40% (1.4x)',
                                    default => 'No extra charge',
                                }
                            ),

                        // IMPROVEMENT: Using Currency instead of TextInput for proper formatting and input type
                        Forms\Components\TextInput::make('total_price')
                             ->label('Total Price')
                             ->numeric()
                             ->disabled()
                             ->dehydrated(),

                             Hidden::make('final_price')
                            ->dehydrated() // Penting: agar nilai dikirim ke DB meski hidden
                            ->default(0),

                        Placeholder::make('formatted_total')
                            ->label('Total (Formatted)')
                            ->content(fn (Forms\Get $get) =>
                                '<span class="text-xl font-bold text-primary-600">Rp ' . number_format($get('total_price') ?? 0, 0, ',', '.') . '</span>'
                            )
                            ->columnSpan(2), // Make this span 2 columns

                        Hidden::make('base_price')->default(0),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // ---------------------------
                // Courier & Scheduling
                // ---------------------------
                Section::make('Courier & Scheduling 📅')
                    ->schema([
                        Select::make('courier_id')
                            ->label('Courier')
                            ->relationship('courier', 'name')
                            ->searchable()
                            ->visible(fn (Forms\Get $get) =>
                                in_array($get('delivery_method'), ['pickup', 'delivery', 'pickup_delivery'])
                            ),

                        DateTimePicker::make('pickup_time')
                            ->label('Pickup Time')
                            ->native(false)
                            ->minDate(now()),

                        DateTimePicker::make('delivery_time')
                            ->label('Delivery/Completion Time')
                            ->native(false)
                            ->after('pickup_time'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // ---------------------------
                // Additional Information
                // ---------------------------
                Section::make('Additional Information 📝')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Order Notes')
                            ->rows(4)
                            ->columnSpanFull(),

                            DateTimePicker::make('created_at')
            ->label('Order Date')
            ->default(now()) // Default ke waktu sekarang
            ->required(),

            
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    /**
     * Calculate total price from repeater items, apply multipliers,
     * and set base_price & total_price in form state.
     */
    protected static function calculateFinalTotalPrice(Forms\Set $set, Forms\Get $get): void
{
    $items = $get('order_items') ?? [];
    $base = 0;

    foreach ($items as $item) {
        $type = $item['pricing_type'] ?? null;
        $priceKg = $item['price_per_kg'] ?? 0;
        $priceUnit = $item['price_per_unit'] ?? 0;
        $qty = $item['quantity'] ?? 0;
        $kg = $item['weight'] ?? 0;

        if ($type === 'kg' && $kg > 0) {
            $base += floatval($kg) * floatval($priceKg);
        }

        if ($type === 'unit' && $qty > 0) {
            $base += intval($qty) * floatval($priceUnit);
        }
    }

    // Apply multipliers from order-level options
    $speed = $get('service_speed') ?? 'regular';
    $delivery = $get('delivery_method') ?? 'walk_in';

    $speedMul = self::getSpeedMultiplier($speed);
    $deliveryMul = self::getDeliveryMultiplier($delivery);

    $total = $base * $speedMul * $deliveryMul;

    // Round values
    $finalTotal = round($total);

    // Set values to form state
    $set('base_price', round($base));
    $set('total_price', $finalTotal);
    $set('final_price', $finalTotal);
    }

    protected static function getSpeedMultiplier(?string $speed): float
    {
        return match ($speed) {
            'express' => 1.5,
            'same_day' => 2.0,
            default => 1.0,
        };
    }

    protected static function getDeliveryMultiplier(?string $method): float
    {
        return match ($method) {
            'pickup' => 1.2,
            'delivery' => 1.2,
            'pickup_delivery' => 1.4,
            default => 1.0,
        };
    }

    // ---------------------------
    // Table & Pages
    // ---------------------------
    public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ---------------------------
            // Primary Identifiers
            // ---------------------------
            Tables\Columns\TextColumn::make('id')
                ->label('Order ID') // Renamed label for clarity
                ->sortable()
                ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                ->copyable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false) // Always visible

            // ---------------------------
            // Customer Details (Improved)
            // ---------------------------
            ,Tables\Columns\TextColumn::make('customer.name')
                ->label('Customer')
                ->sortable()
                ->searchable(query: function (Builder $query, string $search): Builder {
                    // Search both member name and guest name
                    return $query
                        ->whereHas('customer', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhere('guest_name', 'like', "%{$search}%");
                })
                ->formatStateUsing(fn ($state, $record) => $record->customer ? $record->customer->name : ($record->guest_name ?? 'Guest'))
                // Visually differentiate guests from members
                ->color(fn ($record): string => $record->customer_id ? 'primary' : 'secondary'),

            Tables\Columns\TextColumn::make('outlet.name')
                ->label('Outlet')
                ->sortable()
                ->toggleable()
                ->description(fn ($record) => $record->outlet?->address), // Show address on hover or below the name

            // ---------------------------
            // Financial & Status
            // ---------------------------
            Tables\Columns\TextColumn::make('total_price')
                ->label('Total Price') // Renamed label
                ->money('IDR')
                ->sortable()
                ->weight('bold') // Used 'bold' instead of 'semibold' for more emphasis
                ->color('success'),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Order Status') // Renamed label
                ->colors([
                    'secondary' => 'pending',
                    'info' => 'confirmed',
                    'warning' => 'processing',
                    'primary' => 'ready',
                    'success' => 'completed',
                    'danger' => 'cancelled',
                    'gray' => 'picked_up', // Added color for Picked Up/In Delivery
                    'dark' => 'in_delivery',
                ])
                ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
                ->sortable(),
            
            Tables\Columns\BadgeColumn::make('payment_status')
                ->label('Payment')
                ->colors([
                    'warning' => 'pending',
                    'success' => 'paid',
                    'info' => 'partial',
                    'danger' => 'failed',
                    'gray' => 'refunded',
                ])
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false), // Always visible

            // ---------------------------
            // Dates & Metadata
            // ---------------------------
            Tables\Columns\TextColumn::make('delivery_time')
                ->label('Scheduled Finish')
                ->dateTime('d M Y') // Shortened date format for table visibility
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true), // Hidden by default

            Tables\Columns\TextColumn::make('created_at')
                ->label('Order Date')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->since()
                ->toggleable(isToggledHiddenByDefault: false),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'processing' => 'Processing',
                    'ready' => 'Ready',
                    'picked_up' => 'Picked Up', // Added missing statuses to filter
                    'in_delivery' => 'In Delivery',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])
                ->multiple(),

            // IMPROVEMENT: Filter by payment status
            Tables\Filters\SelectFilter::make('payment_status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'partial' => 'Partial',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ])
                ->label('Payment Status')
                ->multiple(),
            
            // IMPROVEMENT: Filter by outlet
            Tables\Filters\SelectFilter::make('outlet_id')
                ->relationship('outlet', 'name')
                ->label('Outlet Location')
                ->preload()
                ->searchable(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            // IMPROVEMENT: Use a Custom Action to quickly mark as Complete/Ready
            Tables\Actions\Action::make('mark_ready')
                ->label('Mark Ready')
                ->icon('heroicon-s-check-badge')
                ->color('primary')
                ->visible(fn ($record) => $record->status === 'processing')
                ->action(function ($record) {
                    $record->status = 'ready';
                    $record->save();
                    Notification::make()->title('Order Ready!')->success()->send();
                }),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->defaultSort('created_at', 'desc')
        ->poll('15s'); // Increased polling frequency for better real-time updates
}

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'confirmed'])->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::whereIn('status', ['pending', 'confirmed'])->count();

        return $count > 5 ? 'danger' : ($count > 0 ? 'warning' : 'success');
    }
}
