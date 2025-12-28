<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Date Filter --}}
        <div class="flex gap-2">
            <x-filament::button 
                wire:click="$set('dateFilter', 'today')"
                :color="$dateFilter === 'today' ? 'primary' : 'gray'"
                size="sm"
            >
                Today
            </x-filament::button>
            
            <x-filament::button 
                wire:click="$set('dateFilter', 'this_week')"
                :color="$dateFilter === 'this_week' ? 'primary' : 'gray'"
                size="sm"
            >
                This Week
            </x-filament::button>
            
            <x-filament::button 
                wire:click="$set('dateFilter', 'this_month')"
                :color="$dateFilter === 'this_month' ? 'primary' : 'gray'"
                size="sm"
            >
                This Month
            </x-filament::button>
            
            <x-filament::button 
                wire:click="$set('dateFilter', 'this_year')"
                :color="$dateFilter === 'this_year' ? 'primary' : 'gray'"
                size="sm"
            >
                This Year
            </x-filament::button>
        </div>

        {{-- Profit Summary Card --}}
        @php
            $summary = $this->getSummary();
        @endphp
        
        <x-filament::section>
            <x-slot name="heading">
                <div class="text-xl font-bold">Profit & Loss Statement</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Period: {{ ucfirst(str_replace('_', ' ', $dateFilter)) }}
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="font-medium">Revenue:</span>
                    <span class="text-success-600 font-semibold text-lg">
                        Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="font-medium">Expenses:</span>
                    <span class="text-danger-600 font-semibold text-lg">
                        - Rp {{ number_format($summary['total_expenses'], 0, ',', '.') }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600 mt-2">
                    <span class="font-bold text-lg">Net Profit:</span>
                    <span class="font-bold text-2xl {{ $summary['net_profit'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center py-2 bg-gray-100 dark:bg-gray-800 rounded-lg px-4">
                    <span class="font-medium">Profit Margin:</span>
                    <span class="font-bold text-xl {{ $summary['profit_margin'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ number_format($summary['profit_margin'], 1) }}%
                    </span>
                </div>
            </div>
        </x-filament::section>

        {{-- Expense Breakdown --}}
        <x-filament::section>
            <x-slot name="heading">
                Expense Breakdown by Category
            </x-slot>
            
            @php
                $expenseBreakdown = $this->getExpenseBreakdown();
            @endphp
            
            <div class="space-y-3">
                @forelse($expenseBreakdown as $category => $amount)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium">{{ $category }}</span>
                            <span class="text-sm font-semibold">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div 
                                class="bg-danger-600 h-2 rounded-full" 
                                style="width: {{ $summary['total_expenses'] > 0 ? ($amount / $summary['total_expenses']) * 100 : 0 }}%"
                            ></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $summary['total_expenses'] > 0 ? number_format(($amount / $summary['total_expenses']) * 100, 1) : 0 }}% of total expenses
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-4">
                        No expense data available for this period
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>