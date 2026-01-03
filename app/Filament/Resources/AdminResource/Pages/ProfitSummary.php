<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use App\Models\Expense;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProfitSummary extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.profit-summary';
    
    protected static ?string $navigationGroup = 'Financial Reports';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $title = 'Profit Summary';

    public $dateFilter = 'this_month';
    
    public function getSummary(): array
{
    $revenueQuery = Payment::where('status', 'success'); // GANTI dari 'paid' ke 'success'
    $expenseQuery = Expense::query();
    
    // Apply date filter
    $revenueQuery = $this->applyDateFilter($revenueQuery);
    $expenseQuery = $this->applyDateFilter($expenseQuery, 'expense_date');
    
    $totalRevenue = $revenueQuery->sum('amount');
    $totalExpenses = $expenseQuery->sum('amount');
    $netProfit = $totalRevenue - $totalExpenses;
    $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
    
    return [
        'total_revenue' => $totalRevenue,
        'total_expenses' => $totalExpenses,
        'net_profit' => $netProfit,
        'profit_margin' => $profitMargin,
    ];
}
    
    public function getExpenseBreakdown(): array
    {
        $query = Expense::join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name');
        
        $query = $this->applyDateFilter($query, 'expense_date');
        
        return $query->get()->pluck('total', 'name')->toArray();
    }
    
    protected function applyDateFilter($query, $dateColumn = 'created_at')
    {
        return match($this->dateFilter) {
            'today' => $query->whereDate($dateColumn, today()),
            'this_week' => $query->whereBetween($dateColumn, [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth($dateColumn, now()->month)->whereYear($dateColumn, now()->year),
            'this_year' => $query->whereYear($dateColumn, now()->year),
            default => $query,
        };
    }
}