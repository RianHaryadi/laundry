<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $modelLabel = 'Service';

    protected static ?string $pluralModelLabel = 'Services';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter service name')
                            ->helperText('The name of the service')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->placeholder('Describe this service in detail...')
                            ->helperText('Provide a detailed description of what this service includes')
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Is this service currently available?'),
                    ]),

                Forms\Components\Section::make('Pricing Type')
                    ->schema([
                        Forms\Components\Radio::make('pricing_type')
                            ->label('Pricing Type')
                            ->options([
                                'kg' => 'Per Kilogram',
                                'unit' => 'Per Unit/Item',
                                'both' => 'Both (KG & Unit)',
                            ])
                            ->default('kg')
                            ->required()
                            ->live()
                            ->helperText('Select how this service will be priced')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pricing Information')
                    ->schema([
                        Forms\Components\TextInput::make('price_per_kg')
                            ->label('Price Per Kilogram')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(500)
                            ->minValue(0)
                            ->placeholder('5000')
                            ->helperText('Price per kilogram')
                            ->required(fn (Forms\Get $get) => in_array($get('pricing_type'), ['kg', 'both']))
                            ->visible(fn (Forms\Get $get) => in_array($get('pricing_type'), ['kg', 'both']))
                            ->live(onBlur: true),
                        
                        Forms\Components\TextInput::make('price_per_unit')
                            ->label('Price Per Unit/Item')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('25000')
                            ->helperText('Price per unit or item')
                            ->required(fn (Forms\Get $get) => in_array($get('pricing_type'), ['unit', 'both']))
                            ->visible(fn (Forms\Get $get) => in_array($get('pricing_type'), ['unit', 'both']))
                            ->live(onBlur: true),
                        
                        Forms\Components\Placeholder::make('formatted_prices')
                            ->label('Price Preview')
                            ->content(function (Forms\Get $get): string {
                                $pricingType = $get('pricing_type');
                                $priceKg = $get('price_per_kg');
                                $priceUnit = $get('price_per_unit');
                                
                                $preview = [];
                                
                                if (in_array($pricingType, ['kg', 'both']) && $priceKg) {
                                    $preview[] = 'Per KG: Rp ' . number_format((float)$priceKg, 0, ',', '.');
                                }
                                
                                if (in_array($pricingType, ['unit', 'both']) && $priceUnit) {
                                    $preview[] = 'Per Unit: Rp ' . number_format((float)$priceUnit, 0, ',', '.');
                                }
                                
                                return !empty($preview) ? implode(' | ', $preview) : 'No price set';
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Service Duration')
                    ->schema([
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('Estimated Duration (Hours)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('hours')
                            ->placeholder('24')
                            ->helperText('Estimated time to complete this service')
                            ->default(24),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->description(fn (Service $record): ?string => 
                        $record->description ? \Illuminate\Support\Str::limit($record->description, 60) : null
                    ),
                
                Tables\Columns\TextColumn::make('pricing_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'kg' => 'Per KG',
                        'unit' => 'Per Unit',
                        'both' => 'KG & Unit',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'kg' => 'info',
                        'unit' => 'warning',
                        'both' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price_per_kg')
                    ->label('Price/KG')
                    ->money('IDR')
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-m-currency-dollar')
                    ->placeholder('—')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('price_per_unit')
                    ->label('Price/Unit')
                    ->money('IDR')
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-m-banknotes')
                    ->placeholder('—')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('duration_hours')
                    ->label('Duration')
                    ->suffix(' hrs')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-shopping-cart')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pricing_type')
                    ->label('Pricing Type')
                    ->options([
                        'kg' => 'Per Kilogram',
                        'unit' => 'Per Unit',
                        'both' => 'Both',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All services')
                    ->trueLabel('Active services only')
                    ->falseLabel('Inactive services only'),
                
                Tables\Filters\Filter::make('price_per_kg_range')
                    ->label('Price Per KG Range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price_kg_from')
                                    ->label('Minimum Price/KG')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('price_kg_to')
                                    ->label('Maximum Price/KG')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('50000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_kg_from'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_kg', '>=', $price),
                            )
                            ->when(
                                $data['price_kg_to'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_kg', '<=', $price),
                            );
                    }),
                
                Tables\Filters\Filter::make('price_per_unit_range')
                    ->label('Price Per Unit Range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price_unit_from')
                                    ->label('Minimum Price/Unit')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('price_unit_to')
                                    ->label('Maximum Price/Unit')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('100000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_unit_from'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_unit', '>=', $price),
                            )
                            ->when(
                                $data['price_unit_to'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_unit', '<=', $price),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Service $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Service $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Service $record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn (Service $record) => $record->update(['is_active' => !$record->is_active]))
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('update_price_kg')
                        ->label('Update Price/KG')
                        ->icon('heroicon-m-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\Radio::make('price_action')
                                ->label('Price Action')
                                ->options([
                                    'increase' => 'Increase Price',
                                    'decrease' => 'Decrease Price',
                                    'set' => 'Set Fixed Price',
                                ])
                                ->default('increase')
                                ->required()
                                ->reactive(),
                            
                            Forms\Components\TextInput::make('price_value')
                                ->label(fn (Forms\Get $get) => 
                                    $get('price_action') === 'set' ? 'New Price/KG' : 'Amount'
                                )
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->placeholder('1000'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                if ($record->price_per_kg !== null) {
                                    $currentPrice = $record->price_per_kg;
                                    
                                    $newPrice = match($data['price_action']) {
                                        'increase' => $currentPrice + $data['price_value'],
                                        'decrease' => max(0, $currentPrice - $data['price_value']),
                                        'set' => $data['price_value'],
                                    };
                                    
                                    $record->update(['price_per_kg' => $newPrice]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('update_price_unit')
                        ->label('Update Price/Unit')
                        ->icon('heroicon-m-banknotes')
                        ->color('warning')
                        ->form([
                            Forms\Components\Radio::make('price_action')
                                ->label('Price Action')
                                ->options([
                                    'increase' => 'Increase Price',
                                    'decrease' => 'Decrease Price',
                                    'set' => 'Set Fixed Price',
                                ])
                                ->default('increase')
                                ->required()
                                ->reactive(),
                            
                            Forms\Components\TextInput::make('price_value')
                                ->label(fn (Forms\Get $get) => 
                                    $get('price_action') === 'set' ? 'New Price/Unit' : 'Amount'
                                )
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->placeholder('5000'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                if ($record->price_per_unit !== null) {
                                    $currentPrice = $record->price_per_unit;
                                    
                                    $newPrice = match($data['price_action']) {
                                        'increase' => $currentPrice + $data['price_value'],
                                        'decrease' => max(0, $currentPrice - $data['price_value']),
                                        'set' => $data['price_value'],
                                    };
                                    
                                    $record->update(['price_per_unit' => $newPrice]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Create Service'),
            ])
            ->emptyStateHeading('No services yet')
            ->emptyStateDescription('Create your first service to get started.')
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count === 0 => 'danger',
            $count < 5 => 'warning',
            default => 'success',
        };
    }
}