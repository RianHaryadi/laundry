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
                            ->placeholder('Select order')
                            ->helperText('Select the order to track'),
                        
                        Forms\Components\Select::make('courier_id')
                            ->label('Courier')
                            ->relationship('courier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select courier')
                            ->helperText('Assign courier for this tracking'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location Information')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001)
                            ->placeholder('-6.200000')
                            ->helperText('Example: -6.200000')
                            ->suffix('°')
                            ->minValue(-90)
                            ->maxValue(90),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001)
                            ->placeholder('106.816666')
                            ->helperText('Example: 106.816666')
                            ->suffix('°')
                            ->minValue(-180)
                            ->maxValue(180),
                        
                        Forms\Components\Placeholder::make('map_link')
                            ->label('View on Map')
                            ->content(function ($get) {
                                $lat = $get('latitude');
                                $lng = $get('longitude');
                                
                                if ($lat && $lng) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<a href="https://www.google.com/maps?q=' . $lat . ',' . $lng . '" 
                                            target="_blank" 
                                            class="text-primary-600 hover:underline flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Open in Google Maps
                                        </a>'
                                    );
                                }
                                
                                return 'Enter coordinates to view on map';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Status Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'picked_up' => 'Picked Up',
                                'in_transit' => 'In Transit',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                                'failed' => 'Failed',
                                'returned' => 'Returned',
                            ])
                            ->native(false)
                            ->searchable()
                            ->placeholder('Select status')
                            ->default('pending'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Additional notes about this tracking update...')
                            ->rows(3)
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
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT)),
                
                Tables\Columns\TextColumn::make('courier.name')
                    ->label('Courier')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->weight('medium'),
                
                Tables\Columns\BadgeColumn::make('status')
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
                
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Lat')
                    ->numeric(decimalPlaces: 6)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->copyable()
                    ->tooltip('Latitude coordinate'),
                
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Lng')
                    ->numeric(decimalPlaces: 6)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->copyable()
                    ->tooltip('Longitude coordinate'),
                
                Tables\Columns\TextColumn::make('coordinates')
                    ->label('Location')
                    ->state(function (Tracking $record): string {
                        if ($record->latitude && $record->longitude) {
                            return $record->latitude . ', ' . $record->longitude;
                        }
                        return 'N/A';
                    })
                    ->url(function (Tracking $record): ?string {
                        if ($record->latitude && $record->longitude) {
                            return 'https://www.google.com/maps?q=' . $record->latitude . ',' . $record->longitude;
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-map')
                    ->color('primary')
                    ->tooltip('Click to view on map')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tracked At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_map')
                    ->label('View Map')
                    ->icon('heroicon-m-map')
                    ->color('info')
                    ->url(fn (Tracking $record): ?string => 
                        $record->latitude && $record->longitude 
                            ? 'https://www.google.com/maps?q=' . $record->latitude . ',' . $record->longitude
                            : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Tracking $record): bool => $record->latitude && $record->longitude),
                Tables\Actions\DeleteAction::make(),
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