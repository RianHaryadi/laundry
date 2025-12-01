<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $modelLabel = 'Coupon';

    protected static ?string $pluralModelLabel = 'Coupons';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Coupon Code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., WELCOME2024')
                            ->helperText('Unique code that customers will use')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function (Forms\Set $set) {
                                        $set('code', strtoupper(Str::random(8)));
                                    })
                            )
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('code', strtoupper($state)) : null
                            )
                            ->live(onBlur: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('e.g., Welcome discount for new customers')
                            ->helperText('Optional: Describe what this coupon is for')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Discount Settings')
                    ->schema([
                        Forms\Components\Select::make('discount_type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage (%)',
                                'fixed' => 'Fixed Amount (Rp)',
                                'free_shipping' => 'Free Shipping',
                            ])
                            ->default('percentage')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->helperText('Type of discount to apply'),

                        Forms\Components\TextInput::make('discount_value')
                            ->label(fn (Forms\Get $get): string => 
                                $get('discount_type') === 'percentage' 
                                    ? 'Discount Percentage' 
                                    : 'Discount Amount'
                            )
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (Forms\Get $get): ?int => 
                                $get('discount_type') === 'percentage' ? 100 : null
                            )
                            ->suffix(fn (Forms\Get $get): string => 
                                $get('discount_type') === 'percentage' ? '%' : 'Rp'
                            )
                            ->placeholder(fn (Forms\Get $get): string => 
                                $get('discount_type') === 'percentage' ? '10' : '50000'
                            )
                            ->helperText(fn (Forms\Get $get): string => 
                                $get('discount_type') === 'percentage' 
                                    ? 'Enter percentage (0-100)' 
                                    : 'Enter fixed amount in Rupiah'
                            )
                            ->hidden(fn (Forms\Get $get): bool => 
                                $get('discount_type') === 'free_shipping'
                            ),

                        Forms\Components\TextInput::make('max_discount')
                            ->label('Maximum Discount Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('100000')
                            ->helperText('Optional: Cap the maximum discount amount')
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('discount_type') === 'percentage'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Usage Restrictions')
                    ->schema([
                        Forms\Components\TextInput::make('min_order')
                            ->label('Minimum Order Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('50000')
                            ->helperText('Minimum order value required to use this coupon'),

                        Forms\Components\TextInput::make('max_uses')
                            ->label('Maximum Uses')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('100')
                            ->helperText('Total number of times this coupon can be used (leave empty for unlimited)'),

                        Forms\Components\TextInput::make('max_uses_per_user')
                            ->label('Max Uses Per Customer')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->placeholder('1')
                            ->helperText('How many times one customer can use this coupon'),

                        Forms\Components\TextInput::make('used_count')
                            ->label('Times Used')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Current usage count'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Validity Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date & Time')
                            ->native(false)
                            ->default(now())
                            ->helperText('When this coupon becomes active')
                            ->before('expires_at'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiry Date & Time')
                            ->native(false)
                            ->helperText('When this coupon expires (leave empty for no expiration)')
                            ->after('starts_at'),

                        Forms\Components\Placeholder::make('validity_status')
                            ->label('Current Status')
                            ->content(function (Forms\Get $get): string {
                                $startsAt = $get('starts_at');
                                $expiresAt = $get('expires_at');
                                $isActive = $get('is_active');

                                if (!$isActive) {
                                    return '🔴 Inactive';
                                }

                                if ($startsAt && now()->lt($startsAt)) {
                                    return '🟡 Scheduled (Not yet active)';
                                }

                                if ($expiresAt && now()->gt($expiresAt)) {
                                    return '🔴 Expired';
                                }

                                return '🟢 Active';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Applicability')
                    ->schema([
                        Forms\Components\Select::make('outlets')
                            ->label('Applicable Outlets')
                            ->relationship('outlets', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('All outlets')
                            ->helperText('Leave empty to apply to all outlets'),

                        // COMMENTED OUT - Add these when you have products/categories models
                        // Forms\Components\Select::make('products')
                        //     ->label('Applicable Products')
                        //     ->relationship('products', 'name')
                        //     ->multiple()
                        //     ->searchable()
                        //     ->preload()
                        //     ->placeholder('All products')
                        //     ->helperText('Leave empty to apply to all products'),

                        // Forms\Components\Select::make('categories')
                        //     ->label('Applicable Categories')
                        //     ->relationship('categories', 'name')
                        //     ->multiple()
                        //     ->searchable()
                        //     ->preload()
                        //     ->placeholder('All categories')
                        //     ->helperText('Leave empty to apply to all categories'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Status & Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable or disable this coupon')
                            ->inline(false),

                        Forms\Components\Toggle::make('is_public')
                            ->label('Public')
                            ->default(true)
                            ->helperText('Make this coupon visible to all customers')
                            ->inline(false),

                        Forms\Components\Toggle::make('first_order_only')
                            ->label('First Order Only')
                            ->default(false)
                            ->helperText('Only applicable for customer\'s first order')
                            ->inline(false),

                        Forms\Components\Toggle::make('exclude_discounted_items')
                            ->label('Exclude Sale Items')
                            ->default(false)
                            ->helperText('Cannot be used on already discounted items')
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Coupon Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-ticket')
                    ->copyable()
                    ->copyMessage('Code copied!')
                    ->copyMessageDuration(1500)
                    ->description(fn (Coupon $record): ?string => $record->description),

                Tables\Columns\BadgeColumn::make('discount_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'percentage',
                        'warning' => 'fixed',
                        'info' => 'free_shipping',
                    ])
                    ->icons([
                        'heroicon-o-percent-badge' => 'percentage',
                        'heroicon-o-currency-dollar' => 'fixed',
                        'heroicon-o-truck' => 'free_shipping',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        match($state) {
                            'percentage' => 'Percentage',
                            'fixed' => 'Fixed Amount',
                            'free_shipping' => 'Free Shipping',
                            default => 'N/A',
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_display')
                    ->label('Discount')
                    ->getStateUsing(fn (Coupon $record): string => 
                        $record->discount_type === 'percentage'
                            ? $record->discount_value . '%'
                            : ($record->discount_type === 'fixed'
                                ? 'Rp ' . number_format($record->discount_value, 0, ',', '.')
                                : 'Free')
                    )
                    ->badge()
                    ->color('success')
                    ->sortable(['discount_value']),

                Tables\Columns\TextColumn::make('min_order')
                    ->label('Min. Order')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('No minimum')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('usage_display')
                    ->label('Usage')
                    ->getStateUsing(fn (Coupon $record): string => 
                        $record->used_count . ' / ' . ($record->max_uses ?? '∞')
                    )
                    ->badge()
                    ->color(fn (Coupon $record): string => 
                        $record->max_uses && $record->used_count >= $record->max_uses
                            ? 'danger'
                            : 'info'
                    )
                    ->sortable(['used_count']),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('validity_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Coupon $record): string => 
                        $record->isExpired() 
                            ? 'Expired'
                            : ($record->isActive()
                                ? 'Active'
                                : ($record->isScheduled()
                                    ? 'Scheduled'
                                    : 'Inactive'))
                    )
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'Scheduled',
                        'danger' => 'Expired',
                        'secondary' => 'Inactive',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Active',
                        'heroicon-o-clock' => 'Scheduled',
                        'heroicon-o-x-circle' => 'Expired',
                        'heroicon-o-pause' => 'Inactive',
                    ]),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->description(fn ($record): ?string => 
                        $record->expires_at ? $record->expires_at->diffForHumans() : null
                    )
                    ->placeholder('No expiration')
                    ->color(fn ($record): string => 
                        $record->expires_at && $record->expires_at->isPast()
                            ? 'danger'
                            : 'success'
                    ),

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
                Tables\Filters\SelectFilter::make('discount_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'free_shipping' => 'Free Shipping',
                    ])
                    ->multiple()
                    ->label('Filter by Type'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All coupons')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('active_now')
                    ->label('Active Now')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('is_active', true)
                            ->where(function ($q) {
                                $q->whereNull('starts_at')
                                    ->orWhere('starts_at', '<=', now());
                            })
                            ->where(function ($q) {
                                $q->whereNull('expires_at')
                                    ->orWhere('expires_at', '>=', now());
                            })
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('expires_at')
                            ->where('expires_at', '<', now())
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('fully_used')
                    ->label('Fully Used')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('max_uses')
                            ->whereColumn('used_count', '>=', 'max_uses')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expiring Soon (7 days)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('expires_at')
                            ->whereBetween('expires_at', [now(), now()->addDays(7)])
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Coupon $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Coupon $record) => $record->is_active ? 'heroicon-m-pause' : 'heroicon-m-play')
                    ->color(fn (Coupon $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Coupon $record) => $record->update(['is_active' => !$record->is_active]))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Status updated successfully'),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('gray')
                    ->action(function (Coupon $record) {
                        $newCoupon = $record->replicate();
                        $newCoupon->code = $record->code . '_COPY';
                        $newCoupon->used_count = 0;
                        $newCoupon->is_active = false;
                        $newCoupon->save();
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Coupon duplicated successfully'),

                Tables\Actions\Action::make('reset_usage')
                    ->label('Reset Usage')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->action(fn (Coupon $record) => $record->update(['used_count' => 0]))
                    ->requiresConfirmation()
                    ->visible(fn (Coupon $record) => $record->used_count > 0)
                    ->successNotificationTitle('Usage count reset'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-m-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-m-pause')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('extend_expiry')
                        ->label('Extend Expiry Date')
                        ->icon('heroicon-m-calendar-days')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('days')
                                ->label('Extend by (days)')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(30),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $newExpiry = $record->expires_at 
                                    ? $record->expires_at->addDays($data['days'])
                                    : now()->addDays($data['days']);
                                $record->update(['expires_at' => $newExpiry]);
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Create Coupon'),
            ])
            ->emptyStateHeading('No coupons yet')
            ->emptyStateDescription('Create your first coupon to offer discounts to customers.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s');
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->count();
        
        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Active Coupons';
    }
}