<?php

namespace App\Filament\Resources\AuditLogResource\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $todayLogs = AuditLog::whereDate('created_at', today())->count();
        $yesterdayLogs = AuditLog::whereDate('created_at', today()->subDay())->count();
        $todayChange = $yesterdayLogs > 0 
            ? round((($todayLogs - $yesterdayLogs) / $yesterdayLogs) * 100, 1) 
            : 0;

        $weekLogins = AuditLog::where('event', 'login')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        
        $lastWeekLogins = AuditLog::where('event', 'login')
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();
        
        $loginChange = $lastWeekLogins > 0 
            ? round((($weekLogins - $lastWeekLogins) / $lastWeekLogins) * 100, 1) 
            : 0;

        $failedLoginsToday = AuditLog::where('event', 'failed_login')
            ->whereDate('created_at', today())
            ->count();

        $failedLoginsYesterday = AuditLog::where('event', 'failed_login')
            ->whereDate('created_at', today()->subDay())
            ->count();

        $activeUsersToday = AuditLog::whereDate('created_at', today())
            ->distinct('user_id')
            ->whereNotNull('user_id')
            ->count('user_id');

        return [
            Stat::make('Today\'s Activities', $todayLogs)
                ->description($todayChange >= 0 ? "+{$todayChange}% from yesterday" : "{$todayChange}% from yesterday")
                ->descriptionIcon($todayChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayChange >= 0 ? 'success' : 'danger')
                ->chart($this->getLastSevenDaysChart()),

            Stat::make('This Week\'s Logins', $weekLogins)
                ->description($loginChange >= 0 ? "+{$loginChange}% from last week" : "{$loginChange}% from last week")
                ->descriptionIcon($loginChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($loginChange >= 0 ? 'success' : 'warning'),

            Stat::make('Failed Logins Today', $failedLoginsToday)
                ->description($failedLoginsYesterday > 0 ? "Yesterday: {$failedLoginsYesterday}" : 'No failed logins yesterday')
                ->descriptionIcon($failedLoginsToday > $failedLoginsYesterday ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($failedLoginsToday > 5 ? 'danger' : ($failedLoginsToday > 0 ? 'warning' : 'success')),

            Stat::make('Active Users Today', $activeUsersToday)
                ->description('Unique users with activities')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Total Audit Logs', AuditLog::count())
                ->description('All time records')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('gray'),
        ];
    }

    protected function getLastSevenDaysChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = AuditLog::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }
}