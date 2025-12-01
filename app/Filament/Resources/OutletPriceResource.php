<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletPriceResource\Pages;
use App\Models\OutletPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OutletPriceResource extends Resource
{
    protected static ?string $model = OutletPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Outlet Prices';

    protected static ?string $modelLabel = 'Outlet Price';

    protected static ?string $pluralModelLabel = 'Outlet Prices';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Outlet & Service Selection')
                    ->schema([
                        Forms\Components\Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select outlet')
                            ->helperText('Choose the outlet for this custom pricing')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                // Clear service selection when outlet changes
                                $set('service_id', null);
                            }),
                        
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select service')
                            ->helperText('Choose the service to set custom price')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                // Auto-fill base price from service
                                if ($state) {
                                    $service = \App\Models\Service::find($state);
                                    if ($service && !$get('price')) {
                                        $set('base_price_display', $service->base_price);
                                    }
                                }
                            }),
                        
                        Forms\Components\Placeholder::make('base_price_display')
                            ->label('Service Base Price')
                            ->content(function (Forms\Get $get): string {
                                if ($get('service_id')) {
                                    $service = \App\Models\Service::find($get('service_id'));
                                    if ($service) {
                                        return 'Rp ' . number_format($service->base_price, 0, ',', '.');
                                    }
                                }
                                return 'Select a service to see base price';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Custom Price')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Custom Price for this Outlet')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('100000')
                            ->helperText('Set custom price for this service at this specific outlet')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $set('formatted_price', 'Rp ' . number_format((float)$state, 0, ',', '.'));
                                    
                                    // Calculate difference from base price
                                    if ($get('service_id')) {
                                        $service = \App\Models\Service::find($get('service_id'));
                                        if ($service) {
                                            $difference = (float)$state - (float)$service->base_price;
                                            $percentage = $service->base_price > 0 
                                                ? ($difference / $service->base_price) * 100 
                                                : 0;
                                            
                                            $set('price_difference', $difference);
                                            $set('price_percentage', round($percentage, 2));
                                        }
                                    }
                                }
                            }),
                        
                        Forms\Components\Placeholder::make('formatted_price')
                            ->label('Formatted Price')
                            ->content(fn (Forms\Get $get): string => 
                                $get('price') 
                                    ? 'Rp ' . number_format((float)$get('price'), 0, ',', '.') 
                                    : 'Rp 0'
                            ),
                        
                        Forms\Components\Placeholder::make('price_comparison')
                            ->label('Price Comparison')
                            ->content(function (Forms\Get $get): string {
                                $difference = $get('price_difference') ?? 0;
                                $percentage = $get('price_percentage') ?? 0;
                                
                                if ($difference == 0) {
                                    return 'Same as base price';
                                }
                                
                                $color = $difference > 0 ? 'text-success-600' : 'text-danger-600';
                                $sign = $difference > 0 ? '+' : '';
                                $arrow = $difference > 0 ? '↑' : '↓';
                                
                                return new \Illuminate\Support\HtmlString(
                                    '<span class="' . $color . ' font-semibold">' . 
                                    $arrow . ' Rp ' . number_format(abs($difference), 0, ',', '.') . 
                                    ' (' . $sign . number_format($percentage, 2) . '%)</span>'
                                );
                            })
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('price') && $get('service_id')),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Reason for custom pricing...')
                            ->rows(3)
                            ->helperText('Optional: Add notes about why this custom price is set')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->weight('medium')
                    ->description(fn (OutletPrice $record): ?string => 
                        $record->outlet->address ?? null
                    ),
                
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->weight('medium')
                    ->description(fn (OutletPrice $record): ?string => 
                        $record->service->description 
                            ? \Illuminate\Support\Str::limit($record->service->description, 50) 
                            : null
                    ),
                
                Tables\Columns\TextColumn::make('service.base_price')
                    ->label('Base Price')
                    ->money('IDR')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-m-banknotes')
                    ->description('Original price'),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Custom Price')
                    ->money('IDR')
                    ->sortable()
                    ->weight('semibold')
                    ->color('success')
                    ->icon('heroicon-m-tag')
                    ->description(function (OutletPrice $record): ?string {
                        $basePrice = $record->service->base_price ?? 0;
                        $customPrice = $record->price;
                        $difference = $customPrice - $basePrice;
                        
                        if ($difference == 0) return 'Same as base';
                        
                        $percentage = $basePrice > 0 ? ($difference / $basePrice) * 100 : 0;
                        $sign = $difference > 0 ? '+' : '';
                        
                        return $sign . 'Rp ' . number_format(abs($difference), 0, ',', '.') . 
                               ' (' . $sign . number_format($percentage, 1) . '%)';
                    }),
                
                Tables\Columns\BadgeColumn::make('price_status')
                    ->label('Status')
                    ->getStateUsing(function (OutletPrice $record): string {
                        $basePrice = $record->service->base_price ?? 0;
                        $customPrice = $record->price;
                        
                        if ($customPrice > $basePrice) return 'higher';
                        if ($customPrice < $basePrice) return 'lower';
                        return 'same';
                    })
                    ->colors([
                        'success' => 'higher',
                        'danger' => 'lower',
                        'secondary' => 'same',
                    ])
                    ->icons([
                        'heroicon-o-arrow-trending-up' => 'higher',
                        'heroicon-o-arrow-trending-down' => 'lower',
                        'heroicon-o-minus' => 'same',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(false),
                
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
                Tables\Filters\SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by Outlet'),
                
                Tables\Filters\SelectFilter::make('service')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by Service'),
                
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price_from')
                                    ->label('Price from')
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('price_to')
                                    ->label('Price to')
                                    ->numeric()
                                    ->prefix('Rp'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['price_from'] ?? null) {
                            $indicators[] = 'Price from: Rp ' . number_format($data['price_from'], 0, ',', '.');
                        }
                        
                        if ($data['price_to'] ?? null) {
                            $indicators[] = 'Price to: Rp ' . number_format($data['price_to'], 0, ',', '.');
                        }
                        
                        return $indicators;
                    }),
                
                Tables\Filters\Filter::make('higher_than_base')
                    ->label('Higher than Base Price')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereRaw('price > (SELECT base_price FROM services WHERE services.id = outlet_prices.service_id)')
                    )
                    ->toggle(),
                
                Tables\Filters\Filter::make('lower_than_base')
                    ->label('Lower than Base Price')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereRaw('price < (SELECT base_price FROM services WHERE services.id = outlet_prices.service_id)')
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset_to_base')
                    ->label('Reset to Base')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->action(function (OutletPrice $record) {
                        $record->update(['price' => $record->service->base_price]);
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Price reset to base price'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('adjust_prices')
                        ->label('Adjust Prices')
                        ->icon('heroicon-m-calculator')
                        ->color('warning')
                        ->form([
                            Forms\Components\Radio::make('adjustment_type')
                                ->label('Adjustment Type')
                                ->options([
                                    'increase_percent' => 'Increase by Percentage',
                                    'decrease_percent' => 'Decrease by Percentage',
                                    'increase_amount' => 'Increase by Amount',
                                    'decrease_amount' => 'Decrease by Amount',
                                    'reset_base' => 'Reset to Base Price',
                                ])
                                ->default('increase_percent')
                                ->required()
                                ->reactive(),
                            
                            Forms\Components\TextInput::make('adjustment_value')
                                ->label(function (Forms\Get $get) {
                                    return match($get('adjustment_type')) {
                                        'increase_percent', 'decrease_percent' => 'Percentage (%)',
                                        'increase_amount', 'decrease_amount' => 'Amount (Rp)',
                                        default => 'Value',
                                    };
                                })
                                ->numeric()
                                ->required(fn (Forms\Get $get) => $get('adjustment_type') !== 'reset_base')
                                ->visible(fn (Forms\Get $get) => $get('adjustment_type') !== 'reset_base')
                                ->prefix(fn (Forms\Get $get) => 
                                    in_array($get('adjustment_type'), ['increase_percent', 'decrease_percent']) ? '%' : 'Rp'
                                )
                                ->placeholder(fn (Forms\Get $get) => 
                                    in_array($get('adjustment_type'), ['increase_percent', 'decrease_percent']) ? '10' : '10000'
                                )
                                ->minValue(0),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $currentPrice = $record->price;
                                
                                $newPrice = match($data['adjustment_type']) {
                                    'increase_percent' => $currentPrice * (1 + ($data['adjustment_value'] / 100)),
                                    'decrease_percent' => $currentPrice * (1 - ($data['adjustment_value'] / 100)),
                                    'increase_amount' => $currentPrice + $data['adjustment_value'],
                                    'decrease_amount' => max(0, $currentPrice - $data['adjustment_value']),
                                    'reset_base' => $record->service->base_price,
                                };
                                
                                $record->update(['price' => round($newPrice)]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->successNotificationTitle('Prices adjusted successfully'),
                    
                    Tables\Actions\BulkAction::make('duplicate_to_outlets')
                        ->label('Duplicate to Other Outlets')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('target_outlets')
                                ->label('Target Outlets')
                                ->relationship('outlet', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('Select outlets to copy these prices to'),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                foreach ($data['target_outlets'] as $outletId) {
                                    OutletPrice::updateOrCreate(
                                        [
                                            'outlet_id' => $outletId,
                                            'service_id' => $record->service_id,
                                        ],
                                        [
                                            'price' => $record->price,
                                        ]
                                    );
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->successNotificationTitle('Prices duplicated successfully'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Create Custom Price'),
            ])
            ->emptyStateHeading('No custom prices yet')
            ->emptyStateDescription('Set custom prices for services at specific outlets.')
            ->emptyStateIcon('heroicon-o-tag')
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
            'index' => Pages\ListOutletPrices::route('/'),
            'create' => Pages\CreateOutletPrice::route('/create'),
            'edit' => Pages\EditOutletPrice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}