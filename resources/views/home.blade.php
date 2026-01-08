@extends('layouts.app')
@section('title', 'Home - Rizki Laundry')
@section('content')

<!-- Booking Success Modal -->
    @if(session('booking_success') && session('booking_details'))
    <div id="bookingSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: flex;">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeBookingModal()"></div>

        <!-- Modal panel -->
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-white">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Booking Berhasil!</h3>
                    </div>
                    <button onclick="closeBookingModal()" class="text-white hover:text-gray-200 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-4">
                @php $details = session('booking_details'); @endphp
                
                <!-- Order ID - Highlight -->
                <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 text-center">
                    <p class="text-xs text-gray-600 mb-1">Order ID</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $details['order_id'] }}</p>
                </div>

                <!-- Info Grid -->
                <div class="space-y-3">
                    <!-- Customer -->
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Customer</p>
                            <p class="font-semibold text-gray-800">{{ $details['customer_name'] }}</p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full font-medium {{ $details['customer_type'] === 'Member' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $details['customer_type'] }}
                        </span>
                    </div>

                    <!-- Service -->
                    <div class="pb-3 border-b border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Layanan</p>
                        <p class="font-semibold text-gray-800">{{ $details['service_name'] }}</p>
                    </div>

                    <!-- Outlet -->
                    <div class="pb-3 border-b border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Outlet</p>
                        <p class="font-semibold text-gray-800">{{ $details['outlet_name'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $details['outlet_address'] }}</p>
                    </div>

                    <!-- Delivery & Speed in 2 columns -->
                    <div class="grid grid-cols-2 gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Pengiriman</p>
                            <p class="font-semibold text-sm text-gray-800">{{ $details['delivery_method'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Kecepatan</p>
                            <p class="font-semibold text-sm text-gray-800">{{ $details['service_speed'] }}</p>
                        </div>
                    </div>

                    <!-- Pickup Time -->
                    <div class="pb-3 border-b border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Waktu Penjemputan</p>
                        <p class="font-semibold text-gray-800">{{ $details['pickup_datetime'] }}</p>
                    </div>

                    <!-- Estimated Completion - Highlight -->
                    <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-xs text-gray-600 mb-1">⏰ Estimasi Selesai</p>
                        <p class="font-bold text-green-700">{{ $details['estimated_completion'] }}</p>
                    </div>

                    @if($details['is_free_service'])
                    <div class="p-3 bg-yellow-50 border border-yellow-300 rounded-lg">
                        <p class="text-sm font-semibold text-yellow-800 flex items-center">
                            <span class="mr-2">🎁</span> Layanan Gratis - Reward Terpakai
                        </p>
                    </div>
                    @endif

                    <!-- Info Message -->
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs text-blue-800">
                            <span class="font-semibold">📱 Informasi:</span> Tim kami akan segera menghubungi Anda untuk konfirmasi.
                            @if($details['has_courier'])
                                Kurir sudah ditugaskan.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex gap-3">
                <button onclick="closeBookingModal()" type="button" class="flex-1 px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    Tutup
                </button>
                <a href="{{ route('orders') }}" class="flex-1 px-4 py-2.5 bg-blue-600 rounded-lg text-sm font-medium text-white hover:bg-blue-700 transition text-center">
                    Lihat Order
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Hero Section -->
    <section id="home" class="gradient-bg text-white overflow-hidden -mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 pt-32">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-12 lg:mb-0">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">Layanan <span class="text-yellow-300">Laundry</span> Profesional di Depan Pintu Anda</h1>
                    <p class="text-xl mb-8 opacity-90 max-w-2xl">Kami menjemput, mencuci, dan mengantar pakaian Anda dengan penuh perhatian. Hemat waktu dan nikmati pakaian bersih serta wangi tanpa repot. Kepuasan 100% terjamin.</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('booking') }}" class="inline-block">
                            <button class="btn-primary px-8 py-4 rounded-xl font-semibold text-lg flex items-center justify-center text-white">
                                <i class="fas fa-calendar-alt mr-3"></i> Jadwalkan Penjemputan Sekarang
                            </button>
                        </a>
                    </div>
                    <div class="flex flex-wrap gap-6 mt-12">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-300 text-xl mr-3"></i>
                            <span>Deterjen Ramah Lingkungan</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-300 text-xl mr-3"></i>
                            <span>Penjemputan & Pengantaran Gratis</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-300 text-xl mr-3"></i>
                            <span>Layanan Satu Hari Selesai</span>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="relative">
                    <img src="https://cdn-icons-png.flaticon.com/512/2553/2553642.png" alt="Laundry Service" class="hero-image w-full max-w-lg">
                        <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl p-6 shadow-2xl w-64">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">10.000+</p>
                                    <p class="text-sm text-gray-600">Pelanggan Puas</p>
                                </div>
                            </div>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="text-gray-700 font-medium ml-2">4,8/5</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Section -->
        <div class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="stats-counter mb-2">25.000+</div>
                        <p class="text-gray-600 text-base">Pakaian Dicuci</p>
                    </div>
                    <div class="text-center">
                        <div class="stats-counter mb-2">98%</div>
                        <p class="text-gray-600 text-base">Tingkat Kepuasan</p>
                    </div>
                    <div class="text-center">
                        <div class="stats-counter mb-2">45 menit</div>
                        <p class="text-gray-600 text-base">Waktu Proses Rata-rata</p>
                    </div>
                    <div class="text-center">
                        <div class="stats-counter mb-2">24/7</div>
                        <p class="text-gray-600 text-base">Layanan Tersedia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 text-gray-800">Cara Kerja <span class="gradient-text">Rizki Laundry</span></h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">Pilih layanan yang paling sesuai dengan kebutuhan Anda</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex justify-center mb-12">
                <div class="inline-flex rounded-full bg-white p-2 shadow-lg">
                    <button onclick="switchTab('pickup')" id="pickupTab" class="tab-button active px-8 py-3 rounded-full font-semibold text-lg transition-all">
                        <i class="fas fa-truck mr-2"></i>Antar-Jemput
                    </button>
                    <button onclick="switchTab('walkin')" id="walkinTab" class="tab-button px-8 py-3 rounded-full font-semibold text-lg text-gray-600">
                        <i class="fas fa-store mr-2"></i>Datang Langsung
                    </button>
                </div>
            </div>

            <!-- Pickup Service Content -->
            <div id="pickupContent" class="service-content active grid-cols-1 md:grid-cols-3 gap-10">
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">1</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Jadwalkan Penjemputan</h3>
                    <p class="text-gray-600">Booking waktu penjemputan via WhatsApp, website, atau telepon. Pilih layanan yang Anda butuhkan.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-hands-wash text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">2</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Kami Jemput & Cuci</h3>
                    <p class="text-gray-600">Tim profesional kami menjemput cucian Anda dan mencucinya dengan peralatan modern serta produk ramah lingkungan.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-tshirt text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">3</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Antar ke Rumah Anda</h3>
                    <p class="text-gray-600">Pakaian bersih dan wangi diantar ke rumah sesuai jadwal, sudah dilipat rapi dan siap pakai.</p>
                </div>
            </div>

            <!-- Walk-in Service Content -->
            <div id="walkinContent" class="service-content grid-cols-1 md:grid-cols-3 gap-10">
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">1</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Kunjungi Toko Kami</h3>
                    <p class="text-gray-600">Datang langsung ke outlet Rizki Laundry terdekat. Buka setiap hari pukul 08.00 - 20.00 WIB.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">2</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Serahkan Cucian</h3>
                    <p class="text-gray-600">Serahkan cucian Anda, pilih layanan yang diinginkan, dan dapatkan struk sebagai bukti transaksi.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-clock text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">3</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Ambil Saat Siap</h3>
                    <p class="text-gray-600">Cucian siap dalam 1-3 hari. Kami akan kabari via WhatsApp, lalu Anda bisa mengambilnya kapan saja.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
<section id="services" class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4 text-gray-800">
                Layanan <span class="gradient-text">Premium</span> Kami
            </h2>
            <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                Kami menyediakan berbagai layanan laundry yang disesuaikan dengan kebutuhan spesifik Anda
            </p>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
           @foreach($pricingServices as $index => $service) 
                @php
                    // Skema warna untuk setiap layanan
                    $colors = [
                        [
                            'bg' => 'blue', 
                            'gradient' => 'from-blue-100 to-blue-50', 
                            'icon' => 'fa-water',
                            'iconColor' => 'text-blue-500',
                            'badge' => 'Paling Populer', 
                            'badgeBg' => 'bg-blue-600',
                            'badgeIcon' => 'fa-star',
                            'checkColor' => 'text-blue-600',
                            'priceColor' => 'text-blue-600',
                            'priceBg' => 'bg-blue-50',
                            'buttonBg' => 'bg-blue-600 hover:bg-blue-700'
                        ],
                        [
                            'bg' => 'green', 
                            'gradient' => 'from-green-100 to-green-50', 
                            'icon' => 'fa-tshirt',
                            'iconColor' => 'text-green-500',
                            'badge' => 'Premium', 
                            'badgeBg' => 'bg-green-600',
                            'badgeIcon' => 'fa-leaf',
                            'checkColor' => 'text-green-600',
                            'priceColor' => 'text-green-600',
                            'priceBg' => 'bg-green-50',
                            'buttonBg' => 'bg-green-600 hover:bg-green-700'
                        ],
                        [
                            'bg' => 'purple', 
                            'gradient' => 'from-purple-100 to-purple-50', 
                            'icon' => 'fa-bed',
                            'iconColor' => 'text-purple-500',
                            'badge' => 'Perawatan Rumah', 
                            'badgeBg' => 'bg-purple-600',
                            'badgeIcon' => 'fa-home',
                            'checkColor' => 'text-purple-600',
                            'priceColor' => 'text-purple-600',
                            'priceBg' => 'bg-purple-50',
                            'buttonBg' => 'bg-purple-600 hover:bg-purple-700'
                        ],
                    ];
                    $color = $colors[$index % count($colors)];
                @endphp
                
                <!-- Service Card -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                    <!-- Card Header with Icon -->
                    <div class="h-52 bg-gradient-to-br {{ $color['gradient'] }} flex items-center justify-center relative">
                        <!-- Badge -->
                        <div class="absolute top-4 right-4 {{ $color['badgeBg'] }} text-white px-4 py-2 rounded-full text-sm font-semibold shadow-lg flex items-center gap-2">
                            <i class="fas {{ $color['badgeIcon'] }}"></i>
                            {{ $color['badge'] }}
                        </div>
                        
                        <!-- Icon -->
                        <i class="fas {{ $color['icon'] }} {{ $color['iconColor'] }} text-8xl"></i>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <!-- Title & Description -->
                        <h3 class="text-2xl font-bold mb-2 text-gray-800">
                            {{ $service->name }}
                        </h3>
                        <p class="text-gray-500 mb-5 text-sm">
                            {{ $service->getShortDescription(100) }}
                        </p>
                        
                        <!-- Features List -->
                        <ul class="mb-6 space-y-2.5">
                            <li class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-check-circle {{ $color['checkColor'] }} mr-2.5 text-base"></i>
                                <span>
                                    {{ $service->isExpress() ? 'Layanan selesai hari ini tersedia' : 'Layanan profesional' }}
                                </span>
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-check-circle {{ $color['checkColor'] }} mr-2.5 text-base"></i>
                                <span>Produk ramah lingkungan</span>
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-check-circle {{ $color['checkColor'] }} mr-2.5 text-base"></i>
                                <span>Kualitas terjamin</span>
                            </li>
                        </ul>
                        
                        <!-- Pricing Box -->
                        <div class="flex justify-between items-center mb-6 p-4 {{ $color['priceBg'] }} rounded-2xl">
                            <!-- Price -->
                            <div>
                                @if($service->supportsKgPricing())
                                    <span class="font-bold {{ $color['priceColor'] }} text-3xl">
                                        Rp {{ number_format($service->price_per_kg, 0, ',', '.') }}
                                    </span>
                                    <span class="text-gray-500 text-base">/kg</span>
                                @elseif($service->supportsUnitPricing())
                                    <span class="font-bold {{ $color['priceColor'] }} text-3xl">
                                        Rp {{ number_format($service->price_per_unit, 0, ',', '.') }}
                                    </span>
                                    <span class="text-gray-500 text-base">/item</span>
                                @else
                                    <span class="font-bold {{ $color['priceColor'] }} text-3xl">
                                        Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        <a href="{{ route('booking', ['service_id' => $service->id]) }}" 
                            class="w-full {{ $color['buttonBg'] }} py-3.5 rounded-2xl font-semibold text-white shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                                <i class="fas fa-calendar-check"></i>
                                <span>Pesan Sekarang</span>
                            </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

    <!-- Tracking Section -->
<section id="tracking" class="py-12 md:py-20 bg-gradient-to-br from-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-center">
            <!-- Left Content -->
            <div class="w-full lg:w-1/2">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 md:mb-6 text-gray-800">
                    Pelacakan Pesanan <span class="gradient-text">Real-Time</span>
                </h2>
                <p class="text-gray-600 mb-6 md:mb-8 text-base md:text-lg">
                    Pantau laundry Anda dari penjemputan hingga pengantaran dengan sistem pelacakan langsung kami.
                </p>
                
                <!-- Tracking Input -->
                <div class="bg-white rounded-2xl p-4 md:p-8 shadow-xl mb-6 md:mb-8">
                    <div class="flex flex-col sm:flex-row mb-4 md:mb-6 gap-3 sm:gap-0">
                        <input type="text" id="trackingInput" placeholder="Nomor pesanan" 
                               class="flex-grow px-4 md:px-6 py-3 md:py-4 border border-gray-300 rounded-xl sm:rounded-l-2xl sm:rounded-r-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm md:text-base">
                        <button onclick="trackOrder()" class="bg-blue-600 hover:bg-blue-700 px-6 md:px-8 py-3 md:py-4 rounded-xl sm:rounded-r-2xl sm:rounded-l-none font-medium text-white transition-all text-sm md:text-base whitespace-nowrap">
                            <i class="fas fa-search mr-2"></i> Lacak
                        </button>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-600 text-sm md:text-base">Contoh: 
                            <span class="font-mono font-bold text-blue-600 cursor-pointer hover:underline" onclick="fillExample('000001')">#000001</span>
                        </p>
                    </div>
                </div>
                
                <!-- Feature Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
                    <div class="bg-white rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg">
                        <div class="flex items-center mb-3 md:mb-4">
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-blue-100 flex items-center justify-center mr-3 md:mr-4 flex-shrink-0">
                                <i class="fas fa-bell text-blue-600 text-lg md:text-xl"></i>
                            </div>
                            <h4 class="font-bold text-base md:text-lg">Notifikasi Langsung</h4>
                        </div>
                        <p class="text-gray-600 text-sm md:text-base">Dapatkan pembaruan real-time untuk setiap tahap proses.</p>
                    </div>
                    <div class="bg-white rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg">
                        <div class="flex items-center mb-3 md:mb-4">
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-green-100 flex items-center justify-center mr-3 md:mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-green-600 text-lg md:text-xl"></i>
                            </div>
                            <h4 class="font-bold text-base md:text-lg">Lacak Kurir</h4>
                        </div>
                        <p class="text-gray-600 text-sm md:text-base">Lihat posisi kurir saat proses jemput/antar berlangsung.</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Content - Placeholder -->
            <div id="trackingPlaceholder" class="w-full lg:w-1/2 relative">
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl md:rounded-3xl p-6 md:p-12 shadow-2xl border border-blue-100 relative overflow-hidden">
                    <!-- Decorative circles -->
                    <div class="absolute top-0 right-0 w-40 h-40 md:w-64 md:h-64 bg-blue-100 rounded-full opacity-20 -mr-20 -mt-20 md:-mr-32 md:-mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 md:w-48 md:h-48 bg-indigo-100 rounded-full opacity-20 -ml-16 -mb-16 md:-ml-24 md:-mb-24"></div>
                    
                    <div class="relative z-10 text-center">
                        <div class="mb-6 md:mb-8 relative inline-block">
                            <div class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl md:rounded-3xl flex items-center justify-center mx-auto transform rotate-3 shadow-xl">
                                <i class="fas fa-box-open text-white text-4xl md:text-6xl"></i>
                            </div>
                            <div class="absolute -top-1 -right-1 md:-top-2 md:-right-2 w-6 h-6 md:w-8 md:h-8 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                <i class="fas fa-star text-white text-xs md:text-sm"></i>
                            </div>
                        </div>
                        
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3 md:mb-4">Lacak Pesanan Anda</h3>
                        <p class="text-gray-600 text-base md:text-lg mb-6 md:mb-8 leading-relaxed px-2">Pantau status cucian Anda secara real-time dengan memasukkan nomor pesanan di atas</p>
                        
                        <div class="flex items-center justify-center gap-3 md:gap-6 mb-6 md:mb-8 flex-wrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-blue-600 text-sm md:text-base"></i>
                                </div>
                                <span class="text-xs md:text-sm font-medium text-gray-700">Aman</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-green-600 text-sm md:text-base"></i>
                                </div>
                                <span class="text-xs md:text-sm font-medium text-gray-700">Real-time</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bell text-purple-600 text-sm md:text-base"></i>
                                </div>
                                <span class="text-xs md:text-sm font-medium text-gray-700">Notifikasi</span>
                            </div>
                        </div>
                        
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg">
                            <div class="flex items-center justify-between mb-2 md:mb-3">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <div class="w-2 h-2 md:w-3 md:h-3 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs md:text-sm font-medium text-gray-700">Status pelacakan aktif</span>
                                </div>
                                <span class="text-xs text-gray-500">24/7</span>
                            </div>
                            <p class="text-xs text-gray-600">Sistem kami siap melacak pesanan Anda kapan saja</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Content - Tracking Result -->
            <div id="trackingResult" class="w-full lg:w-1/2 relative" style="display: none;">
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-2xl border border-blue-100 relative overflow-hidden">
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 md:w-40 md:h-40 bg-blue-100 rounded-full opacity-30 -mr-16 -mt-16 md:-mr-20 md:-mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 md:w-32 md:h-32 bg-indigo-100 rounded-full opacity-30 -ml-12 -mb-12 md:-ml-16 md:-mb-16"></div>
                    
                    <div class="relative z-10">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6 md:mb-8 gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 md:gap-3 mb-2">
                                    <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                                        <i class="fas fa-receipt text-white text-base md:text-xl"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-lg md:text-2xl font-bold text-gray-800 truncate">
                                            Pesanan <span id="res_orderID" class="text-blue-600"></span>
                                        </h3>
                                        <p class="text-gray-600 text-xs md:text-sm mt-1 truncate">
                                            <i class="fas fa-user text-blue-500 mr-1"></i>
                                            <span id="res_customerName" class="font-medium"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 md:gap-3 flex-shrink-0">
                                <span id="res_statusLabel" class="px-3 py-1.5 md:px-5 md:py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-full font-semibold text-xs md:text-sm shadow-lg whitespace-nowrap"></span>
                                <button onclick="closeTracking()" class="w-8 h-8 md:w-10 md:h-10 bg-red-50 hover:bg-red-100 rounded-full flex items-center justify-center transition-all group">
                                    <i class="fas fa-times text-red-400 group-hover:text-red-600 text-sm md:text-lg"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Timeline -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg mb-4 md:mb-6">
                            <h4 class="text-base md:text-lg font-bold text-gray-800 mb-4 md:mb-6 flex items-center gap-2">
                                <i class="fas fa-route text-blue-600"></i>
                                Status Perjalanan
                            </h4>
                            <div id="res_timeline" class="space-y-0"></div>
                        </div>
                        
                        <!-- Delivery Info -->
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl md:rounded-2xl p-4 md:p-6 shadow-xl text-white mb-4 md:mb-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 md:gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-clock text-yellow-300 text-sm md:text-base"></i>
                                        <p class="text-blue-100 text-xs md:text-sm font-medium">Perkiraan Selesai</p>
                                    </div>
                                    <p id="res_deliveryTime" class="text-xl md:text-2xl font-bold"></p>
                                </div>
                                <div id="res_courierBtn" class="w-full sm:w-auto"></div>
                            </div>
                        </div>
                        
                        <!-- Additional Info Cards -->
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="bg-white/80 backdrop-blur-sm rounded-lg md:rounded-xl p-3 md:p-4 shadow-md">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-600 text-sm md:text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-gray-600">Status</p>
                                        <p class="text-xs md:text-sm font-bold text-gray-800 truncate">Terlacak</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/80 backdrop-blur-sm rounded-lg md:rounded-xl p-3 md:p-4 shadow-md">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-bell text-purple-600 text-sm md:text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-gray-600">Notifikasi</p>
                                        <p class="text-xs md:text-sm font-bold text-gray-800 truncate">Aktif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.gradient-text {
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Ensure text doesn't overflow on mobile */
@media (max-width: 640px) {
    h2, h3, h4 {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
}
</style>

<script>
function fillExample(orderNum) {
    document.getElementById('trackingInput').value = orderNum;
}

function trackOrder() {
    const orderNum = document.getElementById('trackingInput').value.trim();
    
    if (!orderNum) {
        alert('Masukkan nomor pesanan');
        return;
    }
    
    // Simulasi data pesanan
    const orderData = {
        orderID: orderNum,
        customerName: 'John Doe',
        status: 'Dalam Proses',
        deliveryTime: '16:00 WIB, Hari Ini',
        timeline: [
            { status: 'Pesanan Diterima', time: '10:00 WIB', completed: true },
            { status: 'Dijemput', time: '11:30 WIB', completed: true },
            { status: 'Sedang Dicuci', time: '14:00 WIB', completed: true },
            { status: 'Siap Diantar', time: '15:30 WIB', completed: false },
            { status: 'Selesai', time: '16:00 WIB', completed: false }
        ]
    };
    
    displayTrackingResult(orderData);
}

function displayTrackingResult(data) {
    document.getElementById('res_orderID').textContent = '#' + data.orderID;
    document.getElementById('res_customerName').textContent = data.customerName;
    document.getElementById('res_statusLabel').textContent = data.status;
    document.getElementById('res_deliveryTime').textContent = data.deliveryTime;
    
    // Timeline
    const timeline = document.getElementById('res_timeline');
    timeline.innerHTML = data.timeline.map((item, index) => `
        <div class="flex gap-3 md:gap-4 ${index !== data.timeline.length - 1 ? 'pb-6 md:pb-8' : ''}">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-full ${item.completed ? 'bg-gradient-to-br from-green-400 to-green-600' : 'bg-gray-200'} flex items-center justify-center shadow-md flex-shrink-0">
                    <i class="fas fa-${item.completed ? 'check' : 'clock'} text-white text-xs md:text-sm"></i>
                </div>
                ${index !== data.timeline.length - 1 ? `<div class="w-0.5 md:w-1 flex-1 ${item.completed ? 'bg-green-400' : 'bg-gray-200'} mt-2"></div>` : ''}
            </div>
            <div class="flex-1 ${index !== data.timeline.length - 1 ? 'pb-2' : ''}">
                <p class="font-bold text-gray-800 text-sm md:text-base">${item.status}</p>
                <p class="text-gray-600 text-xs md:text-sm mt-1">${item.time}</p>
            </div>
        </div>
    `).join('');
    
    // Courier button
    const courierBtn = document.getElementById('res_courierBtn');
    courierBtn.innerHTML = `
        <button class="bg-white text-blue-600 hover:bg-blue-50 px-4 md:px-6 py-2 md:py-3 rounded-full font-semibold transition-all shadow-lg text-xs md:text-sm whitespace-nowrap w-full sm:w-auto">
            <i class="fas fa-map-marker-alt mr-2"></i>
            Lacak Kurir
        </button>
    `;
    
    // Show result, hide placeholder
    document.getElementById('trackingPlaceholder').style.display = 'none';
    document.getElementById('trackingResult').style.display = 'block';
}

function closeTracking() {
    document.getElementById('trackingResult').style.display = 'none';
    document.getElementById('trackingPlaceholder').style.display = 'block';
    document.getElementById('trackingInput').value = '';
}
</script>

    <!-- Pricing Section -->
<section id="pricing" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4 text-gray-800">Layanan <span class="gradient-text">Laundry</span> Kami</h2>
            <p class="text-gray-600 max-w-3xl mx-auto text-lg">Pilih layanan yang sesuai dengan kebutuhan Anda dengan harga transparan</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @foreach($premiumServices as $index => $service)
            @php
                // Array warna agar tampilan bervariasi
                $colors = ['blue', 'purple', 'orange', 'pink'];
                $color = $colors[$index % 4];
            @endphp
            
            <div class="pricing-card-modern group">
                <div class="icon-wrapper bg-gradient-to-br from-{{$color}}-400 to-{{$color}}-600">
                    <i class="fas {{ $service->pricing_type == 'kg' ? 'fa-weight-hanging' : 'fa-tshirt' }} text-white text-3xl"></i>
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $service->name }}</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $service->description }}</p>
                
                <div class="price-tag">
                    <span class="text-3xl font-bold text-gray-800">
                        Rp {{ number_format($service->pricing_type == 'kg' ? $service->price_per_kg : $service->price_per_unit, 0, ',', '.') }}
                    </span>
                    <span class="text-gray-600 text-sm">/{{ $service->pricing_type }}</span>
                </div>
                 <a href="{{ route('booking', ['service_id' => $service->id]) }}" 
                class="w-full inline-block text-center bg-{{$color}}-600 hover:bg-{{$color}}-700 text-white py-3 rounded-xl font-semibold transition-all transform group-hover:scale-105">
                    Pesan Sekarang
                </a>
            </div>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ route('services.index') }}" class="inline-flex items-center px-8 py-3 border-2 border-blue-600 text-blue-600 font-bold rounded-full hover:bg-blue-600 hover:text-white transition-all">
                Lihat Semua Layanan <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 text-gray-800">Kata <span class="gradient-text">Pelanggan</span> Kami</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">Bergabunglah dengan ribuan pelanggan puas yang mempercayakan kebutuhan laundry mereka kepada kami</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="testimonial-card">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-xl mr-4">
                            SJ
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">Sarah Johnson</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Rizki Laundry benar-benar menghemat waktu saya! Pakaian selalu kembali dengan lipatan rapi dan wangi segar. Saya juga suka pendekatan ramah lingkungan mereka!"</p>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Pelanggan selama 2 tahun
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-500 to-blue-500 flex items-center justify-center text-white font-bold text-xl mr-4">
                            MC
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">Michael Chen</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Fitur pelacakannya luar biasa! Saya selalu tahu persis di mana posisi laundry saya. Paket premium sangat worth it untuk keluarga kami yang berempat!"</p>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Pelanggan selama 1 tahun
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xl mr-4">
                            DM
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">David Martinez</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Awalnya ragu, tapi setelah mencoba Rizki Laundry, saya tidak akan kembali mencuci sendiri. Layanan dry clean mereka sangat bagus untuk setelan kerja saya."</p>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Pelanggan selama 8 bulan
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    
    :root {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --secondary: #8b5cf6;
        --accent: #10b981;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8fafc;
        overflow-x: hidden;
    }
    
    .gradient-bg {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    }
    
    .gradient-text {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .hero-image {
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(2deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    
    .service-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .service-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .service-card:hover::after {
        transform: scaleX(1);
    }
    
    .pricing-card {
        transition: all 0.3s ease;
    }
    
    .pricing-card:hover {
        transform: scale(1.03);
    }
    
    .feature-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    }
    
    .stats-card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    /* Tracking Step Styles - Updated */
    .tracking-step {
        position: relative;
        padding-left: 40px;
        transition: all 0.3s ease;
    }
    
    .tracking-step::before {
        content: '';
        position: absolute;
        left: 0;
        top: 5px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #e5e7eb;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .tracking-step::after {
        content: '';
        position: absolute;
        left: 11px;
        top: 29px;
        width: 2px;
        height: calc(100% + 20px);
        background-color: #e5e7eb;
    }
    
    .tracking-step:last-child::after {
        display: none;
    }
    
    .tracking-step.active::before {
        background-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2), 0 0 0 5px rgba(59, 130, 246, 0.1);
        animation: pulse 2s ease-in-out infinite;
    }
    
    .tracking-step.completed::before {
        background-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    
    .tracking-step.completed::after {
        background-color: var(--accent);
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2), 0 0 0 5px rgba(59, 130, 246, 0.1);
        }
        50% {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), 0 0 0 8px rgba(59, 130, 246, 0.15);
        }
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .slide-in {
        animation: slideIn 0.5s ease-out;
    }
    
    .testimonial-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .testimonial-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
    }
    
    .btn-primary::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.7s ease;
    }
    
    .btn-primary:hover::after {
        left: 100%;
    }
    
    .btn-secondary {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: var(--primary);
        color: white;
    }
    
    .floating-action {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        z-index: 100;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .floating-action:hover {
        transform: scale(1.1) rotate(90deg);
    }
    
    .stats-counter {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Tab Styles for How It Works */
    .tab-button {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .tab-button.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }
    
    .tab-button::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.7s ease;
    }
    
    .tab-button:hover::after {
        left: 100%;
    }
    
    .service-content {
        display: none;
        animation: fadeIn 0.5s ease-in;
    }
    
    .service-content.active {
        display: grid;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .step-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }
    
    .step-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .step-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .step-card:hover::after {
        transform: scaleX(1);
    }
    
    /* Pricing Section Styles */
    .pricing-toggle {
        color: #6b7280;
        transition: all 0.3s ease;
    }
    
    .pricing-toggle.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .pricing-content {
        display: none;
        animation: fadeIn 0.5s ease-in;
    }
    
    .pricing-content.active {
        display: block;
    }
    
    .pricing-card-modern {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .pricing-card-modern:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    
    .price-tag {
        margin: 16px 0;
    }
    
    .membership-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .membership-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }
    
    .membership-card.featured {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        transform: scale(1.05);
    }
    
    .membership-card.featured:hover {
        transform: scale(1.08) translateY(-8px);
    }
    
    @media (max-width: 768px) {
        .hero-image {
            width: 70%;
        }
        
        .stats-counter {
            font-size: 2rem;
        }
    }
/* CSS Tambahan untuk Timeline Fix */
    .tracking-step {
        position: relative;
        padding-left: 45px;
        padding-bottom: 30px;
        border-left: 2px solid #e5e7eb;
    }
    .tracking-step:last-child {
        border-left: 2px solid transparent;
        padding-bottom: 0;
    }
    .tracking-step::before {
        content: '';
        position: absolute;
        left: -9px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #d1d5db;
        z-index: 2;
    }
    .tracking-step.completed { border-left-color: #10b981; }
    .tracking-step.completed::before { background: #10b981; border-color: #10b981; }
    .tracking-step.active::before { 
        background: #3b82f6; 
        border-color: #3b82f6; 
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
    /**
     * Fungsi Switch Tab antara Pickup dan Walk-in
     */
    function switchTab(tabName) {
        // Ambil semua elemen tab dan content
        const pickupTab = document.getElementById('pickupTab');
        const walkinTab = document.getElementById('walkinTab');
        const pickupContent = document.getElementById('pickupContent');
        const walkinContent = document.getElementById('walkinContent');

        if (tabName === 'pickup') {
            // Aktifkan tab Pickup
            pickupTab.classList.add('active');
            pickupTab.classList.remove('text-gray-600');
            walkinTab.classList.remove('active');
            walkinTab.classList.add('text-gray-600');

            // Tampilkan content Pickup
            pickupContent.style.display = 'grid';
            pickupContent.classList.add('active');
            walkinContent.style.display = 'none';
            walkinContent.classList.remove('active');
        } else if (tabName === 'walkin') {
            // Aktifkan tab Walk-in
            walkinTab.classList.add('active');
            walkinTab.classList.remove('text-gray-600');
            pickupTab.classList.remove('active');
            pickupTab.classList.add('text-gray-600');

            // Tampilkan content Walk-in
            walkinContent.style.display = 'grid';
            walkinContent.classList.add('active');
            pickupContent.style.display = 'none';
            pickupContent.classList.remove('active');
        }
    }

    /**
     * Fungsi Utama Pelacakan
     * Hanya berjalan jika dipanggil (Klik tombol / Enter)
     */
    function trackOrder() {
        const input = document.getElementById('trackingInput');
        if (!input) return;

        const trackingNumber = input.value.trim();
        
        if (!trackingNumber) {
            alert('Silakan masukkan nomor tracking');
            return;
        }

        // URL dari Laravel
        const url = "{{ route('order.track') }}?number=" + trackingNumber;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Server bermasalah');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Sembunyikan placeholder, tampilkan hasil
                    const placeholder = document.getElementById('trackingPlaceholder');
                    const resultDiv = document.getElementById('trackingResult');
                    
                    if (placeholder) placeholder.style.display = 'none';
                    if (resultDiv) {
                        resultDiv.style.display = 'block';
                        resultDiv.classList.add('slide-in');
                    }

                    // Fungsi pembantu untuk mengisi teks dengan aman
                    const setText = (id, val) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val;
                    };

                    setText('res_orderID', data.order_number);
                    setText('res_customerName', data.customer_name);
                    setText('res_statusLabel', data.status_label);
                    setText('res_deliveryTime', data.delivery_time);

                    // Render Timeline
                    const timelineEl = document.getElementById('res_timeline');
                    if (timelineEl) {
                        timelineEl.innerHTML = data.steps.map(step => `
                            <div class="tracking-step ${step.is_completed ? 'completed' : ''} ${step.is_active ? 'active' : ''}">
                                <h4 class="font-bold text-lg text-gray-800 mb-1">${step.title}</h4>
                                <p class="text-gray-500 text-sm">${step.desc}</p>
                            </div>
                        `).join('');
                    }

                    // Tombol Kurir
                    const btnContainer = document.getElementById('res_courierBtn');
                    if (btnContainer) {
                        btnContainer.innerHTML = data.courier_phone ? `
                            <a href="https://wa.me/${data.courier_phone}" target="_blank" class="btn-primary px-6 py-3 rounded-xl font-medium text-white inline-block">
                                <i class="fab fa-whatsapp mr-2"></i> Chat Kurir
                            </a>` : '';
                    }

                    if (resultDiv) resultDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal mengambil data. Pastikan nomor order benar.');
            });
    }

    /**
     * Fungsi untuk mengisi contoh (FC-789...)
     */
    function fillExample(id) {
        const input = document.getElementById('trackingInput');
        if (input) {
            input.value = id;
            // Kita tidak panggil trackOrder() secara otomatis di sini agar user yang klik
        }
    }

    /**
     * Fungsi Menutup Hasil Tracking
     */
    function closeTracking() {
        const resultDiv = document.getElementById('trackingResult');
        const placeholder = document.getElementById('trackingPlaceholder');
        
        if (resultDiv) resultDiv.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
        
        const input = document.getElementById('trackingInput');
        if (input) input.value = '';
    }

    /**
     * Fungsi Menutup Modal Booking Success
     */
    function closeBookingModal() {
        const modal = document.getElementById('bookingSuccessModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        console.log('Modal ditutup'); // Debug log
        return false;
    }

    /**
     * Inisialisasi Event Listener
     */
    document.addEventListener('DOMContentLoaded', function() {
        const trackingInput = document.getElementById('trackingInput');
        if (trackingInput) {
            // Hanya aktifkan Enter key, tidak menjalankan fetch saat load
            trackingInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    trackOrder();
                }
            });
        }

        // Set default tab saat halaman load
        switchTab('pickup');

        // Auto close modal booking success setelah 30 detik
        const bookingModal = document.getElementById('bookingSuccessModal');
        if (bookingModal && bookingModal.style.display !== 'none') {
            setTimeout(function() {
                closeBookingModal();
            }, 30000);
        }
    });
</script>
@endpush
