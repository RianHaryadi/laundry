<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Filament\Resources\AuditLogResource\RelationManagers;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool
    {
        return false; // Audit logs should not be manually created
    }

    public static function canEdit($record): bool
    {
        return false; // Audit logs should not be edited
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('owner'); // Only super admin can delete
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),

                        Forms\Components\TextInput::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Event Information')
                    ->schema([
                        Forms\Components\Select::make('event')
                            ->label('Event Type')
                            ->options([
                                'created' => 'Created',
                                'updated' => 'Updated',
                                'deleted' => 'Deleted',
                                'restored' => 'Restored',
                                'login' => 'Login',
                                'logout' => 'Logout',
                                'failed_login' => 'Failed Login',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('auditable_type')
                            ->label('Model Type')
                            ->disabled(),

                        Forms\Components\TextInput::make('auditable_id')
                            ->label('Model ID')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Changes')
                    ->schema([
                        Forms\Components\Textarea::make('old_values')
                            ->label('Old Values')
                            ->rows(5)
                            ->disabled()
                            ->formatStateUsing(fn ($state) => 
                                is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT)
                            )
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('new_values')
                            ->label('New Values')
                            ->rows(5)
                            ->disabled()
                            ->formatStateUsing(fn ($state) => 
                                is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT)
                            )
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Timestamp')
                            ->disabled(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->description(fn ($record): string => $record->created_at->diffForHumans())
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->placeholder('System')
                    ->description(fn ($record): ?string => $record->user?->email),

                Tables\Columns\BadgeColumn::make('event')
                    ->label('Event')
                    ->colors([
                        'success' => 'created',
                        'info' => 'updated',
                        'danger' => 'deleted',
                        'warning' => 'restored',
                        'primary' => 'login',
                        'secondary' => 'logout',
                        'danger' => 'failed_login',
                    ])
                    ->icons([
                        'heroicon-o-plus-circle' => 'created',
                        'heroicon-o-pencil-square' => 'updated',
                        'heroicon-o-trash' => 'deleted',
                        'heroicon-o-arrow-path' => 'restored',
                        'heroicon-o-arrow-right-on-rectangle' => 'login',
                        'heroicon-o-arrow-left-on-rectangle' => 'logout',
                        'heroicon-o-exclamation-triangle' => 'failed_login',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? ucfirst(str_replace('_', ' ', $state)) : 'N/A'
                    )
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? class_basename($state) : 'N/A'
                    )
                    ->icon('heroicon-m-cube')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): ?string => 
                        $record->auditable_id ? "ID: {$record->auditable_id}" : null
                    ),

                Tables\Columns\TextColumn::make('auditable.name')
                    ->label('Record')
                    ->getStateUsing(function ($record) {
                        if (!$record->auditable) {
                            return 'Deleted';
                        }
                        
                        // Try common name fields
                        return $record->auditable->name 
                            ?? $record->auditable->title 
                            ?? $record->auditable->code 
                            ?? "ID: {$record->auditable_id}";
                    })
                    ->placeholder('N/A')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('changes_summary')
                    ->label('Changes')
                    ->getStateUsing(function ($record): string {
                        if ($record->event === 'created') {
                            return 'Record created';
                        }
                        
                        if ($record->event === 'deleted') {
                            return 'Record deleted';
                        }
                        
                        $oldValues = is_string($record->old_values) 
                            ? json_decode($record->old_values, true) 
                            : $record->old_values;
                        
                        $newValues = is_string($record->new_values) 
                            ? json_decode($record->new_values, true) 
                            : $record->new_values;
                        
                        if (!$oldValues || !$newValues) {
                            return 'No changes';
                        }
                        
                        $changedFields = array_keys(array_diff_assoc($newValues, $oldValues));
                        $count = count($changedFields);
                        
                        if ($count === 0) {
                            return 'No changes';
                        }
                        
                        $preview = implode(', ', array_slice($changedFields, 0, 3));
                        return $count > 3 
                            ? "{$preview} (+". ($count - 3) ." more)" 
                            : $preview;
                    })
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->icon('heroicon-m-globe-alt')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyMessage('IP copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->user_agent)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by User'),

                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'failed_login' => 'Failed Login',
                    ])
                    ->multiple()
                    ->label('Filter by Event'),

                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label('Filter by Model')
                    ->options(function () {
                        return AuditLog::query()
                            ->distinct()
                            ->pluck('auditable_type')
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                            ->toArray();
                    })
                    ->multiple(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until')
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
                    }),

                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereDate('created_at', today())
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('security_events')
                    ->label('Security Events')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereIn('event', ['login', 'logout', 'failed_login', 'deleted'])
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('view_changes')
                    ->label('View Changes')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Changes - {$record->event}")
                    ->modalContent(fn ($record) => view('filament.resources.audit-log.view-changes', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn ($record) => in_array($record->event, ['updated', 'created', 'deleted'])),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->action(function ($record) {
                        // Restore logic here
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => 
                        $record->event === 'deleted' && auth()->user()?->hasRole('owner')
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('owner')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->hasRole('owner')),

                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('gray')
                        ->action(function ($records) {
                            // Export logic here
                        }),
                ]),
            ])
            ->emptyStateHeading('No audit logs yet')
            ->emptyStateDescription('System activities will be logged here.')
            ->emptyStateIcon('heroicon-o-shield-check')
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
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $todayCount = static::getModel()::whereDate('created_at', today())->count();
        return $todayCount > 0 ? (string) $todayCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $failedLogins = static::getModel()::where('event', 'failed_login')
            ->whereDate('created_at', today())
            ->count();
        
        return $failedLogins > 5 ? 'danger' : 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Activities Today';
    }
}