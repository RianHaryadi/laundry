@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 py-16 md:py-24 overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-72 h-72 bg-blue-400 rounded-full opacity-10 blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-indigo-400 rounded-full opacity-5 blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto">
            <!-- Service Count -->
            <div class="inline-flex items-center bg-white/10 backdrop-blur-md px-4 py-2 rounded-full mb-6">
                <span class="text-sm font-medium text-white">
                    {{ $services->count() }} Layanan Tersedia
                </span>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                Layanan Laundry <span class="text-blue-200">Profesional</span>
            </h1>
            
            <!-- Description -->
            <p class="text-lg text-blue-100 mb-8 leading-relaxed">
                Temukan solusi laundry terbaik untuk semua kebutuhan Anda. 
                Cepat, bersih, dan terpercaya.
            </p>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="py-6 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Search -->
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="searchService"
                    placeholder="Cari layanan..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                >
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2">
                <button class="filter-btn px-4 py-2.5 text-sm font-medium rounded-lg border hover:border-blue-500 hover:text-blue-600 active" data-filter="all">
                    Semua
                </button>
                <button class="filter-btn px-4 py-2.5 text-sm font-medium rounded-lg border hover:border-blue-500 hover:text-blue-600" data-filter="kg">
                    Per Kg
                </button>
                <button class="filter-btn px-4 py-2.5 text-sm font-medium rounded-lg border hover:border-blue-500 hover:text-blue-600" data-filter="unit">
                    Per Unit
                </button>
            </div>

            <!-- Sort -->
            <div class="relative">
                <select class="appearance-none bg-gray-50 border border-gray-200 rounded-lg pl-3 pr-8 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-48" id="sortService">
                    <option value="default">Urutkan</option>
                    <option value="price-low">Harga: Rendah ke Tinggi</option>
                    <option value="price-high">Harga: Tinggi ke Rendah</option>
                    <option value="name">Nama: A-Z</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Semua Layanan</h2>
            <div class="flex items-center gap-2 mt-2">
                <span id="resultsCount" class="text-blue-600 font-medium">{{ $services->count() }}</span>
                <span class="text-gray-600 text-sm">layanan tersedia</span>
            </div>
        </div>

        <!-- Services Grid -->
        <div id="servicesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($services as $service)
                @php
                    // Color schemes
                    $colors = [
                        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'button' => 'bg-blue-600 hover:bg-blue-700'],
                        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100', 'button' => 'bg-indigo-600 hover:bg-indigo-700'],
                        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'button' => 'bg-emerald-600 hover:bg-emerald-700'],
                        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-100', 'button' => 'bg-purple-600 hover:bg-purple-700'],
                    ];
                    
                    $color = array_keys($colors)[$loop->index % count($colors)];
                    $scheme = $colors[$color];
                    
                    // Price info
                    $price = $service->price_per_kg ?? $service->price_per_unit;
                    $unit = $service->pricing_type === 'kg' ? 'kg' : 'unit';
                @endphp
                
                <div class="service-card group bg-white rounded-xl border border-gray-200 hover:border-{{ $color }}-200 shadow-sm hover:shadow-md transition-all duration-200"
                     data-pricing="{{ $service->pricing_type }}"
                     data-price="{{ $price }}"
                     data-name="{{ strtolower($service->name) }}">

                    <!-- Card Content -->
                    <div class="p-5">
                        <!-- Icon & Title -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="{{ $scheme['bg'] }} p-3 rounded-lg">
                                @if($service->pricing_type === 'kg')
                                    <svg class="w-6 h-6 {{ $scheme['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 {{ $scheme['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @endif
                            </div>
                            
                            @if($loop->index < 2)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    Populer
                                </span>
                            @endif
                        </div>

                        <!-- Service Name -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:{{ $scheme['text'] }}">
                            {{ $service->name }}
                        </h3>

                        <!-- Description -->
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                            {{ $service->description }}
                        </p>

                        <!-- Features -->
                        @if($service->features ?? false)
                            <div class="mb-5 space-y-2">
                                @foreach(array_slice(explode(',', $service->features), 0, 2) as $feature)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>{{ trim($feature) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Price & Button -->
                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">Mulai dari</div>
                                    <div class="flex items-baseline">
                                        <span class="text-xl font-bold text-gray-900">Rp{{ number_format($price, 0, ',', '.') }}</span>
                                        <span class="text-sm text-gray-500 ml-1">/{{ $unit }}</span>
                                    </div>
                                </div>
                                
                                <a href="{{ route('booking', ['service_id' => $service->id]) }}" 
                                   class="{{ $scheme['button'] }} text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada layanan ditemukan</h3>
            <p class="text-gray-600 text-sm mb-4">Coba gunakan kata kunci lain atau filter berbeda</p>
            <button onclick="resetFilters()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Reset pencarian
            </button>
        </div>
    </div>
</section>
@endsection

@section('styles')
<style>
.filter-btn {
    border: 1px solid #e5e7eb;
    color: #6b7280;
    background: white;
    transition: all 0.2s;
}

.filter-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.filter-btn:hover:not(.active) {
    border-color: #3b82f6;
    color: #3b82f6;
}

.service-card {
    opacity: 0;
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

/* Stagger animations */
.service-card:nth-child(1) { animation-delay: 0.05s; }
.service-card:nth-child(2) { animation-delay: 0.1s; }
.service-card:nth-child(3) { animation-delay: 0.15s; }
.service-card:nth-child(4) { animation-delay: 0.2s; }
.service-card:nth-child(5) { animation-delay: 0.25s; }
.service-card:nth-child(6) { animation-delay: 0.3s; }
.service-card:nth-child(7) { animation-delay: 0.35s; }
.service-card:nth-child(8) { animation-delay: 0.4s; }
</style>
@endsection

@section('scripts')
<script>
class ServiceFilter {
    constructor() {
        this.searchInput = document.getElementById('searchService');
        this.sortSelect = document.getElementById('sortService');
        this.filterButtons = document.querySelectorAll('.filter-btn');
        this.servicesGrid = document.getElementById('servicesGrid');
        this.emptyState = document.getElementById('emptyState');
        this.resultsCount = document.getElementById('resultsCount');
        
        this.currentFilter = 'all';
        this.currentSort = 'default';
        this.allCards = Array.from(document.querySelectorAll('.service-card'));
        
        this.init();
    }
    
    init() {
        // Search input
        this.searchInput.addEventListener('input', () => this.filterAndSort());
        
        // Filter buttons
        this.filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentFilter = btn.dataset.filter;
                this.filterAndSort();
            });
        });
        
        // Sort select
        this.sortSelect.addEventListener('change', () => {
            this.currentSort = this.sortSelect.value;
            this.filterAndSort();
        });
    }
    
    filterAndSort() {
        const searchTerm = this.searchInput.value.toLowerCase().trim();
        
        // Filter cards
        let visibleCards = this.allCards.filter(card => {
            const name = card.dataset.name;
            const pricing = card.dataset.pricing;
            
            const matchesSearch = searchTerm === '' || name.includes(searchTerm);
            const matchesFilter = this.currentFilter === 'all' || pricing === this.currentFilter;
            
            return matchesSearch && matchesFilter;
        });
        
        // Sort cards
        if (this.currentSort === 'price-low') {
            visibleCards.sort((a, b) => a.dataset.price - b.dataset.price);
        } else if (this.currentSort === 'price-high') {
            visibleCards.sort((a, b) => b.dataset.price - a.dataset.price);
        } else if (this.currentSort === 'name') {
            visibleCards.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
        }
        
        // Update UI
        this.updateDisplay(visibleCards);
    }
    
    updateDisplay(cards) {
        // Update count
        this.resultsCount.textContent = cards.length;
        
        // Clear grid
        this.servicesGrid.innerHTML = '';
        
        if (cards.length === 0) {
            this.emptyState.classList.remove('hidden');
            return;
        }
        
        this.emptyState.classList.add('hidden');
        
        // Add cards with animation
        cards.forEach((card, index) => {
            card.style.animationDelay = `${(index * 0.05)}s`;
            this.servicesGrid.appendChild(card);
        });
    }
    
    reset() {
        this.searchInput.value = '';
        this.sortSelect.value = 'default';
        
        this.filterButtons.forEach((btn, index) => {
            btn.classList.remove('active');
            if (index === 0) btn.classList.add('active');
        });
        
        this.currentFilter = 'all';
        this.currentSort = 'default';
        
        this.filterAndSort();
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.serviceFilter = new ServiceFilter();
});

// Global reset function
window.resetFilters = () => window.serviceFilter?.reset();
</script>
@endsection