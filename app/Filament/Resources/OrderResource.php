<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Service;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Collection;

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

    // Constants for better maintainability
    private const CUSTOMER_TYPE_MEMBER = 'member';
    private const CUSTOMER_TYPE_GUEST = 'guest';
    private const PRICING_TYPE_KG = 'kg';
    private const PRICING_TYPE_UNIT = 'unit';

    // ========== AUTHORIZATION ==========
    
    /**
     * Hide/Show menu in navigation based on user role
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->canAccessOrders();
    }

    /**
     * Check if user can view list of orders
     */
    public static function canViewAny(): bool
    {
        return auth()->user()->canAccessOrders();
    }

    /**
     * Check if user can view single order
     */
    public static function canView($record): bool
    {
        $user = auth()->user();

        // Owner can view all orders
        if ($user->isOwner()) {
            return true;
        }

        // Other roles can only view orders from their outlet
        return $record->outlet_id === $user->outlet_id;
    }

    /**
     * Check if user can create orders
     */
    public static function canCreate(): bool
    {
        return auth()->user()->canCreateOrders();
    }

    /**
     * Check if user can edit order
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        // Owner can edit all orders
        if ($user->isOwner()) {
            return true;
        }

        // Admin and Staff can edit orders from their outlet
        if ($user->hasAnyRole(['admin', 'staff'])) {
            return $record->outlet_id === $user->outlet_id;
        }

        // Courier cannot edit orders
        return false;
    }

    /**
     * Check if user can delete order
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();

        // Only Owner and Admin can delete
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $record->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Check if user can delete any orders (bulk delete)
     */
    public static function canDeleteAny(): bool
    {
        return auth()->user()->canDeleteOrders();
    }

    /**
     * Scope query based on user role and outlet
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Owner can see all orders
        if ($user->isOwner()) {
            return $query;
        }

        // Admin, Staff, Courier can only see orders from their outlet
        return $query->where('outlet_id', $user->outlet_id);
    }

    // ========== FORM SCHEMA ==========
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            self::getCustomerSection(),
            self::getFreeServiceSection(),
            self::getOrderItemsRepeater(),
            self::getServiceOptionsSection(),
            self::getCouponSection(),
            self::getPriceCalculationSection(),
            self::getStatusPaymentSection(),
            self::getCourierSchedulingSection(),
            self::getAdditionalInfoSection(),
        ]);
    }

    // ===================================
    // FORM SECTIONS
    // ===================================

  protected static function getCustomerSection(): Section
{
    return Section::make('Customer & Outlet Information 🧑‍💻')
        ->schema([
            Radio::make('customer_type')
                ->label('Customer Type')
                ->options([
                    self::CUSTOMER_TYPE_MEMBER => 'Member (Registered)',
                    self::CUSTOMER_TYPE_GUEST => 'Guest (Walk-in)',
                ])
                ->default(fn ($record) => $record?->customer_type ?? self::CUSTOMER_TYPE_MEMBER)
                ->inline()
                ->live()
                ->required()
                ->dehydrated(true) // PENTING: Pastikan disimpan ke database
                ->afterStateHydrated(function (Set $set, $state, $record) {
                    // Saat load data existing, auto-detect customer type jika belum ada
                    if ($record && empty($state)) {
                        $detectedType = $record->customer_id 
                            ? self::CUSTOMER_TYPE_MEMBER 
                            : self::CUSTOMER_TYPE_GUEST;
                        $set('customer_type', $detectedType);
                    }
                })
                ->helperText('Select customer type'),

            Select::make('customer_id')
                ->label('Select Member')
                ->relationship('customer', 'name')
                ->searchable(['name', 'email', 'phone'])
                ->preload()
                ->required(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_MEMBER)
                ->hidden(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_GUEST)
                ->dehydrated(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_MEMBER)
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    // Clear guest fields when member is selected
                    if ($state && $get('customer_type') === self::CUSTOMER_TYPE_MEMBER) {
                        $set('guest_name', null);
                        $set('guest_phone', null);
                        $set('guest_address', null);
                    }
                    
                    self::recalculatePricing($set, $get);
                    
                    if ($state) {
                        self::notifyCustomerSelected($state);
                    }
                }),

            TextInput::make('guest_name')
                ->label('Guest Name')
                ->required(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_GUEST)
                ->hidden(fn (Get $get): bool => $get('customer_type') !== self::CUSTOMER_TYPE_GUEST)
                ->dehydrated(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_GUEST)
                ->maxLength(255)
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    // Clear customer_id when guest info is entered
                    if ($state && $get('customer_type') === self::CUSTOMER_TYPE_GUEST) {
                        $set('customer_id', null);
                    }
                }),

            TextInput::make('guest_phone')
                ->label('Guest Phone')
                ->tel()
                ->required(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_GUEST)
                ->hidden(fn (Get $get): bool => $get('customer_type') !== self::CUSTOMER_TYPE_GUEST)
                ->dehydrated(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_GUEST)
                ->maxLength(20),

            Textarea::make('guest_address')
                ->label('Guest Address')
                ->rows(2)
                ->hidden(fn (Get $get): bool => $get('customer_type') !== self::CUSTOMER_TYPE_GUEST)
                ->dehydrated(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_GUEST)
                ->maxLength(500),

           Select::make('outlet_id')
    ->label('Outlet')
    ->relationship('outlet', 'name', function ($query) {
        $user = auth()->user();
        
        // Jika bukan owner, hanya tampilkan outlet milik user tersebut
        if ($user->role !== 'owner' && $user->outlet_id) {
            $query->where('id', $user->outlet_id);
        }
        // Jika owner, tampilkan semua outlet (tidak perlu where)
    })
    ->searchable(['name', 'address'])
    ->preload()
    ->required()
    ->columnSpanFull(),
        ])
        ->columns(2)
        ->collapsible();
}

    protected static function getFreeServiceSection(): Section
    {
        return Section::make('Free Service Reward 🎁')
            ->schema([
                Placeholder::make('reward_eligibility')
                    ->label('Reward Status')
                    ->content(function (Get $get, $record) {
                        $customerId = $get('customer_id');
                        
                        if (!$customerId) {
                            return '⚠️ Only available for registered members';
                        }

                        $customer = Customer::find($customerId);
                        
                        if (!$customer) {
                            return '❌ Customer not found';
                        }

                        $completedOrders = $customer->orders()
                            ->whereIn('status', ['completed', 'picked_up', 'in_delivery'])
                            ->when($record?->id, function ($query, $orderId) {
                                return $query->where('id', '!=', $orderId);
                            })
                            ->count();

                        $progress = $completedOrders % 6;
                        $ordersLeft = 6 - $progress;
                        $percentage = ($progress / 6) * 100;

                        $statusText = $progress === 0 && $completedOrders >= 6 
                            ? "🎉 ELIGIBLE FOR FREE SERVICE!" 
                            : "📊 Progress: {$progress}/6 orders ({$ordersLeft} more to go)";

                        return "
                            <div class='space-y-2'>
                                <div class='font-semibold text-sm'>{$statusText}</div>
                                <div class='w-full bg-gray-200 rounded-full h-3'>
                                    <div class='bg-green-500 h-3 rounded-full transition-all' style='width: {$percentage}%'></div>
                                </div>
                            </div>
                        ";
                    })
                    ->columnSpanFull(),

                Toggle::make('is_free_service')
                    ->label('Apply Free Service Reward')
                    ->helperText('Enable this to make this order FREE (available after 6 completed orders)')
                    ->live()
                    ->visible(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_MEMBER)
                    ->disabled(function (Get $get, $record) {
                        $customerId = $get('customer_id');
                        
                        if (!$customerId) return true;

                        $customer = Customer::find($customerId);
                        
                        if (!$customer) return true;

                        $completedOrders = $customer->orders()
                            ->whereIn('status', ['completed', 'picked_up', 'in_delivery'])
                            ->when($record?->id, function ($query, $orderId) {
                                return $query->where('id', '!=', $orderId);
                            })
                            ->count();

                        return ($completedOrders % 6) !== 0 || $completedOrders < 6;
                    })
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        self::recalculatePricing($set, $get);
                        
                        if ($state) {
                            Notification::make()
                                ->success()
                                ->title('🎉 Free Service Applied!')
                                ->body('This order is now FREE as a reward.')
                                ->send();
                        }
                    }),

                Placeholder::make('free_service_note')
                    ->label('')
                    ->content('💡 **Note:** Free service is automatically applied for every 6th completed order. Toggle above to activate.')
                    ->visible(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_MEMBER)
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->visible(fn (Get $get): bool => $get('customer_type') === self::CUSTOMER_TYPE_MEMBER)
            ->collapsible();
    }

   protected static function getOrderItemsRepeater(): Repeater
{
    return Repeater::make('order_items')
        ->label('Services 🧺')
        ->relationship('orderItems')
        ->live()
        ->schema([
            Select::make('service_id')
                ->label('Service Type')
                ->relationship('service', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    if (!$state) return;
                    
                    $service = Service::find($state);
                    if (!$service) return;

                    self::applyServiceDefaults($set, $service);
                    
                    // Force recalculate setelah service dipilih
                    $set('weight', null);
                    $set('quantity', null);
                    $set('subtotal', 0);
                    
                    self::recalculatePricing($set, $get);
                })
                ->columnSpan(2),

            // Hidden fields
            Hidden::make('pricing_type')->dehydrated(),
            Hidden::make('price_per_kg')->dehydrated(),
            Hidden::make('price_per_unit')->dehydrated(),
            Hidden::make('price')->default(0)->dehydrated(),
            Hidden::make('subtotal')->default(0)->dehydrated(),

            TextInput::make('weight')
                ->label('Weight (KG)')
                ->numeric()
                ->minValue(0.1)
                ->step(0.1)
                ->default(null)
                ->visible(fn (Get $get): bool => $get('pricing_type') === self::PRICING_TYPE_KG)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    // Recalculate bahkan jika 0
                    self::recalculateItemSubtotal($set, $get);
                    self::recalculatePricing($set, $get);
                }),

            TextInput::make('quantity')
                ->label('Qty')
                ->numeric()
                ->minValue(1)
                ->default(null)
                ->visible(fn (Get $get): bool => $get('pricing_type') === self::PRICING_TYPE_UNIT)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    // Recalculate bahkan jika 0
                    self::recalculateItemSubtotal($set, $get);
                    self::recalculatePricing($set, $get);
                }),

            Placeholder::make('subtotal_display')
                ->label('Subtotal')
                ->content(function (Get $get) {
                    $subtotal = (float) ($get('subtotal') ?? 0);
                    
                    return $subtotal > 0 
                        ? "Rp " . number_format($subtotal, 0, ',', '.') 
                        : "Rp 0";
                })
                ->columnSpan(1),
        ])
        ->columns(4)
        ->minItems(1)
        ->defaultItems(1)
        ->addActionLabel('Add Service')
        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculatePricing($set, $get))
        ->columnSpanFull();
}

    protected static function getServiceOptionsSection(): Section
{
    return Section::make('Service Options ⚡🚚')
        ->schema([
            Select::make('service_speed')
                ->label('Service Speed')
                ->options([
                    'regular' => 'Regular (2–3 days)',
                    'express' => 'Express (1 day) +50%',
                    'same_day' => 'Same Day +100%',
                ])
                ->default('regular')
                ->live()
                ->required()
                ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculatePricing($set, $get)),

            Select::make('delivery_method')
                ->label('Delivery Method')
                ->options([
                    'walk_in' => 'Walk-in (Standard)',
                    'pickup' => 'Pickup +20%',
                    'delivery' => 'Delivery +20%',
                    'pickup_delivery' => 'Both +40%',
                ])
                ->default('walk_in')
                ->live()
                ->required()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    self::recalculatePricing($set, $get);
                    
                    // Auto-show/hide courier section based on delivery method
                    $needsCourier = in_array($state, ['pickup', 'delivery', 'pickup_delivery']);
                    
                    if (!$needsCourier) {
                        $set('courier_id', null);
                    }
                })
                ->helperText('Pickup/Delivery will create tracking record automatically'),
        ])
        ->columns(2)
        ->collapsible();
}

    protected static function getCouponSection(): Section
    {
        return Section::make('Coupon & Discount 🎟️')
            ->schema([
                TextInput::make('coupon_code')
                    ->label('Coupon Code')
                    ->placeholder('Enter promo code...')
                    ->maxLength(50)
                    ->live(debounce: 600)
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        self::resetCouponFields($set);
                        
                        if (!empty($state)) {
                            self::recalculatePricing($set, $get);
                        }
                    })
                    ->helperText('Members get reward coupon after order completion'),

                Placeholder::make('coupon_status_display')
                    ->label('Status')
                    ->content(fn (Get $get): ?string => $get('coupon_message') ?? '-')
                    ->extraAttributes(fn (Get $get): array => [
                        'class' => self::getCouponStatusClass($get('coupon_message'))
                    ]),

                Hidden::make('coupon_id'),
                Hidden::make('discount_amount')->default(0),
                Hidden::make('discount_type'),
                Hidden::make('coupon_message'),
                Hidden::make('coupon_earned')->default(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getPriceCalculationSection(): Section
    {
        return Section::make('Price Calculation 🧮')
            ->schema([
                Placeholder::make('base_price_display')
                    ->label('Base Price (Items)')
                    ->content(fn (Get $get): string =>
                        'Rp ' . number_format($get('base_price') ?? 0, 0, ',', '.')
                    ),

                Placeholder::make('surcharges_info')
                    ->label('Surcharges (Speed & Delivery)')
                    ->content(function (Get $get): string {
                        $totalMultiplier = self::calculateTotalMultiplier($get);
                        
                        if ($totalMultiplier <= 1) {
                            return 'No extra charge';
                        }
                        
                        return "Multiplier: x" . number_format($totalMultiplier, 2);
                    }),

                Placeholder::make('discount_display')
                    ->label('Discount Applied')
                    ->content(function (Get $get): string {
                        $discount = $get('discount_amount') ?? 0;
                        $type = $get('discount_type');
                        
                        if ($get('is_free_service')) {
                            return '🎁 100% OFF (Free Service Reward)';
                        }
                        
                        if ($discount <= 0) {
                            return '-';
                        }
                        
                        $label = $type === 'coupon' ? '(Coupon)' : ($type === 'membership' ? '(Member)' : '');
                        
                        return '- Rp ' . number_format($discount, 0, ',', '.') . ' ' . $label;
                    })
                    ->extraAttributes(['class' => 'text-success-600 font-bold']),

                TextInput::make('total_price')
                    ->label('Total Price (Before Discount)')
                    ->numeric()
                    ->readOnly()
                    ->prefix('Rp')
                    ->dehydrated(),

                TextInput::make('final_price')
                    ->label('Final Total Price (After Discount)')
                    ->numeric()
                    ->readOnly()
                    ->prefix('Rp')
                    ->dehydrated()
                    ->extraInputAttributes(fn (Get $get): array => [
                        'class' => $get('is_free_service') 
                            ? 'text-xl font-bold text-green-600' 
                            : 'text-xl font-bold text-primary-600'
                    ]),

                Hidden::make('base_price')->default(0),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getStatusPaymentSection(): Section
    {
        return Section::make('Status & Payment 💰')
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
                    ->required()
                    ->live()
                    ->helperText('Completing order will give reward coupon to member'),

                Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->default('pending')
                    ->required(),

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
                    ->default('cash')
                    ->required(),
            ])
            ->columns(3)
            ->collapsible();
    }

    protected static function getCourierSchedulingSection(): Section
{
    return Section::make('Courier & Scheduling 📅')
        ->schema([
            Placeholder::make('tracking_info')
                ->label('📍 Tracking Notice')
                ->content('A tracking record will be automatically created when you save this order with pickup/delivery method.')
                ->visible(fn (Get $get): bool =>
                    in_array($get('delivery_method'), ['pickup', 'delivery', 'pickup_delivery'], true)
                )
                ->columnSpanFull(),

            Select::make('courier_id')
                ->label('Courier')
                ->relationship('courier', 'name', function ($query) {
                    $user = auth()->user();
                    
                    // Filter courier berdasarkan outlet
                    if ($user->role !== 'owner' && $user->outlet_id) {
                        $query->where('outlet_id', $user->outlet_id);
                    }
                    
                    // Hanya tampilkan user dengan role courier
                    $query->where('role', 'courier');
                })
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool =>
                    in_array($get('delivery_method'), ['pickup', 'delivery', 'pickup_delivery'], true)
                )
                ->visible(fn (Get $get): bool =>
                    in_array($get('delivery_method'), ['pickup', 'delivery', 'pickup_delivery'], true)
                )
                ->helperText('Assign courier for pickup/delivery'),

            DateTimePicker::make('pickup_time')
                ->label('Pickup Time')
                ->native(false)
                ->minDate(now())
                ->seconds(false)
                ->visible(fn (Get $get): bool =>
                    in_array($get('delivery_method'), ['pickup', 'pickup_delivery'], true)
                ),

            DateTimePicker::make('delivery_time')
                ->label('Delivery/Completion Time')
                ->native(false)
                ->minDate(now())
                ->seconds(false),
        ])
        ->columns(3)
        ->collapsible();
}

    protected static function getAdditionalInfoSection(): Section
    {
        return Section::make('Additional Information 📝')
            ->schema([
                Textarea::make('notes')
                    ->label('Order Notes')
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                DateTimePicker::make('created_at')
                    ->label('Order Date')
                    ->default(now())
                    ->required()
                    ->native(false),
            ])
            ->collapsible()
            ->collapsed();
    }

    // ===================================
    // CALCULATION HELPERS
    // ===================================

    /**
     * Main calculation method - recalculates entire order pricing
     */
    protected static function recalculatePricing(Set $set, Get $get): void
    {
        // 1. Calculate base price from items
        $basePrice = self::calculateBasePrice($get);
        $set('base_price', round($basePrice));

        // 2. Apply multipliers
        $totalMultiplier = self::calculateTotalMultiplier($get);
        $subtotal = $basePrice * $totalMultiplier;
        $set('total_price', round($subtotal));

        // 3. Check if free service is enabled
        $isFreeService = (bool) $get('is_free_service');

        if ($isFreeService) {
            // Free service: set everything to 0
            $set('discount_amount', round($subtotal));
            $set('discount_type', 'free_service');
            $set('final_price', 0);
            return;
        }

        // 4. Apply coupon discount (if not free service)
        $couponResult = self::processCoupon($get, $subtotal);

        // 5. Calculate final total
        $finalTotal = max(0, $subtotal - $couponResult['discount']);
        $finalTotal = round($finalTotal);

        // 6. Update form state
        $set('coupon_id', $couponResult['id']);
        $set('coupon_message', $couponResult['message']);
        $set('discount_amount', $couponResult['discount']);
        $set('discount_type', $couponResult['type']);
        $set('final_price', $finalTotal);
    }

    /**
     * Calculate base price from all order items
     */
    protected static function calculateBasePrice(Get $get): float
    {
        $items = $get('order_items') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $total += self::calculateItemSubtotal($item);
        }

        return $total;
    }

    /**
 * Calculate subtotal for a single item
 */
protected static function calculateItemSubtotal(array $item): float
{
    $type = $item['pricing_type'] ?? null;
    $priceKg = (float) ($item['price_per_kg'] ?? 0);
    $priceUnit = (float) ($item['price_per_unit'] ?? 0);
    $quantity = (float) ($item['quantity'] ?? 0);
    $weight = (float) ($item['weight'] ?? 0);

    if ($type === self::PRICING_TYPE_KG) {
        return $weight * $priceKg;
    }

    if ($type === self::PRICING_TYPE_UNIT) {
        return $quantity * $priceUnit;
    }

    return 0;
}

    /**
     * Get unit price for a single item
     */
    protected static function getItemUnitPrice(array $item): float
    {
        $type = $item['pricing_type'] ?? null;

        if ($type === self::PRICING_TYPE_KG) {
            return floatval($item['price_per_kg'] ?? 0);
        }

        if ($type === self::PRICING_TYPE_UNIT) {
            return floatval($item['price_per_unit'] ?? 0);
        }

        return 0;
    }

    /**
     * Calculate total multiplier (speed * delivery)
     */
    protected static function calculateTotalMultiplier(Get $get): float
    {
        $speed = $get('service_speed') ?? 'regular';
        $delivery = $get('delivery_method') ?? 'walk_in';

        return self::getSpeedMultiplier($speed) * self::getDeliveryMultiplier($delivery);
    }

    /**
     * Process coupon validation and discount calculation
     */
    protected static function processCoupon(Get $get, float $grossTotal): array
    {
        $couponCode = $get('coupon_code');
        
        $result = [
            'id' => null,
            'discount' => 0,
            'message' => null,
            'type' => null,
        ];

        if (empty($couponCode)) {
            return $result;
        }

        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            $result['message'] = "❌ Invalid coupon code";
            return $result;
        }

        $customerId = $get('customer_id') ? (int) $get('customer_id') : 0;
        
        // Ensure the Coupon model has canBeUsedByCustomer method
        $validation = method_exists($coupon, 'canBeUsedByCustomer') 
            ? $coupon->canBeUsedByCustomer($customerId, $grossTotal)
            : ['valid' => false, 'message' => 'Coupon validation method not found'];

        if (!$validation['valid']) {
            $result['message'] = "❌ " . ($validation['message'] ?? 'Invalid coupon');
            return $result;
        }

        $result['id'] = $coupon->id;
        $result['discount'] = method_exists($coupon, 'calculateDiscount') 
            ? $coupon->calculateDiscount($grossTotal)
            : 0;
        $result['message'] = "✅ Applied: " . ($coupon->discount_display ?? $coupon->code);
        $result['type'] = 'coupon';

        return $result;
    }

    /**
     * Get speed multiplier
     */
    protected static function getSpeedMultiplier(?string $speed): float
    {
        return match ($speed) {
            'express' => 1.5,
            'same_day' => 2.0,
            default => 1.0,
        };
    }

    /**
     * Get delivery method multiplier
     */
    protected static function getDeliveryMultiplier(?string $method): float
    {
        return match ($method) {
            'pickup' => 1.2,
            'delivery' => 1.2,
            'pickup_delivery' => 1.4,
            default => 1.0,
        };
    }

    // ===================================
    // HELPER METHODS
    // ===================================

    /**
     * Apply service defaults to form
     */
    protected static function applyServiceDefaults(Set $set, Service $service): void
    {
        $set('pricing_type', $service->pricing_type);
        $set('price_per_kg', $service->price_per_kg);
        $set('price_per_unit', $service->price_per_unit);
        $set('weight', null);
        $set('quantity', null);
    }

    /**
     * Reset coupon fields
     */
    protected static function resetCouponFields(Set $set): void
    {
        $set('coupon_id', null);
        $set('discount_amount', 0);
        $set('discount_type', null);
        $set('coupon_message', null);
    }

    /**
     * Get CSS class for coupon status
     */
    protected static function getCouponStatusClass(?string $message): string
    {
        if (!$message) {
            return 'text-gray-500';
        }

        return str_contains($message, '✅') 
            ? 'text-success-600 font-bold' 
            : 'text-danger-600 font-bold';
    }

    /**
     * Send notification when customer is selected
     */
    protected static function notifyCustomerSelected(int $customerId): void
    {
        $customer = Customer::find($customerId);
        
        if ($customer) {
            $memberStatus = method_exists($customer, 'isMember') && $customer->isMember() ? ' ⭐ MEMBER' : '';
            
            Notification::make()
                ->title('Customer Selected' . $memberStatus)
                ->body("Customer: {$customer->name} | Phone: {$customer->phone}")
                ->success()
                ->send();
        }
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->formatStateUsing(fn ($state): string => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                ->copyable()
                ->searchable()
                ->description(fn ($record): ?string => $record->created_at?->format('d/m/Y'))
                ->color('primary')
                ->weight('semibold')
                ->alignCenter(),

            Tables\Columns\TextColumn::make('customer.name')
                ->label('Customer')
                ->default(fn ($record): string => $record->guest_name ?? 'Guest')
                ->sortable()
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query
                        ->whereHas('customer', fn (Builder $q): Builder =>
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                        )
                        ->orWhere('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_phone', 'like', "%{$search}%");
                })
                ->formatStateUsing(function ($record, $state): string {
                    if ($record->customer_type === 'member' && $state) {
                        return $state;
                    }
                    return $record->guest_name ?? 'Guest';
                })
                ->description(function ($record): ?string {
                    $phone = $record->customer_type === 'member' 
                        ? ($record->customer?->phone ?? 'No Phone')
                        : ($record->guest_phone ?? 'No Phone');
                    
                    $type = $record->customer_type === 'member' ? '👤 Member' : '🚶 Walk-in';
                    
                    return "{$phone} • {$type}";
                })
                ->icon(fn ($record): string =>
                    $record->customer_type === 'member' ? 'heroicon-o-user-circle' : 'heroicon-o-user'
                )
                ->iconColor(fn ($record): string =>
                    $record->customer_type === 'member' ? 'warning' : 'gray'
                )
                ->tooltip(fn ($record): string =>
                    $record->customer_type === 'member' ? '⭐ Member Customer' : '🚶 Guest Customer'
                )
                ->wrap(),

            Tables\Columns\TextColumn::make('outlet.name')
                ->label('Outlet')
                ->badge()
                ->color('success')
                ->icon('heroicon-o-building-storefront')
                ->sortable()
                ->searchable()
                ->limit(15),

            Tables\Columns\IconColumn::make('is_free_service')
                ->label('Free')
                ->boolean()
                ->trueIcon('heroicon-o-gift')
                ->falseIcon('heroicon-o-currency-dollar')
                ->trueColor('success')
                ->falseColor('primary')
                ->tooltip(fn ($record): string => 
                    $record->is_free_service ? '🎁 Free Service Reward' : '💰 Paid Order'
                )
                ->sortable()
                ->alignCenter(),
            
            Tables\Columns\TextColumn::make('final_price')
                ->label('Total')
                ->money('idr', true)
                ->sortable()
                ->searchable()
                ->color(fn ($record): string => 
                    $record->is_free_service ? 'success' : 
                    ($record->payment_status === 'paid' ? 'primary' : 'warning')
                )
                ->weight('bold')
                ->description(fn ($record): ?string => 
                    $record->is_free_service ? '🎁 FREE' : 
                    ($record->payment_status === 'paid' ? '✅ Paid' : 
                    ($record->payment_status === 'pending' ? '⏳ Pending' : null))
                )
                ->alignEnd(),
                
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn ($state): string => match($state) {
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'processing' => 'Processing',
                    'ready' => 'Ready',
                    'picked_up' => 'Picked Up',
                    'in_delivery' => 'In Delivery',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    default => ucfirst($state)
                })
                ->colors([
                    'gray' => 'pending',
                    'blue' => 'confirmed',
                    'info' => 'processing',
                    'success' => 'completed',
                    'green' => 'ready',
                    'purple' => 'picked_up',
                    'cyan' => 'in_delivery',
                    'danger' => 'cancelled',
                ])
                ->icons([
                    'pending' => 'heroicon-o-clock',
                    'confirmed' => 'heroicon-o-check-circle',
                    'processing' => 'heroicon-o-cog-6-tooth',
                    'ready' => 'heroicon-o-inbox-arrow-down',
                    'picked_up' => 'heroicon-o-truck',
                    'in_delivery' => 'heroicon-o-truck',
                    'completed' => 'heroicon-o-check-badge',
                    'cancelled' => 'heroicon-o-x-circle',
                ])
                ->iconPosition('before')
                ->sortable()
                ->searchable(),
                
            Tables\Columns\TextColumn::make('payment_status')
                ->label('Payment')
                ->badge()
                ->formatStateUsing(fn ($state): string => match($state) {
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'partial' => 'Partial',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                    default => ucfirst($state)
                })
                ->colors([
                    'warning' => 'pending',
                    'success' => 'paid',
                    'info' => 'partial',
                    'danger' => 'failed',
                    'gray' => 'refunded',
                ])
                ->icons([
                    'pending' => 'heroicon-o-clock',
                    'paid' => 'heroicon-o-banknotes',
                    'partial' => 'heroicon-o-currency-dollar',
                    'failed' => 'heroicon-o-exclamation-triangle',
                    'refunded' => 'heroicon-o-arrow-path',
                ])
                ->iconPosition('before')
                ->sortable()
                ->searchable(),
                
            Tables\Columns\TextColumn::make('service_speed')
                ->label('Speed')
                ->badge()
                ->formatStateUsing(fn ($state): string => match($state) {
                    'express' => 'Express',
                    'same_day' => 'Same Day',
                    default => 'Regular'
                })
                ->colors([
                    'gray' => 'regular',
                    'warning' => 'express',
                    'danger' => 'same_day',
                ])
                ->icons([
                    'regular' => 'heroicon-o-clock',
                    'express' => 'heroicon-o-bolt',
                    'same_day' => 'heroicon-o-rocket-launch',
                ])
                ->iconPosition('before')
                ->toggleable(),
                
            Tables\Columns\TextColumn::make('delivery_method')
                ->label('Delivery')
                ->badge()
                ->formatStateUsing(fn ($state): string => match($state) {
                    'pickup' => 'Pickup',
                    'delivery' => 'Delivery',
                    'pickup_delivery' => 'Both',
                    default => 'Walk-in'
                })
                ->colors([
                    'gray' => 'walk_in',
                    'blue' => 'pickup',
                    'purple' => 'delivery',
                    'indigo' => 'pickup_delivery',
                ])
                ->icons([
                    'walk_in' => 'heroicon-o-building-storefront',
                    'pickup' => 'heroicon-o-arrow-up-tray',
                    'delivery' => 'heroicon-o-truck',
                    'pickup_delivery' => 'heroicon-o-arrows-right-left',
                ])
                ->iconPosition('before')
                ->toggleable(),
                
            Tables\Columns\TextColumn::make('created_at')
                ->label('Order Date')
                ->dateTime('d M Y, H:i')
                ->sortable()
                ->description(fn ($record): string => $record->created_at->diffForHumans())
                ->color('gray')
                ->toggleable(isToggledHiddenByDefault: false),
                
            Tables\Columns\TextColumn::make('orderItems_count')
                ->label('Items')
                ->counts('orderItems')
                ->badge()
                ->color('info')
                ->icon('heroicon-o-rectangle-stack')
                ->sortable()
                ->alignCenter()
                ->toggleable(),
                
            Tables\Columns\TextColumn::make('total_price')
                ->label('Subtotal')
                ->money('idr', true)
                ->color('gray')
                ->description('Before discount')
                ->toggleable(isToggledHiddenByDefault: true),
                
            Tables\Columns\TextColumn::make('discount_amount')
                ->label('Discount')
                ->money('idr', true)
                ->color('success')
                ->formatStateUsing(fn ($state, $record): string => 
                    $record->is_free_service ? 'FREE' : ($state > 0 ? '- ' . number_format($state, 0, ',', '.') : '0')
                )
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('customer_type')
                ->label('Customer Type')
                ->options([
                    'member' => '👤 Member',
                    'guest' => '🚶 Guest / Walk-in',
                ])
                ->placeholder('All Customers')
                ->searchable(),

            Tables\Filters\SelectFilter::make('status')
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
                ->multiple()
                ->label('Order Status')
                ->placeholder('All Statuses')
                ->searchable(),

            Tables\Filters\SelectFilter::make('payment_status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'partial' => 'Partial',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ])
                ->multiple()
                ->label('Payment Status')
                ->placeholder('All Payments')
                ->searchable(),

            Tables\Filters\SelectFilter::make('outlet')
                ->relationship('outlet', 'name')
                ->searchable()
                ->preload()
                ->label('Filter by Outlet')
                ->placeholder('All Outlets'),
                
            Tables\Filters\SelectFilter::make('is_free_service')
                ->label('Service Type')
                ->options([
                    true => '🎁 Free Orders',
                    false => '💰 Paid Orders',
                ])
                ->placeholder('All Orders')
                ->searchable(),
                
            Tables\Filters\SelectFilter::make('service_speed')
                ->label('Service Speed')
                ->options([
                    'regular' => 'Regular',
                    'express' => 'Express',
                    'same_day' => 'Same Day',
                ])
                ->multiple()
                ->placeholder('All Speeds'),
                
            Tables\Filters\SelectFilter::make('delivery_method')
                ->label('Delivery Method')
                ->options([
                    'walk_in' => 'Walk-in',
                    'pickup' => 'Pickup',
                    'delivery' => 'Delivery',
                    'pickup_delivery' => 'Pickup & Delivery',
                ])
                ->multiple()
                ->placeholder('All Methods'),
                
            Tables\Filters\TernaryFilter::make('has_coupon')
                ->label('Coupon Usage')
                ->nullable()
                ->placeholder('All Orders')
                ->trueLabel('With Coupon')
                ->falseLabel('Without Coupon')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('coupon_id'),
                    false: fn (Builder $query) => $query->whereNull('coupon_id'),
                    blank: fn (Builder $query) => $query,
                ),
                
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('From Date')
                        ->native(false),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('Until Date')
                        ->native(false),
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
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['created_from'] ?? null) {
                        $indicators[] = 'From: ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                    }
                    if ($data['created_until'] ?? null) {
                        $indicators[] = 'Until: ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                    }
                    return $indicators;
                }),
        ])
        ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                        
                    Tables\Actions\EditAction::make()
                        ->color('warning')
                        ->visible(fn ($record) => auth()->user()->can('update', $record)),
                        
                    Tables\Actions\Action::make('mark_completed')
                        ->label('Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record): bool => 
                            auth()->user()->canManageOrders() &&
                            !in_array($record->status, ['completed', 'cancelled'])
                        )
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'completed',
                                'payment_status' => $record->is_free_service ? 'paid' : $record->payment_status
                            ]);
                            
                            Notification::make()
                                ->success()
                                ->title('Order Completed')
                                ->body('Order has been marked as completed')
                                ->send();
                        }),
                        
                    Tables\Actions\Action::make('mark_paid')
                        ->label('Mark Paid')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn ($record): bool => 
                            auth()->user()->hasAnyRole(['owner', 'admin']) &&
                            !$record->is_free_service && 
                            $record->payment_status !== 'paid'
                        )
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['payment_status' => 'paid']);
                            
                            Notification::make()
                                ->success()
                                ->title('Payment Updated')
                                ->body('Order marked as paid')
                                ->send();
                        }),
                        
                    Tables\Actions\Action::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn ($record) => auth()->user()->canManageOrders())
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
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
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update(['status' => $data['status']]);
                            
                            Notification::make()
                                ->success()
                                ->title('Status Updated')
                                ->body("Order status changed to {$data['status']}")
                                ->send();
                        }),
                        
                    Tables\Actions\Action::make('download_invoice')
                        ->label('Download Invoice')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn () => auth()->user()->hasAnyRole(['owner', 'admin', 'staff']))
                        ->action(function ($record) {
                            $record->load(['customer', 'outlet', 'courier', 'orderItems.service', 'coupon']);
                            
                            $order = $record;
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.order-pdf', compact('order'));
                            $filename = 'Invoice-' . str_pad($record->id, 6, '0', STR_PAD_LEFT) . '.pdf';
                            
                            Notification::make()
                                ->success()
                                ->title('Invoice Downloaded')
                                ->body('PDF saved to your downloads folder')
                                ->send();
                            
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $filename);
                        }),
                        
                    Tables\Actions\Action::make('open_whatsapp')
                        ->label('Open WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->visible(fn ($record): bool => 
                            auth()->user()->hasAnyRole(['owner', 'admin', 'staff']) &&
                            !empty($record->customer_type === 'member' 
                                ? $record->customer?->phone 
                                : $record->guest_phone)
                        )
                        ->action(function ($record) {
                            $phone = $record->customer_type === 'member' 
                                ? $record->customer?->phone 
                                : $record->guest_phone;

                            if (!$phone) {
                                Notification::make()
                                    ->danger()
                                    ->title('No Phone Number')
                                    ->body('Customer does not have a phone number')
                                    ->send();
                                return;
                            }

                            $phone = preg_replace('/[^0-9]/', '', $phone);
                            
                            if (!str_starts_with($phone, '62')) {
                                if (str_starts_with($phone, '0')) {
                                    $phone = '62' . substr($phone, 1);
                                } else {
                                    $phone = '62' . $phone;
                                }
                            }

                            $customerName = $record->customer_type === 'member' 
                                ? $record->customer?->name 
                                : $record->guest_name;

                            $message = "Halo *{$customerName}* 👋\n\n";
                            $message .= "Terima kasih telah menggunakan layanan kami!\n\n";
                            $message .= "🧾 *Invoice Details:*\n";
                            $message .= "━━━━━━━━━━━━━━━━\n";
                            $message .= "📋 Order ID: #" . str_pad($record->id, 6, '0', STR_PAD_LEFT) . "\n";
                            $message .= "📅 Date: " . $record->created_at->format('d M Y, H:i') . "\n";
                            $message .= "🏪 Outlet: {$record->outlet->name}\n";
                            $message .= "📊 Status: " . ucfirst($record->status) . "\n";
                            $message .= "💰 Total: Rp " . number_format($record->final_price, 0, ',', '.') . "\n";
                            
                            if ($record->is_free_service) {
                                $message .= "🎁 *FREE SERVICE REWARD*\n";
                            }
                            
                            $message .= "━━━━━━━━━━━━━━━━\n\n";
                            $message .= "_Silakan kirim invoice PDF secara manual_\n\n";
                            $message .= "Terima kasih! 🙏";

                            $encodedMessage = urlencode($message);
                            $whatsappUrl = "https://wa.me/{$phone}?text={$encodedMessage}";

                            Notification::make()
                                ->success()
                                ->title('Opening WhatsApp')
                                ->body('Send the invoice PDF manually')
                                ->send();

                            return redirect()->away($whatsappUrl);
                        }),
                        
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->visible(fn ($record) => auth()->user()->can('delete', $record)),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_completed_bulk')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn () => auth()->user()->canManageOrders())
                        ->action(function (Collection $records) {
                            $count = 0;
                            $records->each(function ($record) use (&$count) {
                                if (!in_array($record->status, ['completed', 'cancelled'])) {
                                    $record->update([
                                        'status' => 'completed',
                                        'payment_status' => $record->is_free_service ? 'paid' : $record->payment_status
                                    ]);
                                    $count++;
                                }
                            });
                            
                            Notification::make()
                                ->success()
                                ->title('Orders Completed')
                                ->body("{$count} orders marked as completed")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('mark_paid_bulk')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn () => auth()->user()->hasAnyRole(['owner', 'admin']))
                        ->action(function (Collection $records) {
                            $count = 0;
                            $records->each(function ($record) use (&$count) {
                                if (!$record->is_free_service && $record->payment_status !== 'paid') {
                                    $record->update(['payment_status' => 'paid']);
                                    $count++;
                                }
                            });
                                
                            Notification::make()
                                ->success()
                                ->title('Payment Updated')
                                ->body("{$count} orders marked as paid")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('update_status_bulk')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn () => auth()->user()->canManageOrders())
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['status' => $data['status']]);
                            
                            Notification::make()
                                ->success()
                                ->title('Status Updated')
                                ->body("{$records->count()} orders updated to {$data['status']}")
                                ->send();
                        })
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
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
                                ->required(),
                        ])
                        ->modalWidth('sm')
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('export_orders')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn () => auth()->user()->hasAnyRole(['owner', 'admin']))
                        ->action(function (Collection $records) {
                            Notification::make()
                                ->success()
                                ->title('Export Started')
                                ->body("Exporting {$records->count()} orders...")
                                ->send();
                        }),
                        
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->successNotificationTitle('Orders deleted')
                        ->modalHeading('Delete Orders')
                        ->modalDescription('This will permanently delete selected orders.')
                        ->modalSubmitActionLabel('Yes, Delete All')
                        ->visible(fn () => auth()->user()->canDeleteOrders()),
                ])
                ->label('Bulk Actions')
                ->color('primary')
                ->dropdownWidth('64'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Order')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->size('lg')
                    ->visible(fn () => auth()->user()->canCreateOrders()),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession();
    }

    /**
 * Recalculate subtotal untuk single item di repeater
 */
protected static function recalculateItemSubtotal(Set $set, Get $get): void
{
    $pricingType = $get('pricing_type');
    $pricePerKg = (float) ($get('price_per_kg') ?? 0);
    $pricePerUnit = (float) ($get('price_per_unit') ?? 0);
    $quantity = (float) ($get('quantity') ?? 0);
    $weight = (float) ($get('weight') ?? 0);

    $subtotal = self::calculateItemSubtotal([
        'pricing_type' => $pricingType,
        'price_per_kg' => $pricePerKg,
        'price_per_unit' => $pricePerUnit,
        'quantity' => $quantity,
        'weight' => $weight,
    ]);
    
    // Set subtotal dan price
    $set('subtotal', $subtotal);
    $set('price', self::getItemUnitPrice([
        'pricing_type' => $pricingType,
        'price_per_kg' => $pricePerKg,
        'price_per_unit' => $pricePerUnit,
    ]));
}





    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}