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

        {{-- Stats Overview --}}
        @php
            $stats = $this->getStats();
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">
                        Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Total Revenue
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">
                        {{ number_format($stats['total_transactions']) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Transactions
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">
                        Rp {{ number_format($stats['avg_transaction'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Avg Transaction
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold {{ $stats['growth'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $stats['growth'] >= 0 ? '+' : '' }}{{ number_format($stats['growth'], 1) }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Growth
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Revenue Breakdown --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $revenueByService = $this->getRevenueByService();
                $revenueByPayment = $this->getRevenueByPaymentMethod();
            @endphp
            
            <x-filament::section>
                <x-slot name="heading">
                    Revenue by Service
                </x-slot>
                
                <div class="space-y-3">
                    @forelse($revenueByService as $service => $amount)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium">{{ $service }}</span>
                                <span class="text-sm font-semibold">
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div 
                                    class="bg-primary-600 h-2 rounded-full" 
                                    style="width: {{ ($amount / $stats['total_revenue']) * 100 }}%"
                                ></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ number_format(($amount / $stats['total_revenue']) * 100, 1) }}%
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No revenue data available
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">
                    Revenue by Payment Method
                </x-slot>
                
                <div class="space-y-3">
                    @forelse($revenueByPayment as $method => $amount)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium">{{ $method }}</span>
                                <span class="text-sm font-semibold">
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div 
                                    class="bg-success-600 h-2 rounded-full" 
                                    style="width: {{ ($amount / $stats['total_revenue']) * 100 }}%"
                                ></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ number_format(($amount / $stats['total_revenue']) * 100, 1) }}%
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No payment data available
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>