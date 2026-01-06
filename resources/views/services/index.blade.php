@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 py-20 overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full mb-6">
                <i class="fas fa-sparkles text-yellow-300"></i>
                <span class="text-white font-medium">{{ $services->count() }} Layanan Tersedia</span>
            </div>

            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
                Layanan Laundry <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-pink-300">Premium</span>
            </h1>
            <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                Solusi lengkap untuk semua kebutuhan laundry Anda. Dari cucian harian hingga perawatan khusus, kami siap membantu!
            </p>

            <!-- Stats -->
            <div class="flex flex-wrap justify-center gap-8 mt-12">
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">24/7</div>
                    <div class="text-blue-200">Layanan</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">10K+</div>
                    <div class="text-blue-200">Pelanggan</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">4.9★</div>
                    <div class="text-blue-200">Rating</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wave Divider -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" class="w-full h-16 md:h-24 fill-gray-50">
            <path d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,58.7C960,64,1056,64,1152,58.7C1248,53,1344,43,1392,37.3L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
        </svg>
    </div>
</section>

<!-- Filter & Search Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Search -->
                <div class="relative flex-1 w-full md:w-auto">
                    <input type="text" 
                           id="searchService"
                           placeholder="Cari layanan..."
                           class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors">
                    <i class="fas fa-search absolute left-4 top-4 text-gray-400"></i>
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-2 flex-wrap">
                    <button class="filter-btn active px-4 py-2 rounded-lg font-medium transition-all" data-filter="all">
                        Semua
                    </button>
                    <button class="filter-btn px-4 py-2 rounded-lg font-medium transition-all" data-filter="kg">
                        Per Kg
                    </button>
                    <button class="filter-btn px-4 py-2 rounded-lg font-medium transition-all" data-filter="unit">
                        Per Unit
                    </button>
                </div>

                <!-- Sort -->
                <select class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none cursor-pointer" id="sortService">
                    <option value="default">Urutkan</option>
                    <option value="price-low">Harga: Rendah ke Tinggi</option>
                    <option value="price-high">Harga: Tinggi ke Rendah</option>
                    <option value="name">Nama A-Z</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div id="servicesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @foreach($services as $service)
                @php
                    $colorSchemes = [
                        'blue' => ['gradient' => 'from-blue-500 to-blue-600', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'text-blue-500', 'shadow' => 'shadow-blue-200'],
                        'purple' => ['gradient' => 'from-purple-500 to-purple-600', 'bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'icon' => 'text-purple-500', 'shadow' => 'shadow-purple-200'],
                        'orange' => ['gradient' => 'from-orange-500 to-orange-600', 'bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'icon' => 'text-orange-500', 'shadow' => 'shadow-orange-200'],
                        'indigo' => ['gradient' => 'from-indigo-500 to-indigo-600', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'icon' => 'text-indigo-500', 'shadow' => 'shadow-indigo-200'],
                        'pink' => ['gradient' => 'from-pink-500 to-pink-600', 'bg' => 'bg-pink-50', 'text' => 'text-pink-600', 'icon' => 'text-pink-500', 'shadow' => 'shadow-pink-200'],
                        'teal' => ['gradient' => 'from-teal-500 to-teal-600', 'bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'icon' => 'text-teal-500', 'shadow' => 'shadow-teal-200'],
                        'emerald' => ['gradient' => 'from-emerald-500 to-emerald-600', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'text-emerald-500', 'shadow' => 'shadow-emerald-200'],
                        'rose' => ['gradient' => 'from-rose-500 to-rose-600', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'icon' => 'text-rose-500', 'shadow' => 'shadow-rose-200'],
                    ];
                    
                    $colorKeys = array_keys($colorSchemes);
                    $scheme = $colorSchemes[$colorKeys[$loop->index % count($colorKeys)]];
                    
                    $icons = [
                        'kg' => 'fa-weight-hanging',
                        'unit' => 'fa-tshirt',
                        'default' => 'fa-soap'
                    ];
                    $icon = $icons[$service->pricing_type] ?? $icons['default'];
                @endphp
                
                <div class="service-card group relative bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2"
                     data-pricing="{{ $service->pricing_type }}"
                     data-price="{{ $service->price_per_kg ?? $service->price_per_unit }}"
                     data-name="{{ $service->name }}">
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br {{ $scheme['gradient'] }} opacity-0 group-hover:opacity-5 transition-opacity duration-500"></div>
                    
                    <!-- Content -->
                    <div class="relative p-6">
                        <!-- Icon with Animated Background -->
                        <div class="relative mb-6">
                            <div class="absolute inset-0 {{ $scheme['bg'] }} rounded-2xl blur-xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative w-16 h-16 {{ $scheme['bg'] }} rounded-2xl flex items-center justify-center transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                                <i class="fas {{ $icon }} text-3xl {{ $scheme['icon'] }}"></i>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <div class="mb-6">
                            <h3 class="text-xl font-bold mb-2 text-gray-800 group-hover:{{ $scheme['text'] }} transition-colors">
                                {{ $service->name }}
                            </h3>
                            <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">
                                {{ $service->description }}
                            </p>
                        </div>

                        <!-- Features -->
                        @if($service->features ?? false)
                        <div class="mb-6 space-y-2">
                            @foreach(explode(',', $service->features) as $feature)
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-check-circle {{ $scheme['icon'] }} mr-2"></i>
                                <span>{{ trim($feature) }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Price Card -->
                        <div class="bg-gradient-to-br {{ $scheme['gradient'] }} rounded-2xl p-4 mb-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-10 translate-x-10"></div>
                            <div class="relative">
                                <div class="text-xs text-white/80 uppercase tracking-wider mb-1 font-medium">Harga Mulai</div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-white">
                                        {{ number_format($service->price_per_kg ?? $service->price_per_unit, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm text-white/90 font-medium">
                                        /{{ $service->pricing_type }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <a href="{{ route('booking', ['service_id' => $service->id]) }}" 
                           class="block w-full text-center bg-gradient-to-r {{ $scheme['gradient'] }} hover:shadow-xl {{ $scheme['shadow'] }} text-white py-3.5 rounded-xl font-semibold transform group-hover:scale-105 transition-all duration-300">
                            <span class="flex items-center justify-center gap-2">
                                <i class="fas fa-calendar-check"></i>
                                Pesan Sekarang
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </a>
                    </div>

                    <!-- Popular Badge -->
                    @if($loop->index < 3)
                    <div class="absolute top-4 right-4">
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg flex items-center gap-1">
                            <i class="fas fa-star"></i>
                            Populer
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-16">
            <div class="w-32 h-32 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-search text-5xl text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Tidak ada layanan ditemukan</h3>
            <p class="text-gray-600 mb-6">Coba ubah kata kunci pencarian atau filter Anda</p>
            <button onclick="resetFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-all">
                Reset Filter
            </button>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 relative overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-white rounded-full filter blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-pink-300 rounded-full filter blur-3xl"></div>
    </div>
</section>

<style>
@keyframes blob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.filter-btn {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.filter-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.filter-btn.active {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.service-card {
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Stagger animation */
.service-card:nth-child(1) { animation-delay: 0.1s; }
.service-card:nth-child(2) { animation-delay: 0.2s; }
.service-card:nth-child(3) { animation-delay: 0.3s; }
.service-card:nth-child(4) { animation-delay: 0.4s; }
.service-card:nth-child(5) { animation-delay: 0.5s; }
.service-card:nth-child(6) { animation-delay: 0.6s; }
.service-card:nth-child(7) { animation-delay: 0.7s; }
.service-card:nth-child(8) { animation-delay: 0.8s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchService');
    const sortSelect = document.getElementById('sortService');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const servicesGrid = document.getElementById('servicesGrid');
    const emptyState = document.getElementById('emptyState');
    
    let currentFilter = 'all';
    let currentSort = 'default';
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        filterAndSort();
    });
    
    // Filter functionality
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterAndSort();
        });
    });
    
    // Sort functionality
    sortSelect.addEventListener('change', function() {
        currentSort = this.value;
        filterAndSort();
    });
    
    function filterAndSort() {
        const searchTerm = searchInput.value.toLowerCase();
        const cards = Array.from(document.querySelectorAll('.service-card'));
        
        // Filter
        let visibleCards = cards.filter(card => {
            const name = card.dataset.name.toLowerCase();
            const pricing = card.dataset.pricing;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesFilter = currentFilter === 'all' || pricing === currentFilter;
            
            return matchesSearch && matchesFilter;
        });
        
        // Sort
        if (currentSort === 'price-low') {
            visibleCards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (currentSort === 'price-high') {
            visibleCards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        } else if (currentSort === 'name') {
            visibleCards.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
        }
        
        // Hide all cards
        cards.forEach(card => card.style.display = 'none');
        
        // Show filtered and sorted cards
        if (visibleCards.length > 0) {
            emptyState.classList.add('hidden');
            visibleCards.forEach(card => {
                servicesGrid.appendChild(card);
                card.style.display = 'block';
            });
        } else {
            emptyState.classList.remove('hidden');
        }
    }
    
    window.resetFilters = function() {
        searchInput.value = '';
        sortSelect.value = 'default';
        filterButtons.forEach(btn => btn.classList.remove('active'));
        filterButtons[0].classList.add('active');
        currentFilter = 'all';
        currentSort = 'default';
        filterAndSort();
    };
});

// Add scroll reveal animation
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, {
    threshold: 0.1
});

document.querySelectorAll('.service-card').forEach(card => {
    observer.observe(card);
});
</script>
@endsection