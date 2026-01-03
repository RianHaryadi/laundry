<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Date Filter --}}
        <div class="flex justify-end">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="dateFilter">
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="this_year">This Year</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{-- Stats Cards --}}
        @php
            $stats = $this->getStats();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Revenue --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Revenue
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="mt-2 text-sm {{ $stats['growth'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $stats['growth'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($stats['growth']), 1) }}%
                    </div>
                </div>
            </x-filament::section>

            {{-- Total Transactions --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Transactions
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['total_transactions']) }}
                    </div>
                </div>
            </x-filament::section>

            {{-- Average Transaction --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Average Transaction
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($stats['avg_transaction'], 0, ',', '.') }}
                    </div>
                </div>
            </x-filament::section>

            {{-- Growth Rate --}}
            <x-filament::section>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Growth Rate
                    </div>
                    <div class="mt-2 text-3xl font-bold {{ $stats['growth'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ number_format($stats['growth'], 1) }}%
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Revenue by Service --}}
        @php
            $revenueByService = $this->getRevenueByService();
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                Revenue by Service
            </x-slot>

            <div class="space-y-4">
                @forelse($revenueByService as $service => $amount)
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $service }}
                            </div>
                            <div class="mt-1 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                @php
                                    $maxAmount = max($revenueByService);
                                    $percentage = $maxAmount > 0 ? ($amount / $maxAmount) * 100 : 0;
                                @endphp
                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        <div class="ml-4 text-sm font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        No revenue data available
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Revenue by Payment Method --}}
        @php
            $revenueByPaymentMethod = $this->getRevenueByPaymentMethod();
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                Revenue by Payment Method
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse($revenueByPaymentMethod as $method => $amount)
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">
                            {{ str_replace('_', ' ', $method) }}
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </div>
                        @php
                            $total = array_sum($revenueByPaymentMethod);
                            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
                        @endphp
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ number_format($percentage, 1) }}% of total
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center text-gray-500 dark:text-gray-400 py-8">
                        No payment data available
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>