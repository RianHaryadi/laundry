<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Date Filter --}}
        <div class="flex justify-end">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="dateFilter">
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="this_year">Tahun Ini</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{-- Summary Stats --}}
        @php
            $summary = $this->getSummary();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Revenue --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Pendapatan
                    </div>
                    <div class="mt-2 text-3xl font-bold text-success-600 dark:text-success-400">
                        Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                    </div>
                </div>
            </x-filament::section>

            {{-- Total Expenses --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Pengeluaran
                    </div>
                    <div class="mt-2 text-3xl font-bold text-danger-600 dark:text-danger-400">
                        Rp {{ number_format($summary['total_expenses'], 0, ',', '.') }}
                    </div>
                </div>
            </x-filament::section>

            {{-- Net Profit --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Profit Bersih
                    </div>
                    <div class="mt-2 text-3xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
                    </div>
                    @if($summary['net_profit'] < 0)
                        <div class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                            (Rugi)
                        </div>
                    @endif
                </div>
            </x-filament::section>

            {{-- Profit Margin --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Margin Profit
                    </div>
                    <div class="mt-2 text-3xl font-bold {{ $summary['profit_margin'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ number_format($summary['profit_margin'], 1) }}%
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Profit Visualization --}}
        <x-filament::section>
            <x-slot name="heading">
                Visualisasi Profit
            </x-slot>

            <div class="space-y-6">
                {{-- Revenue vs Expenses Comparison --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-6 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800">
                        <div class="text-sm font-medium text-success-700 dark:text-success-300 mb-2">
                            Pendapatan
                        </div>
                        <div class="text-2xl font-bold text-success-900 dark:text-success-100">
                            Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-success-200 dark:bg-success-800 rounded-full h-3">
                                <div class="bg-success-600 dark:bg-success-500 h-3 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
                        <div class="text-sm font-medium text-danger-700 dark:text-danger-300 mb-2">
                            Pengeluaran
                        </div>
                        <div class="text-2xl font-bold text-danger-900 dark:text-danger-100">
                            Rp {{ number_format($summary['total_expenses'], 0, ',', '.') }}
                        </div>
                        <div class="mt-3">
                            @php
                                $expensePercentage = $summary['total_revenue'] > 0 
                                    ? ($summary['total_expenses'] / $summary['total_revenue']) * 100 
                                    : 0;
                            @endphp
                            <div class="w-full bg-danger-200 dark:bg-danger-800 rounded-full h-3">
                                <div class="bg-danger-600 dark:bg-danger-500 h-3 rounded-full" style="width: {{ min($expensePercentage, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Net Profit Summary --}}
                <div class="p-6 {{ $summary['net_profit'] >= 0 ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800' : 'bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-800' }} rounded-lg border">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium {{ $summary['net_profit'] >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-warning-700 dark:text-warning-300' }}">
                                Hasil Akhir
                            </div>
                            <div class="text-3xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-primary-900 dark:text-primary-100' : 'text-warning-900 dark:text-warning-100' }} mt-1">
                                Rp {{ number_format(abs($summary['net_profit']), 0, ',', '.') }}
                                @if($summary['net_profit'] < 0)
                                    <span class="text-lg">(Rugi)</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium {{ $summary['net_profit'] >= 0 ? 'text-primary-700 dark:text-primary-300' : 'text-warning-700 dark:text-warning-300' }}">
                                Margin
                            </div>
                            <div class="text-3xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-primary-900 dark:text-primary-100' : 'text-warning-900 dark:text-warning-100' }} mt-1">
                                {{ number_format($summary['profit_margin'], 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Expense Breakdown --}}
        @php
            $expenseBreakdown = $this->getExpenseBreakdown();
        @endphp

        @if(!empty($expenseBreakdown))
            <x-filament::section>
                <x-slot name="heading">
                    Rincian Pengeluaran per Kategori
                </x-slot>

                <div class="space-y-4">
                    @foreach($expenseBreakdown as $category => $amount)
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $category }}
                                </div>
                                <div class="mt-1 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    @php
                                        $maxAmount = max($expenseBreakdown);
                                        $percentage = $maxAmount > 0 ? ($amount / $maxAmount) * 100 : 0;
                                    @endphp
                                    <div class="bg-danger-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            <div class="ml-4 text-right">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </div>
                                @php
                                    $categoryPercentage = $summary['total_expenses'] > 0 
                                        ? ($amount / $summary['total_expenses']) * 100 
                                        : 0;
                                @endphp
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($categoryPercentage, 1) }}% dari total
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">
                    Rincian Pengeluaran per Kategori
                </x-slot>

                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    Tidak ada data pengeluaran untuk periode ini
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>