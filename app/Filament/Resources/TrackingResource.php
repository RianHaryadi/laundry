<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackingResource\Pages;
use App\Models\Tracking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrackingResource extends Resource
{
    protected static ?string $model = Tracking::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Tracking';

    protected static ?string $modelLabel = 'Tracking';

    protected static ?string $pluralModelLabel = 'Tracking Records';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Order & Courier Information')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Order')
                        ->relationship('order', 'id')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->getSearchResultsUsing(fn (string $search): array => 
                            \App\Models\Order::where('id', 'like', "%{$search}%")
                                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhere('guest_name', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($order) => [
                                    $order->id => '#' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . ' - ' . 
                                        ($order->customer?->name ?? $order->guest_name ?? 'Guest')
                                ])
                                ->toArray()
                        )
                        ->getOptionLabelUsing(fn ($value): ?string => 
                            $value ? '#' . str_pad($value, 6, '0', STR_PAD_LEFT) : null
                        )
                        ->placeholder('Select order'),
                    
                    Forms\Components\Select::make('courier_id')
                        ->label('Courier')
                        ->relationship('courier', 'name', function ($query) {
                            $user = auth()->user();
                            
                            if ($user->role !== 'owner' && $user->outlet_id) {
                                $query->where('outlet_id', $user->outlet_id);
                            }
                            
                            $query->where('role', 'courier')->where('is_active', true);
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Select courier'),
                    
                    Forms\Components\Select::make('type')
                        ->label('Tracking Type')
                        ->options([
                            'pickup' => '📦 Pickup (Jemput dari Customer)',
                            'delivery' => '🚚 Delivery (Antar ke Customer)',
                        ])
                        ->required()
                        ->default('pickup')
                        ->live()
                        ->native(false),
                ])
                ->columns(3),

            Forms\Components\Section::make('Schedule Information')
                ->schema([
                    Forms\Components\DateTimePicker::make('scheduled_time')
                        ->label('Scheduled Time')
                        ->native(false)
                        ->seconds(false)
                        ->required()
                        ->default(now())
                        ->helperText('Waktu yang dijadwalkan untuk pickup/delivery'),
                    
                    Forms\Components\DateTimePicker::make('actual_time')
                        ->label('Actual Time')
                        ->native(false)
                        ->seconds(false)
                        ->helperText('Waktu aktual ketika selesai (opsional)'),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Address Information 📍')
                ->schema([
                    Forms\Components\Textarea::make('pickup_address')
                        ->label('Pickup Address')
                        ->rows(3)
                        ->placeholder('Alamat penjemputan laundry...')
                        ->visible(fn ($get) => $get('type') === 'pickup')
                        ->helperText('Alamat untuk menjemput laundry')
                        ->columnSpanFull(),
                    
                    Forms\Components\Placeholder::make('pickup_address_map')
                        ->label('')
                        ->content(function ($get) {
                            $address = $get('pickup_address');
                            
                            if ($address) {
                                $encodedAddress = urlencode($address);
                                return new \Illuminate\Support\HtmlString(
                                    '<a href="https://www.google.com/maps/search/?api=1&query=' . $encodedAddress . '" 
                                        target="_blank" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        🗺️ Open Pickup Address in Google Maps
                                    </a>'
                                );
                            }
                            
                            return '<span class="text-gray-500">📍 Enter pickup address first to view on map</span>';
                        })
                        ->visible(fn ($get) => $get('type') === 'pickup')
                        ->columnSpanFull(),
                    
                    Forms\Components\Textarea::make('delivery_address')
                        ->label('Delivery Address')
                        ->rows(3)
                        ->placeholder('Alamat pengiriman laundry...')
                        ->visible(fn ($get) => $get('type') === 'delivery')
                        ->helperText('Alamat untuk mengantar laundry')
                        ->columnSpanFull(),
                    
                    Forms\Components\Placeholder::make('delivery_address_map')
                        ->label('')
                        ->content(function ($get) {
                            $address = $get('delivery_address');
                            
                            if ($address) {
                                $encodedAddress = urlencode($address);
                                return new \Illuminate\Support\HtmlString(
                                    '<a href="https://www.google.com/maps/search/?api=1&query=' . $encodedAddress . '" 
                                        target="_blank" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        🗺️ Open Delivery Address in Google Maps
                                    </a>'
                                );
                            }
                            
                            return '<span class="text-gray-500">📍 Enter delivery address first to view on map</span>';
                        })
                        ->visible(fn ($get) => $get('type') === 'delivery')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->collapsible(),

            Forms\Components\Section::make('GPS Coordinates (Optional)')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('latitude')
                                ->numeric()
                                ->step(0.0000001)
                                ->placeholder('-6.200000')
                                ->helperText('GPS Latitude')
                                ->suffix('°')
                                ->minValue(-90)
                                ->maxValue(90),
                            
                            Forms\Components\TextInput::make('longitude')
                                ->numeric()
                                ->step(0.0000001)
                                ->placeholder('106.816666')
                                ->helperText('GPS Longitude')
                                ->suffix('°')
                                ->minValue(-180)
                                ->maxValue(180),
                        ]),
                    
                    Forms\Components\Placeholder::make('map_link')
                        ->label('View GPS Location')
                        ->content(function ($get) {
                            $lat = $get('latitude');
                            $lng = $get('longitude');
                            
                            if ($lat && $lng) {
                                return new \Illuminate\Support\HtmlString(
                                    '<a href="https://www.google.com/maps?q=' . $lat . ',' . $lng . '" 
                                        target="_blank" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        📍 Open GPS Coordinates in Google Maps
                                    </a>
                                    <div class="mt-2 text-sm text-gray-600">
                                        Coordinates: ' . number_format($lat, 7) . ', ' . number_format($lng, 7) . '
                                    </div>'
                                );
                            }
                            
                            return '<span class="text-gray-500">💡 Koordinat GPS opsional, akan diisi otomatis oleh courier saat pickup/delivery</span>';
                        })
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(),

            Forms\Components\Section::make('Status & Documentation')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->required()
                        ->options([
                            'pending' => '⏳ Pending',
                            'picked_up' => '📦 Picked Up',
                            'in_transit' => '🚚 In Transit',
                            'out_for_delivery' => '🚀 Out for Delivery',
                            'delivered' => '✅ Delivered',
                            'failed' => '❌ Failed',
                            'returned' => '↩️ Returned',
                        ])
                        ->native(false)
                        ->searchable()
                        ->default('pending'),
                    
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Catatan tambahan tentang tracking ini...')
                        ->rows(3)
                        ->columnSpanFull(),
                    
                    Forms\Components\FileUpload::make('photo')
                        ->label('Photo Proof')
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                        ->maxSize(2048)
                        ->directory('tracking-photos')
                        ->helperText('Upload foto bukti pickup/delivery (max 2MB)')
                        ->columnSpanFull(),
                ])
                ->columns(1),
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
        ->copyable()
        ->tooltip('Click to copy')
        ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
        ->weight('semibold')
        ->color('primary'),
    
    Tables\Columns\BadgeColumn::make('type')
        ->label('Type')
        ->colors([
            'info' => 'pickup',
            'success' => 'delivery',
        ])
        ->icons([
            'heroicon-o-arrow-down-tray' => 'pickup',
            'heroicon-o-truck' => 'delivery',
        ])
        ->formatStateUsing(fn (string $state): string => ucfirst($state))
        ->sortable()
        ->searchable(),
    
    Tables\Columns\TextColumn::make('courier.name')
        ->label('Courier')
        ->sortable()
        ->searchable()
        ->icon('heroicon-m-user')
        ->weight('medium')
        ->default('Not Assigned')
        ->color(fn ($record) => $record->courier_id ? 'success' : 'gray'),
    
    Tables\Columns\BadgeColumn::make('status')
        ->label('Status')
        ->colors([
            'secondary' => 'pending',
            'info' => 'picked_up',
            'warning' => 'in_transit',
            'primary' => 'out_for_delivery',
            'success' => 'delivered',
            'danger' => 'failed',
            'gray' => 'returned',
        ])
        ->icons([
            'heroicon-o-clock' => 'pending',
            'heroicon-o-hand-raised' => 'picked_up',
            'heroicon-o-truck' => 'in_transit',
            'heroicon-o-map-pin' => 'out_for_delivery',
            'heroicon-o-check-circle' => 'delivered',
            'heroicon-o-x-circle' => 'failed',
            'heroicon-o-arrow-uturn-left' => 'returned',
        ])
        ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
        ->sortable()
        ->searchable(),
    
    Tables\Columns\TextColumn::make('scheduled_time')
        ->label('Scheduled')
        ->dateTime('d M Y, H:i')
        ->sortable()
        ->description(fn ($record): ?string => 
            $record->scheduled_time ? $record->scheduled_time->diffForHumans() : null
        )
        ->color('warning')
        ->icon('heroicon-o-calendar'),
    
    Tables\Columns\TextColumn::make('actual_time')
        ->label('Actual Time')
        ->dateTime('d M Y, H:i')
        ->sortable()
        ->placeholder('Not completed yet')
        ->toggleable(isToggledHiddenByDefault: true)
        ->color('success')
        ->icon('heroicon-o-check-badge'),
    
    Tables\Columns\TextColumn::make('pickup_address')
        ->label('Address')
        ->limit(30)
        ->tooltip(fn ($record) => $record->pickup_address ?? $record->delivery_address)
        ->formatStateUsing(fn ($record) => $record->pickup_address ?? $record->delivery_address ?? 'N/A')
        ->wrap()
        ->searchable(['pickup_address', 'delivery_address'])
        ->icon('heroicon-o-map-pin')
        ->color('info'),
    
    Tables\Columns\TextColumn::make('coordinates')
        ->label('Location')
        ->state(function ($record): string {
            if ($record->latitude && $record->longitude) {
                return number_format($record->latitude, 6) . ', ' . number_format($record->longitude, 6);
            }
            return 'Not set';
        })
        ->url(function ($record): ?string {
            if ($record->latitude && $record->longitude) {
                return 'https://www.google.com/maps?q=' . $record->latitude . ',' . $record->longitude;
            }
            return null;
        })
        ->openUrlInNewTab()
        ->icon('heroicon-m-map')
        ->color('primary')
        ->tooltip('Click to view on Google Maps')
        ->toggleable(isToggledHiddenByDefault: false),
    
    Tables\Columns\TextColumn::make('latitude')
        ->label('Lat')
        ->numeric(decimalPlaces: 6)
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true)
        ->copyable()
        ->tooltip('Latitude coordinate'),
    
    Tables\Columns\TextColumn::make('longitude')
        ->label('Lng')
        ->numeric(decimalPlaces: 6)
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true)
        ->copyable()
        ->tooltip('Longitude coordinate'),
    
    Tables\Columns\IconColumn::make('photo')
        ->label('Photo')
        ->boolean()
        ->trueIcon('heroicon-o-camera')
        ->falseIcon('heroicon-o-x-mark')
        ->trueColor('success')
        ->falseColor('gray')
        ->tooltip(fn ($record): string => 
            $record->photo ? 'Photo uploaded' : 'No photo'
        )
        ->alignCenter()
        ->toggleable(),
    
    Tables\Columns\TextColumn::make('notes')
        ->label('Notes')
        ->limit(50)
        ->tooltip(fn ($record): ?string => $record->notes)
        ->wrap()
        ->placeholder('No notes')
        ->toggleable(isToggledHiddenByDefault: true)
        ->searchable(),
    
    Tables\Columns\TextColumn::make('created_at')
        ->label('Created At')
        ->dateTime('d M Y, H:i')
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: false)
        ->description(fn ($record): string => $record->created_at->diffForHumans())
        ->color('gray'),
    
    Tables\Columns\TextColumn::make('updated_at')
        ->label('Last Updated')
        ->dateTime('d M Y, H:i')
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true)
        ->since()
        ->color('gray'),
])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'picked_up' => 'Picked Up',
                        'in_transit' => 'In Transit',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                        'returned' => 'Returned',
                    ])
                    ->multiple()
                    ->label('Filter by Status'),
                
                Tables\Filters\SelectFilter::make('courier')
                    ->relationship('courier', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by Courier'),
                
                Tables\Filters\Filter::make('has_location')
                    ->label('Has Location')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('latitude')->whereNotNull('longitude'))
                    ->toggle(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('tracked_from')
                            ->label('Tracked from'),
                        Forms\Components\DatePicker::make('tracked_until')
                            ->label('Tracked until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tracked_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['tracked_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
           ->actions([
    Tables\Actions\ActionGroup::make([
        Tables\Actions\ViewAction::make()
            ->color('info'),

        Tables\Actions\EditAction::make()
            ->color('warning'),

        // ✅ UPDATE STATUS BUTTON
        Tables\Actions\Action::make('update_status')
            ->label('Update Status')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->form([
                Forms\Components\Select::make('status')
                    ->label('New Status')
                    ->options([
                        'pending' => 'Pending',
                        'picked_up' => 'Picked Up',
                        'in_transit' => 'In Transit',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                        'returned' => 'Returned',
                    ])
                    ->required()
                    ->native(false),
            ])
            ->action(fn (array $data, Tracking $record) =>
                $record->update(['status' => $data['status']])
            )
            ->requiresConfirmation()
            ->modalHeading('Update Tracking Status')
            ->modalButton('Update'),

        Tables\Actions\Action::make('open_address_map')
            ->label('Open Address in Map')
            ->icon('heroicon-o-map')
            ->color('success')
            ->visible(fn (Tracking $record) =>
                !empty($record->pickup_address) || !empty($record->delivery_address)
            )
            ->url(fn (Tracking $record) =>
                'https://www.google.com/maps/search/?api=1&query=' .
                urlencode($record->pickup_address ?? $record->delivery_address)
            )
            ->openUrlInNewTab(),

        Tables\Actions\Action::make('view_coordinates_map')
            ->label('View GPS Location')
            ->icon('heroicon-o-map-pin')
            ->color('info')
            ->url(fn (Tracking $record) =>
                $record->latitude && $record->longitude
                    ? 'https://www.google.com/maps?q=' . $record->latitude . ',' . $record->longitude
                    : null
            )
            ->openUrlInNewTab()
            ->visible(fn (Tracking $record) =>
                $record->latitude && $record->longitude
            ),

        Tables\Actions\DeleteAction::make()
            ->requiresConfirmation(),
    ])
    ->label('Actions')
    ->icon('heroicon-m-ellipsis-vertical')
    ->size('sm')
    ->color('gray')
    ->button(),
])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-m-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'picked_up' => 'Picked Up',
                                    'in_transit' => 'In Transit',
                                    'out_for_delivery' => 'Out for Delivery',
                                    'delivered' => 'Delivered',
                                    'failed' => 'Failed',
                                    'returned' => 'Returned',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each->update(['status' => $data['status']]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
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
            'index' => Pages\ListTrackings::route('/'),
            'create' => Pages\CreateTracking::route('/create'),
            'edit' => Pages\EditTracking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['in_transit', 'out_for_delivery'])->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::whereIn('status', ['in_transit', 'out_for_delivery'])->count();
        
        return match (true) {
            $count === 0 => 'success',
            $count < 5 => 'warning',
            default => 'danger',
        };
    }
}