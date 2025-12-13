<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('owner');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),

                Tables\Columns\BadgeColumn::make('event')
                    ->label('Event')
                    ->colors([
                        'success' => 'created',
                        'info' => 'updated',
                        'danger' => ['deleted', 'failed_login'],
                        'warning' => 'restored',
                        'primary' => 'login',
                        'secondary' => 'logout',
                    ])
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? ucfirst(str_replace('_', ' ', $state)) : 'N/A'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System')
                    ->description(fn ($record): ?string => $record->user?->email),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? class_basename($state) : '-'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('Record')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => $state ? "ID: {$state}" : '-'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->getStateUsing(function ($record): string {
                        $user = $record->user?->name ?? 'System';
                        $model = $record->auditable_type ? class_basename($record->auditable_type) : '';
                        $id = $record->auditable_id;

                        return match($record->event) {
                            'login' => "{$user} logged in successfully",
                            'logout' => "{$user} logged out",
                            'failed_login' => self::getFailedLoginDescription($record),
                            'created' => "{$user} created {$model} #{$id}",
                            'deleted' => "{$user} deleted {$model} #{$id}",
                            'restored' => "{$user} restored {$model} #{$id}",
                            'updated' => self::getUpdateDescription($record, $user, $model, $id),
                            default => 'Unknown action'
                        };
                    })
                    ->searchable()
                    ->wrap()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
            ])
            ->defaultSort('created_at', 'desc')
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

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From'),
                        Forms\Components\DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),

                Tables\Filters\Filter::make('authentication_only')
                    ->label('Authentication Only')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereIn('event', ['login', 'logout', 'failed_login'])
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('view_changes')
                    ->label('View Changes')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Details - " . ucfirst($record->event))
                    ->modalContent(fn ($record) => view('filament.resources.audit-log.view-changes', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('owner')),
                ]),
            ]);
    }

    protected static function getFailedLoginDescription($record): string
    {
        $info = is_array($record->new_values) 
            ? $record->new_values 
            : json_decode($record->new_values ?? '{}', true);
        
        $email = $info['email'] ?? 'Unknown';
        return "Failed login attempt for: {$email}";
    }

    protected static function getUpdateDescription($record, $user, $model, $id): string
    {
        $old = is_array($record->old_values) 
            ? $record->old_values 
            : json_decode($record->old_values ?? '{}', true);
        
        $new = is_array($record->new_values) 
            ? $record->new_values 
            : json_decode($record->new_values ?? '{}', true);
        
        if (empty($old) || empty($new)) {
            return "{$user} updated {$model} #{$id}";
        }
        
        $changed = array_keys(array_diff_assoc($new, $old));
        
        if (empty($changed)) {
            return "{$user} updated {$model} #{$id}";
        }
        
        $fields = implode(', ', array_slice($changed, 0, 3));
        $more = count($changed) > 3 ? ' (+' . (count($changed) - 3) . ' more)' : '';
        
        return "{$user} updated {$model} #{$id}: {$fields}{$more}";
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
}