<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Response;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_all')
                ->label('Export All')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $records = AuditLog::orderBy('created_at', 'desc')->get();
                    $filename = 'all_audit_logs_' . now()->format('Y-m-d_His') . '.csv';
                    
                    $headers = [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ];

                    $callback = function() use ($records) {
                        $file = fopen('php://output', 'w');
                        
                        fputcsv($file, [
                            'Timestamp',
                            'User',
                            'Email',
                            'Event',
                            'Model',
                            'Model ID',
                            'Changes',
                            'IP Address',
                            'User Agent'
                        ]);

                        foreach ($records as $record) {
                            fputcsv($file, [
                                $record->created_at->format('Y-m-d H:i:s'),
                                $record->user?->name ?? 'System',
                                $record->user?->email ?? '-',
                                ucfirst(str_replace('_', ' ', $record->event)),
                                $record->auditable_type ? class_basename($record->auditable_type) : '-',
                                $record->auditable_id ?? '-',
                                $this->getChangesSummary($record),
                                $record->ip_address ?? '-',
                                $record->user_agent ?? '-',
                            ]);
                        }

                        fclose($file);
                    };

                    return Response::stream($callback, 200, $headers);
                })
                ->requiresConfirmation()
                ->modalHeading('Export All Audit Logs')
                ->modalDescription('This will export all audit logs to a CSV file. This may take some time if you have many records.')
                ->modalSubmitActionLabel('Export'),

            Action::make('cleanup_old_logs')
                ->label('Cleanup Old Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function () {
                    $deleted = AuditLog::where('created_at', '<', now()->subMonths(6))->delete();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Cleanup Complete')
                        ->body("Deleted {$deleted} old audit logs (older than 6 months)")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Cleanup Old Audit Logs')
                ->modalDescription('This will permanently delete all audit logs older than 6 months. This action cannot be undone.')
                ->modalSubmitActionLabel('Delete Old Logs')
                ->visible(fn () => auth()->user()?->hasRole('owner')),

            Action::make('view_statistics')
                ->label('View Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->url(fn () => static::getUrl())
                ->badge(fn () => AuditLog::whereDate('created_at', today())->count())
                ->badgeColor('success'),
        ];
    }

    // Uncomment this after creating the widget file properly
    protected function getHeaderWidgets(): array
    {
        return [];
        
        // After widget is created, use this:
        // return [
        //     \App\Filament\Resources\AuditLogResource\Widgets\AuditStatsWidget::class,
        // ];
    }

    protected function getChangesSummary($record): string
    {
        if ($record->event === 'created') {
            return 'Record created';
        }
        
        if ($record->event === 'deleted') {
            return 'Record deleted';
        }

        if (in_array($record->event, ['login', 'logout', 'failed_login'])) {
            if ($record->event === 'login') {
                return 'User logged in';
            } elseif ($record->event === 'logout') {
                return 'User logged out';
            } elseif ($record->event === 'failed_login') {
                $info = is_string($record->new_values) 
                    ? json_decode($record->new_values, true) 
                    : $record->new_values;
                $email = $info['email'] ?? 'Unknown';
                return "Failed login attempt: {$email}";
            }
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
        return implode(', ', $changedFields);
    }
}