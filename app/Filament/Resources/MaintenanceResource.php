<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Machine Management';

    protected static ?string $navigationLabel = 'Maintenance';

    protected static ?string $modelLabel = 'Maintenance';

    protected static ?string $pluralModelLabel = 'Maintenance Records';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Machine Information')
                    ->schema([
                        Forms\Components\Select::make('machine_id')
                            ->label('Machine')
                            ->relationship('machine', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select machine')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $machine = \App\Models\Machine::find($state);
                                    if ($machine) {
                                        $set('machine_info_display', $machine->serial_number ?? 'N/A');
                                        $set('machine_outlet_display', $machine->outlet->name ?? 'N/A');
                                    }
                                }
                            }),

                        Forms\Components\Placeholder::make('machine_details')
                            ->label('Machine Details')
                            ->content(function (Forms\Get $get): string {
                                if ($get('machine_id')) {
                                    $machine = \App\Models\Machine::find($get('machine_id'));
                                    if ($machine) {
                                        $details = [];
                                        if ($machine->serial_number) {
                                            $details[] = 'Serial: ' . $machine->serial_number;
                                        }
                                        if ($machine->outlet) {
                                            $details[] = 'Outlet: ' . $machine->outlet->name;
                                        }
                                        if ($machine->type) {
                                            $details[] = 'Type: ' . ucfirst($machine->type);
                                        }

                                        return implode(' | ', $details) ?: 'No details available';
                                    }
                                }
                                return 'Select a machine to see details';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Description & Issues')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Maintenance Description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Scheduling & Personnel')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Maintenance Date')
                            ->required()
                            ->native(false)
                            ->default(now()),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Start Time')
                            ->seconds(false)
                            ->native(false),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('End Time')
                            ->seconds(false)
                            ->native(false)
                            ->after('start_time'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Cost Information')
                    ->schema([
                        Forms\Components\TextInput::make('cost')
                            ->label('Maintenance Cost')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $set('formatted_cost', 'Rp ' . number_format((float)$state, 0, ',', '.'));
                                }
                            }),

                        Forms\Components\Placeholder::make('formatted_cost')
                            ->label('Formatted Cost')
                            ->content(fn (Forms\Get $get): string =>
                                $get('cost')
                                    ? 'Rp ' . number_format((float)$get('cost'), 0, ',', '.')
                                    : 'Rp 0'
                            ),

                        Forms\Components\Textarea::make('cost_breakdown')
                            ->label('Cost Breakdown')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Machine')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-cpu-chip')
                    ->description(fn (Maintenance $record): ?string =>
                        $record->machine->serial_number ?? null
                    ),

                Tables\Columns\TextColumn::make('machine.outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->description(fn ($record): ?string =>
                        $record->date ? $record->date->diffForHumans() : null
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->colors([
                        'warning' => 'maintenance',
                        'success' => 'selesai',
                    ])
                    ->icons([
                        'heroicon-m-cog' => 'maintenance',
                        'heroicon-m-check-circle' => 'selesai',
                    ]),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Cost')
                    ->money('IDR')
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-m-currency-dollar'),

                Tables\Columns\TextColumn::make('next_maintenance_date')
                    ->label('Next Maintenance')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->placeholder('Not scheduled')
                    ->color('warning')
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
                Tables\Filters\SelectFilter::make('machine')
                    ->relationship('machine', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by Machine'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'maintenance' => 'Maintenance',
                        'selesai' => 'Selesai',
                    ])
                    ->multiple()
                    ->label('Filter by Status'),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')->label('Date from'),
                        Forms\Components\DatePicker::make('date_until')->label('Date until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn (Builder $query, $date) =>
                                $query->whereDate('date', '>=', $date)
                            )
                            ->when($data['date_until'], fn (Builder $query, $date) =>
                                $query->whereDate('date', '<=', $date)
                            );
                    }),

                Tables\Filters\Filter::make('cost_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('cost_from')
                                    ->label('Cost from')
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('cost_to')
                                    ->label('Cost to')
                                    ->numeric()
                                    ->prefix('Rp'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['cost_from'], fn (Builder $query, $cost) =>
                                $query->where('cost', '>=', $cost)
                            )
                            ->when($data['cost_to'], fn (Builder $query, $cost) =>
                                $query->where('cost', '<=', $cost)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options([
                                'maintenance' => 'Maintenance',
                                'selesai' => 'Selesai',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(fn (Maintenance $record, array $data) =>
                        $record->update($data)
                    )
                    ->successNotificationTitle('Status updated successfully'),

                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark Selesai')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn (Maintenance $record) =>
                        $record->update(['status' => 'selesai'])
                    )
                    ->requiresConfirmation()
                    ->visible(fn (Maintenance $record) =>
                        $record->status !== 'selesai'
                    )
                    ->successNotificationTitle('Maintenance marked as selesai'),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('gray')
                    ->action(function (Maintenance $record) {
                        $new = $record->replicate();
                        $new->date = now();
                        $new->status = 'maintenance';
                        $new->save();
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Maintenance record duplicated'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'maintenance' => 'Maintenance',
                                    'selesai' => 'Selesai',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(fn (array $data, $records) =>
                            $records->each->update(['status' => $data['status']])
                        )
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->successNotificationTitle('Status updated successfully'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Create Maintenance Record'),
            ])
            ->emptyStateHeading('No maintenance records yet')
            ->emptyStateDescription('Create your first maintenance record.')
            ->emptyStateIcon('heroicon-o-wrench')
            ->defaultSort('date', 'desc')
            ->striped()
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'maintenance')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'maintenance')->count();

        return match (true) {
            $count === 0 => 'success',
            $count < 3 => 'warning',
            default => 'danger',
        };
    }
}
