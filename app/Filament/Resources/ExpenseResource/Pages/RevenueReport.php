<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\DB;

class RevenueReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string $view = 'filament.pages.revenue-report';
    
    protected static ?string $navigationGroup = 'Financial Reports';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $title = 'Revenue Report';

    public $dateFilter = 'this_month';
    
    public function getStats(): array
    {
        $query = Payment::where('status', 'success');
        
        // Apply date filter
        $query = $this->applyDateFilter($query);
        
        $totalRevenue = $query->sum('amount');
        $totalTransactions = $query->count();
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        // Calculate growth (compare with previous period)
        $previousQuery = Payment::where('status', 'success');
        $previousQuery = $this->applyPreviousDateFilter($previousQuery);
        $previousRevenue = $previousQuery->sum('amount');
        
        $growth = $previousRevenue > 0 
            ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;
        
        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'avg_transaction' => $avgTransaction,
            'growth' => $growth,
        ];
    }
    
    public function getRevenueByService(): array
    {
        $query = Payment::where('payments.status', 'success')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('services.name');
        
        $query = $this->applyDateFilter($query, 'payments');
        
        return $query->get()->pluck('total', 'name')->toArray();
    }
    
    public function getRevenueByPaymentMethod(): array
    {
        $query = Payment::where('status', 'success')
            ->select('gateway as payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('gateway');
        
        $query = $this->applyDateFilter($query);
        
        return $query->get()->pluck('total', 'payment_method')->toArray();
    }
    
    protected function applyDateFilter($query, $table = null)
    {
        $dateColumn = $table ? "{$table}.created_at" : 'created_at';
        
        return match($this->dateFilter) {
            'today' => $query->whereDate($dateColumn, today()),
            'this_week' => $query->whereBetween($dateColumn, [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth($dateColumn, now()->month)->whereYear($dateColumn, now()->year),
            'this_year' => $query->whereYear($dateColumn, now()->year),
            default => $query,
        };
    }
    
    protected function applyPreviousDateFilter($query)
    {
        return match($this->dateFilter) {
            'today' => $query->whereDate('created_at', today()->subDay()),
            'this_week' => $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
            'this_year' => $query->whereYear('created_at', now()->subYear()->year),
            default => $query->where('id', 0), // Return empty
        };
    }
}