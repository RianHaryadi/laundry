<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('John Doe')
                            ->autocomplete('name'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('john@example.com')
                            ->autocomplete('email')
                            ->helperText('Optional: For sending receipts and promotions'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('+62 812-3456-7890')
                            ->autocomplete('tel')
                            ->helperText('Used for loyalty program and notifications'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Full Address')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Street address, city, postal code')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Membership & Loyalty')
                    ->schema([
                        Forms\Components\Select::make('membership_level')
                            ->label('Membership Level')
                            ->options([
                                'bronze' => 'Bronze',
                                'silver' => 'Silver',
                                'gold' => 'Gold',
                                'platinum' => 'Platinum',
                                'vip' => 'VIP',
                            ])
                            ->default('bronze')
                            ->required()
                            ->native(false)
                            ->helperText('Membership tier based on spending or points'),

                        Forms\Components\TextInput::make('points')
                            ->label('Loyalty Points')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(10)
                            ->suffix('pts')
                            ->helperText('Current loyalty points balance'),

                        Forms\Components\DatePicker::make('member_since')
                            ->label('Member Since')
                            ->native(false)
                            ->default(now())
                            ->maxDate(now())
                            ->helperText('Date when customer joined'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\DatePicker::make('birthday')
                            ->label('Date of Birth')
                            ->native(false)
                            ->maxDate(now())
                            ->placeholder('Select birthday')
                            ->helperText('For birthday promotions'),

                        Forms\Components\Select::make('preferred_outlet_id')
                            ->label('Preferred Outlet')
                            ->relationship('preferredOutlet', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select preferred location')
                            ->helperText('Customer\'s favorite outlet'),

                        Forms\Components\Toggle::make('email_notifications')
                            ->label('Email Notifications')
                            ->default(true)
                            ->helperText('Receive promotions via email'),

                        Forms\Components\Toggle::make('sms_notifications')
                            ->label('SMS Notifications')
                            ->default(true)
                            ->helperText('Receive promotions via SMS'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Special preferences, allergies, etc.')
                            ->helperText('Only visible to staff')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-user')
                    ->description(fn (Customer $record): ?string => $record->email),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->copyMessage('Phone copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\BadgeColumn::make('membership_level')
                    ->label('Membership')
                    ->colors([
                        'secondary' => 'bronze',
                        'info' => 'silver',
                        'warning' => 'gold',
                        'success' => 'platinum',
                        'danger' => 'vip',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'bronze',
                        'heroicon-o-star' => 'silver',
                        'heroicon-o-trophy' => 'gold',
                        'heroicon-o-sparkles' => 'platinum',
                        'heroicon-o-fire' => 'vip',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? ucfirst($state) : 'N/A'
                    )
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->suffix(' pts')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->toggleable(),

                // COMMENTED OUT - Uncomment when orders table is ready
                // Tables\Columns\TextColumn::make('total_spent')
                //     ->label('Total Spent')
                //     ->getStateUsing(fn (Customer $record): float => 
                //         $record->orders()->sum('grand_total') ?? 0  // Change 'grand_total' to your actual column name
                //     )
                //     ->money('IDR')
                //     ->color('success')
                //     ->weight('semibold')
                //     ->toggleable(),

                Tables\Columns\TextColumn::make('preferredOutlet.name')
                    ->label('Preferred Outlet')
                    ->icon('heroicon-m-building-storefront')
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('member_since')
                    ->label('Member Since')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn ($record): ?string => 
                        $record->member_since ? $record->member_since->diffForHumans() : null
                    ),

                Tables\Columns\IconColumn::make('email_notifications')
                    ->label('Email')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('sms_notifications')
                    ->label('SMS')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\SelectFilter::make('membership_level')
                    ->options([
                        'bronze' => 'Bronze',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'platinum' => 'Platinum',
                        'vip' => 'VIP',
                    ])
                    ->multiple()
                    ->label('Filter by Membership'),

                Tables\Filters\SelectFilter::make('preferred_outlet')
                    ->relationship('preferredOutlet', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by Preferred Outlet'),

                Tables\Filters\Filter::make('points_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('points_from')
                                    ->label('Points from')
                                    ->numeric()
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('points_to')
                                    ->label('Points to')
                                    ->numeric()
                                    ->placeholder('1000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['points_from'],
                                fn (Builder $query, $points): Builder => $query->where('points', '>=', $points),
                            )
                            ->when(
                                $data['points_to'],
                                fn (Builder $query, $points): Builder => $query->where('points', '<=', $points),
                            );
                    }),

                Tables\Filters\Filter::make('high_value')
                    ->label('High Value Customers')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('points', '>=', 1000)
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('birthday_this_month')
                    ->label('Birthday This Month')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereMonth('birthday', now()->month)
                    )
                    ->toggle(),

                Tables\Filters\TernaryFilter::make('email_notifications')
                    ->label('Email Notifications')
                    ->placeholder('All customers')
                    ->trueLabel('Opted in')
                    ->falseLabel('Opted out'),

                Tables\Filters\TernaryFilter::make('has_email')
                    ->label('Has Email')
                    ->placeholder('All customers')
                    ->trueLabel('With email')
                    ->falseLabel('Without email')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email'),
                        false: fn (Builder $query) => $query->whereNull('email'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('add_points')
                    ->label('Add Points')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('points_to_add')
                            ->label('Points to Add')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('pts'),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->placeholder('e.g., Purchase bonus, Birthday gift')
                            ->rows(2),
                    ])
                    ->action(function (Customer $record, array $data) {
                        $record->increment('points', $data['points_to_add']);
                    })
                    ->successNotificationTitle('Points added successfully'),

                Tables\Actions\Action::make('upgrade_membership')
                    ->label('Upgrade Membership')
                    ->icon('heroicon-m-arrow-up-circle')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('new_level')
                            ->label('New Membership Level')
                            ->options([
                                'bronze' => 'Bronze',
                                'silver' => 'Silver',
                                'gold' => 'Gold',
                                'platinum' => 'Platinum',
                                'vip' => 'VIP',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(fn (Customer $record, array $data) => 
                        $record->update(['membership_level' => $data['new_level']])
                    )
                    ->successNotificationTitle('Membership upgraded successfully'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('update_membership')
                        ->label('Update Membership')
                        ->icon('heroicon-m-arrow-up-circle')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('membership_level')
                                ->label('New Membership Level')
                                ->options([
                                    'bronze' => 'Bronze',
                                    'silver' => 'Silver',
                                    'gold' => 'Gold',
                                    'platinum' => 'Platinum',
                                    'vip' => 'VIP',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(fn (array $data, $records) => 
                            $records->each->update(['membership_level' => $data['membership_level']])
                        )
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('add_bulk_points')
                        ->label('Add Points to Selected')
                        ->icon('heroicon-m-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('points')
                                ->label('Points to Add')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->suffix('pts'),
                        ])
                        ->action(fn (array $data, $records) => 
                            $records->each->increment('points', $data['points'])
                        )
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('toggle_notifications')
                        ->label('Toggle Notifications')
                        ->icon('heroicon-m-bell')
                        ->color('info')
                        ->form([
                            Forms\Components\Toggle::make('email_notifications')
                                ->label('Email Notifications'),
                            Forms\Components\Toggle::make('sms_notifications')
                                ->label('SMS Notifications'),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'email_notifications' => $data['email_notifications'],
                                    'sms_notifications' => $data['sms_notifications'],
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Add Customer'),
            ])
            ->emptyStateHeading('No customers yet')
            ->emptyStateDescription('Start by adding your first customer to the system.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            // COMMENTED OUT - Uncomment when OrdersRelationManager is ready
            // RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $totalCount = static::getModel()::count();
        return $totalCount > 0 ? (string) $totalCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $vipCount = static::getModel()::where('membership_level', 'vip')->count();
        $platinumCount = static::getModel()::where('membership_level', 'platinum')->count();
        
        return match (true) {
            $vipCount >= 5 => 'danger',
            $vipCount > 0 || $platinumCount >= 3 => 'warning',
            default => 'success',
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $total = static::getModel()::count();
        $vip = static::getModel()::where('membership_level', 'vip')->count();
        $platinum = static::getModel()::where('membership_level', 'platinum')->count();
        $gold = static::getModel()::where('membership_level', 'gold')->count();
        
        return "Total: {$total} | VIP: {$vip} | Platinum: {$platinum} | Gold: {$gold}";
    }
}