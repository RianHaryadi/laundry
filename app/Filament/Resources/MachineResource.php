<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineResource\Pages;
use App\Filament\Resources\MachineResource\RelationManagers;
use App\Models\Machine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MachineResource extends Resource
{
    protected static ?string $model = Machine::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Machine Management';

    protected static ?string $navigationLabel = 'Machines';

    protected static ?string $modelLabel = 'Machine';

    protected static ?string $pluralModelLabel = 'Machines';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select outlet')
                            ->helperText('Select the outlet where this machine is located')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('location')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('address')
                                    ->rows(3),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Machine Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Espresso Machine 01')
                            ->helperText('Unique name to identify this machine'),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., SN-2024-001')
                            ->helperText('Manufacturer serial number'),

                        Forms\Components\Select::make('type')
                            ->label('Machine Type')
                            ->options([
                                'washer' => 'Washing Machine',
                                'dryer' => 'Dryer',
                                'ironer' => 'Ironer / Press',
                                'boiler' => 'Boiler / Steam Generator',
                                'conveyor' => 'Conveyor System',
                                'packing' => 'Packing Machine',
                                'other' => 'Other Equipment',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->placeholder('Select machine type'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'operational' => 'Operational',
                                'maintenance' => 'Under Maintenance',
                                'broken' => 'Broken',
                                'retired' => 'Retired',
                            ])
                            ->default('operational')
                            ->required()
                            ->native(false)
                            ->placeholder('Select status'),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Manufacturer')
                            ->maxLength(255)
                            ->placeholder('e.g., La Marzocco'),

                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->maxLength(255)
                            ->placeholder('e.g., Linea PB'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Purchase & Warranty Information')
                    ->schema([
                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Purchase Date')
                            ->native(false)
                            ->placeholder('Select date'),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Purchase Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('0'),

                        Forms\Components\DatePicker::make('warranty_until')
                            ->label('Warranty Until')
                            ->native(false)
                            ->placeholder('Select date')
                            ->after('purchase_date'),

                        Forms\Components\TextInput::make('supplier')
                            ->label('Supplier')
                            ->maxLength(255)
                            ->placeholder('Supplier name'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Maintenance Information')
                    ->schema([
                        Forms\Components\DatePicker::make('last_maintenance')
                            ->label('Last Maintenance Date')
                            ->native(false)
                            ->placeholder('Select date')
                            ->maxDate(now()),

                        Forms\Components\TextInput::make('maintenance_interval')
                            ->label('Maintenance Interval (days)')
                            ->numeric()
                            ->suffix('days')
                            ->minValue(1)
                            ->placeholder('90')
                            ->helperText('How often should this machine be maintained?'),

                        Forms\Components\Placeholder::make('next_maintenance')
                            ->label('Next Scheduled Maintenance')
                            ->content(function (Forms\Get $get): string {
                                $lastMaintenance = $get('last_maintenance');
                                $interval = $get('maintenance_interval');

                                if ($lastMaintenance && $interval) {
                                    $nextDate = \Carbon\Carbon::parse($lastMaintenance)->addDays($interval);
                                    return $nextDate->format('d M Y') . ' (' . $nextDate->diffForHumans() . ')';
                                }

                                return 'Set last maintenance and interval to calculate';
                            }),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('specifications')
                            ->label('Specifications')
                            ->rows(3)
                            ->placeholder('Technical specifications, capacity, power requirements, etc.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Additional notes, special instructions, etc.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Machine Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-cpu-chip')
                    ->description(fn (Machine $record): ?string => $record->serial_number),

                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-storefront')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'washer',
                        'success' => 'dryer',
                        'warning' => 'ironer',
                        'danger' => 'boiler',
                        'info' => 'conveyor',
                        'secondary' => 'packing',
                        'gray' => 'other',
                    ])
                    ->icons([
                        'heroicon-o-arrow-path' => 'washer',
                        'heroicon-o-fire' => 'dryer',
                        'heroicon-o-sparkles' => 'ironer',
                        'heroicon-o-bolt' => 'boiler',
                        'heroicon-o-arrows-right-left' => 'conveyor',
                        'heroicon-o-archive-box' => 'packing',
                        'heroicon-o-wrench' => 'other',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        match($state) {
                            'washer' => 'Washing Machine',
                            'dryer' => 'Dryer',
                            'ironer' => 'Ironer / Press',
                            'boiler' => 'Boiler / Steam',
                            'conveyor' => 'Conveyor',
                            'packing' => 'Packing',
                            'other' => 'Other',
                            default => 'N/A'
                        }
                    )
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'operational',
                        'warning' => 'maintenance',
                        'danger' => 'broken',
                        'secondary' => 'retired',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'operational',
                        'heroicon-o-wrench' => 'maintenance',
                        'heroicon-o-x-circle' => 'broken',
                        'heroicon-o-archive-box' => 'retired',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? ucfirst($state) : 'N/A'
                    )
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Manufacturer')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('last_maintenance')
                    ->label('Last Maintenance')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->description(fn ($record): ?string => 
                        $record->last_maintenance ? $record->last_maintenance->diffForHumans() : null
                    )
                    ->placeholder('Never')
                    ->color(fn ($record): string => 
                        $record->last_maintenance && $record->last_maintenance->diffInDays(now()) > 90 
                            ? 'danger' 
                            : 'success'
                    ),

                Tables\Columns\TextColumn::make('next_maintenance')
                    ->label('Next Maintenance')
                    ->getStateUsing(fn (Machine $record): ?string => 
                        $record->last_maintenance && $record->maintenance_interval
                            ? $record->last_maintenance->addDays($record->maintenance_interval)->format('d M Y')
                            : null
                    )
                    ->icon('heroicon-m-calendar-days')
                    ->color('warning')
                    ->placeholder('Not scheduled')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Purchase Price')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('maintenances_count')
                    ->label('Maintenance Count')
                    ->counts('maintenances')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

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

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'washer' => 'Washing Machine',
                        'dryer' => 'Dryer',
                        'ironer' => 'Ironer / Press',
                        'boiler' => 'Boiler / Steam Generator',
                        'conveyor' => 'Conveyor System',
                        'packing' => 'Packing Machine',
                        'other' => 'Other Equipment',
                    ])
                    ->multiple()
                    ->label('Filter by Type'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'operational' => 'Operational',
                        'maintenance' => 'Under Maintenance',
                        'broken' => 'Broken',
                        'retired' => 'Retired',
                    ])
                    ->multiple()
                    ->label('Filter by Status'),

                Tables\Filters\Filter::make('needs_maintenance')
                    ->label('Needs Maintenance')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('last_maintenance')
                            ->whereNotNull('maintenance_interval')
                            ->whereRaw('DATE_ADD(last_maintenance, INTERVAL maintenance_interval DAY) < NOW()')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('under_warranty')
                    ->label('Under Warranty')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('warranty_until')
                            ->where('warranty_until', '>=', now())
                    )
                    ->toggle(),

                Tables\Filters\TernaryFilter::make('has_serial_number')
                    ->label('Has Serial Number')
                    ->placeholder('All machines')
                    ->trueLabel('With serial number')
                    ->falseLabel('Without serial number')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('serial_number'),
                        false: fn (Builder $query) => $query->whereNull('serial_number'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('schedule_maintenance')
                    ->label('Schedule Maintenance')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->color('warning')
                    ->url(fn (Machine $record): string => 
                        route('filament.admin.resources.maintenances.create', [
                            'machine_id' => $record->id
                        ])
                    )
                    ->visible(fn (Machine $record) => $record->status === 'operational'),

                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-m-arrow-path')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options([
                                'operational' => 'Operational',
                                'maintenance' => 'Under Maintenance',
                                'broken' => 'Broken',
                                'retired' => 'Retired',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(fn (Machine $record, array $data) => $record->update($data))
                    ->successNotificationTitle('Status updated successfully'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'operational' => 'Operational',
                                    'maintenance' => 'Under Maintenance',
                                    'broken' => 'Broken',
                                    'retired' => 'Retired',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(fn (array $data, $records) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('assign_outlet')
                        ->label('Assign to Outlet')
                        ->icon('heroicon-m-building-storefront')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('outlet_id')
                                ->label('Outlet')
                                ->relationship('outlet', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(fn (array $data, $records) => $records->each->update(['outlet_id' => $data['outlet_id']]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Add Machine'),
            ])
            ->emptyStateHeading('No machines yet')
            ->emptyStateDescription('Add your first machine to start tracking equipment.')
            ->emptyStateIcon('heroicon-o-cpu-chip')
            ->defaultSort('name', 'asc')
            ->striped()
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MaintenancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachines::route('/'),
            'create' => Pages\CreateMachine::route('/create'),
            'edit' => Pages\EditMachine::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $totalCount = static::getModel()::count();
        return $totalCount > 0 ? (string) $totalCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $brokenCount = static::getModel()::where('status', 'broken')->count();
        $maintenanceCount = static::getModel()::where('status', 'maintenance')->count();
        
        return match (true) {
            $brokenCount > 0 => 'danger',      
            $maintenanceCount > 0 => 'warning',
            default => 'success',
        };
    }
}