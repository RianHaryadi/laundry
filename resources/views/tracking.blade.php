@extends('layouts.app')

@section('title', 'Tracking - LaundryKu')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Animated Header -->
        <div class="text-center mb-16">
            <div class="relative inline-block mb-8">
                <div class="absolute -inset-4 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full blur-xl opacity-30 animate-pulse"></div>
                <div class="relative w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                    <i class="fas fa-shipping-fast text-white text-4xl animate-bounce"></i>
                </div>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent animate-fade-in">
                Lacak Pesanan Anda
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto animate-slide-up">
                Pantau perjalanan laundry Anda secara real-time dengan teknologi tracking terkini
            </p>
        </div>

        <!-- Search Section with Floating Effect -->
        <div class="max-w-4xl mx-auto mb-16 animate-slide-up" style="animation-delay: 0.2s">
            <div class="relative">
                <!-- Floating Background Effect -->
                <div class="absolute -inset-8 bg-gradient-to-r from-blue-400/20 to-indigo-500/20 rounded-3xl blur-3xl"></div>
                
                <div class="relative bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 border border-white/20">
                    <div class="flex items-center mb-6">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-search text-white text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Cari Pesanan</h2>
                            <p class="text-gray-600">Masukkan kode tracking atau nomor telepon</p>
                        </div>
                    </div>

                    <form action="{{ route('tracking.search') }}" method="POST" id="tracking-form">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Tracking Code Input -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-3">Kode Tracking</label>
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                                    <div class="relative">
                                        <input type="text" name="tracking_code" 
                                               class="w-full pl-12 pr-4 py-4 bg-white rounded-xl border-0 focus:ring-4 focus:ring-blue-100 focus:outline-none text-lg font-medium shadow-inner"
                                               placeholder="LDRY-789456"
                                               value="{{ old('tracking_code') }}"
                                               id="tracking-input">
                                        <div class="absolute left-4 top-4">
                                            <i class="fas fa-barcode text-blue-500 text-xl"></i>
                                        </div>
                                        <button type="button" onclick="pasteFromClipboard()" 
                                                class="absolute right-3 top-3 text-gray-400 hover:text-blue-600 transition duration-300"
                                                title="Tempel dari clipboard">
                                            <i class="fas fa-paste"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone Number Input -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-3">Nomor Telepon</label>
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                                    <div class="relative">
                                        <input type="tel" name="phone" 
                                               class="w-full pl-12 pr-4 py-4 bg-white rounded-xl border-0 focus:ring-4 focus:ring-blue-100 focus:outline-none text-lg font-medium shadow-inner"
                                               placeholder="08xxxxxxxxxx"
                                               value="{{ old('phone') }}"
                                               id="phone-input">
                                        <div class="absolute left-4 top-4">
                                            <i class="fas fa-phone text-blue-500 text-xl"></i>
                                        </div>
                                        <button type="button" onclick="fillMyNumber()" 
                                                class="absolute right-3 top-3 text-gray-400 hover:text-blue-600 transition duration-300"
                                                title="Gunakan nomor saya">
                                            <i class="fas fa-user"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" 
                                    class="flex-1 group relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-4 px-8 rounded-xl font-bold text-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                                <span class="relative flex items-center justify-center">
                                    <i class="fas fa-satellite-dish mr-3 animate-pulse"></i>
                                    Lacak Sekarang
                                    <i class="fas fa-arrow-right ml-3 group-hover:translate-x-2 transition-transform duration-300"></i>
                                </span>
                            </button>
                            <button type="button" onclick="scanQRCode()"
                                    class="flex items-center justify-center gap-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white py-4 px-8 rounded-xl font-bold text-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300">
                                <i class="fas fa-qrcode"></i>
                                Scan QR Code
                            </button>
                        </div>
                    </form>

                    <!-- Recent Searches -->
                    @if(auth()->check() && count($recentSearches ?? []) > 0)
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-history mr-2 text-blue-500"></i>
                            Pencarian Terakhir
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($recentSearches as $search)
                            <button onclick="fillTrackingCode('{{ $search->tracking_code }}')"
                                    class="px-4 py-2 bg-gray-100 hover:bg-blue-100 rounded-lg text-gray-700 hover:text-blue-700 transition duration-300 flex items-center">
                                <span class="font-mono">{{ $search->tracking_code }}</span>
                                <span class="ml-2 text-xs px-2 py-1 rounded-full 
                                    @if($search->status === 'completed') bg-green-100 text-green-800
                                    @elseif($search->status === 'processing') bg-blue-100 text-blue-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($search->status) }}
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @isset($order)
        <!-- Order Tracking Dashboard -->
        <div class="animate-fade-in" style="animation-delay: 0.3s">
            <!-- Status Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-blue-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold text-blue-600">{{ $order->quantity }}</span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Jumlah (kg)</h3>
                    <p class="text-gray-600 text-sm">Berat cucian</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-green-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold text-green-600">
                            @if($order->status === 'pending') 4
                            @elseif($order->status === 'processing') 3
                            @elseif($order->status === 'ready') 2
                            @else 1
                            @endif
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Step Tersisa</h3>
                    <p class="text-gray-600 text-sm">Menuju penyelesaian</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-purple-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold text-purple-600">
                            {{ number_format($order->price ?? $order->quantity * 10000, 0, ',', '.') }}
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Total Biaya</h3>
                    <p class="text-gray-600 text-sm">Estimasi pembayaran</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-orange-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-truck text-orange-600 text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold text-orange-600">
                            @if($order->status === 'completed') 0
                            @elseif($order->status === 'ready') 1
                            @elseif($order->status === 'processing') 2
                            @else 3
                            @endif
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Hari Tersisa</h3>
                    <p class="text-gray-600 text-sm">Estimasi penyelesaian</p>
                </div>
            </div>

            <!-- Main Tracking Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Tracking Progress -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <!-- Status Header -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                            <div class="relative">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Tracking Pesanan #{{ $order->tracking_code }}</h2>
                                        <p class="text-blue-100">Pesanan dibuat: {{ $order->created_at->isoFormat('dddd, D MMMM YYYY') }}</p>
                                    </div>
                                    <div class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full">
                                        <span class="text-white font-bold text-lg">{{ strtoupper($order->status) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mt-6">
                                    <div class="flex justify-between text-sm text-blue-100 mb-2">
                                        <span>Order Received</span>
                                        <span>100%</span>
                                    </div>
                                    <div class="w-full h-3 bg-white/20 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full progress-animation"
                                             style="width: 
                                                @if($order->status === 'pending') 25%
                                                @elseif($order->status === 'processing') 50%
                                                @elseif($order->status === 'ready') 75%
                                                @else 100%
                                                @endif">
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-2 text-xs text-blue-100">
                                        <span>0%</span>
                                        <span>25%</span>
                                        <span>50%</span>
                                        <span>75%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Timeline -->
                        <div class="p-8">
                            <div class="relative">
                                <!-- Timeline Line -->
                                <div class="absolute left-8 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-indigo-500 to-purple-500"></div>
                                
                                <!-- Timeline Items -->
                                <div class="space-y-12">
                                    @php
                                        $steps = [
                                            [
                                                'status' => 'received',
                                                'icon' => 'fas fa-inbox',
                                                'title' => 'Pesanan Diterima',
                                                'description' => 'Pesanan Anda telah berhasil diregistrasi',
                                                'time' => $order->created_at->isoFormat('HH:mm'),
                                                'color' => 'from-green-400 to-emerald-500',
                                                'active' => true
                                            ],
                                            [
                                                'status' => 'processing',
                                                'icon' => 'fas fa-washing-machine',
                                                'title' => 'Sedang Diproses',
                                                'description' => 'Cucian sedang dalam proses pencucian',
                                                'time' => $order->status === 'processing' ? 'Saat ini' : ($order->status === 'ready' || $order->status === 'completed' ? 'Selesai' : 'Menunggu'),
                                                'color' => $order->status === 'processing' || $order->status === 'ready' || $order->status === 'completed' ? 'from-blue-400 to-indigo-500' : 'from-gray-300 to-gray-400',
                                                'active' => $order->status === 'processing' || $order->status === 'ready' || $order->status === 'completed'
                                            ],
                                            [
                                                'status' => 'quality_check',
                                                'icon' => 'fas fa-search',
                                                'title' => 'Quality Check',
                                                'description' => 'Pengecekan kualitas dan penyetrikaan',
                                                'time' => $order->status === 'ready' || $order->status === 'completed' ? 'Selesai' : 'Menunggu',
                                                'color' => $order->status === 'ready' || $order->status === 'completed' ? 'from-yellow-400 to-orange-500' : 'from-gray-300 to-gray-400',
                                                'active' => $order->status === 'ready' || $order->status === 'completed'
                                            ],
                                            [
                                                'status' => 'delivery',
                                                'icon' => 'fas fa-truck',
                                                'title' => 'Pengantaran',
                                                'description' => 'Pesanan sedang dalam perjalanan',
                                                'time' => $order->status === 'completed' ? 'Terkirim' : ($order->status === 'ready' ? 'Siap' : 'Menunggu'),
                                                'color' => $order->status === 'completed' ? 'from-purple-400 to-pink-500' : 'from-gray-300 to-gray-400',
                                                'active' => $order->status === 'completed'
                                            ],
                                            [
                                                'status' => 'completed',
                                                'icon' => 'fas fa-check-circle',
                                                'title' => 'Selesai',
                                                'description' => 'Pesanan telah diterima customer',
                                                'time' => $order->status === 'completed' ? $order->updated_at->isoFormat('HH:mm') : 'Menunggu',
                                                'color' => $order->status === 'completed' ? 'from-green-400 to-emerald-500' : 'from-gray-300 to-gray-400',
                                                'active' => $order->status === 'completed'
                                            ]
                                        ];
                                    @endphp

                                    @foreach($steps as $step)
                                    <div class="flex items-start group cursor-pointer timeline-item" 
                                         data-status="{{ $step['status'] }}">
                                        <div class="flex-shrink-0 relative z-10">
                                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $step['color'] }} 
                                                flex items-center justify-center text-white shadow-lg transform group-hover:scale-110 
                                                transition duration-300 {{ $step['active'] ? 'ring-4 ring-opacity-30 ring-white' : '' }}">
                                                <i class="{{ $step['icon'] }} text-xl"></i>
                                            </div>
                                            <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs font-bold 
                                                {{ $step['active'] ? 'text-blue-600' : 'text-gray-400' }}">
                                                {{ $step['time'] }}
                                            </div>
                                        </div>
                                        <div class="ml-8 bg-gray-50 group-hover:bg-white rounded-xl p-5 flex-grow 
                                            transform group-hover:-translate-y-1 transition duration-300 shadow-sm group-hover:shadow-md">
                                            <div class="flex justify-between items-center mb-2">
                                                <h4 class="font-bold text-lg text-gray-800">{{ $step['title'] }}</h4>
                                                @if($step['active'])
                                                <span class="px-3 py-1 bg-gradient-to-r from-green-100 to-emerald-100 
                                                    text-green-800 rounded-full text-xs font-bold animate-pulse">
                                                    AKTIF
                                                </span>
                                                @endif
                                            </div>
                                            <p class="text-gray-600">{{ $step['description'] }}</p>
                                            @if($step['status'] === 'processing' && $order->status === 'processing')
                                            <div class="mt-4">
                                                <div class="flex items-center mb-2">
                                                    <i class="fas fa-temperature-high text-red-500 mr-2"></i>
                                                    <span class="text-sm text-gray-700">Suhu: 40°C</span>
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-tint text-blue-500 mr-2"></i>
                                                    <span class="text-sm text-gray-700">Detergen: Lemon Fresh</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full progress-animation" style="width: 65%"></div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Information Panel -->
                <div class="space-y-6">
                    <!-- Order Summary Card -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <h3 class="font-bold text-gray-800 text-xl mb-6 flex items-center">
                            <i class="fas fa-receipt text-blue-600 mr-3"></i>
                            Ringkasan Pesanan
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Kode Pesanan</span>
                                <div class="flex items-center">
                                    <span class="font-mono font-bold text-blue-600">{{ $order->tracking_code }}</span>
                                    <button onclick="copyToClipboard('{{ $order->tracking_code }}')" 
                                            class="ml-2 text-gray-400 hover:text-blue-600 transition duration-300">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Layanan</span>
                                <span class="font-semibold">{{ $order->service }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Tanggal Pickup</span>
                                <span class="font-semibold">{{ $order->pickup_date->isoFormat('DD/MM/YY') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Estimasi Selesai</span>
                                <span class="font-semibold text-green-600">
                                    {{ $order->pickup_date->addDays(2)->isoFormat('DD/MM/YY') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Tracking Card -->
                    @if($order->status === 'ready')
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl p-6 text-white">
                        <h3 class="font-bold text-xl mb-4 flex items-center">
                            <i class="fas fa-map-marked-alt mr-3"></i>
                            Live Tracking Driver
                        </h3>
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span>Perkiraan Tiba</span>
                                <span class="font-bold">30-45 menit</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                <div class="bg-white h-2 rounded-full" style="width: 70%"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="font-bold">Budi Santoso</p>
                                    <p class="text-sm text-green-100">Driver</p>
                                </div>
                            </div>
                            <button onclick="callDriver('6281234567890')"
                                    class="bg-white text-green-600 px-4 py-2 rounded-lg font-bold hover:bg-gray-100 transition duration-300">
                                <i class="fas fa-phone mr-1"></i> Hubungi
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Quick Actions Card -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <h3 class="font-bold text-gray-800 text-xl mb-6 flex items-center">
                            <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                            Tindakan Cepat
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="shareTracking()"
                                    class="p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition duration-300 flex flex-col items-center">
                                <i class="fas fa-share-alt text-blue-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-700">Bagikan</span>
                            </button>
                            <button onclick="downloadReceipt()"
                                    class="p-4 bg-green-50 rounded-xl hover:bg-green-100 transition duration-300 flex flex-col items-center">
                                <i class="fas fa-download text-green-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-700">Invoice</span>
                            </button>
                            <button onclick="scheduleReorder()"
                                    class="p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition duration-300 flex flex-col items-center">
                                <i class="fas fa-redo text-purple-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-700">Pesan Ulang</span>
                            </button>
                            <button onclick="contactSupport()"
                                    class="p-4 bg-orange-50 rounded-xl hover:bg-orange-100 transition duration-300 flex flex-col items-center">
                                <i class="fas fa-headset text-orange-600 text-xl mb-2"></i>
                                <span class="text-sm font-medium text-gray-700">Bantuan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Notifications Card -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <h3 class="font-bold text-gray-800 text-xl mb-4 flex items-center">
                            <i class="fas fa-bell text-red-500 mr-3"></i>
                            Notifikasi
                        </h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                <p class="text-sm text-gray-700">Status update: Pesanan sedang diproses</p>
                                <p class="text-xs text-gray-500 mt-1">2 jam yang lalu</p>
                            </div>
                            <div class="p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                                <p class="text-sm text-gray-700">Driver telah menjemput pesanan</p>
                                <p class="text-xs text-gray-500 mt-1">1 hari yang lalu</p>
                            </div>
                            <button onclick="enableNotifications()"
                                    class="w-full mt-4 p-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold hover:shadow-lg transition duration-300">
                                <i class="fas fa-bell mr-2"></i>
                                Aktifkan Notifikasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Visualization -->
            @if($order->status === 'ready')
            <div class="mt-8 animate-slide-up">
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="font-bold text-gray-800 text-xl mb-6 flex items-center">
                        <i class="fas fa-map text-blue-600 mr-3"></i>
                        Rute Pengantaran
                    </h3>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-dashed border-blue-200">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-3">A</div>
                                <div>
                                    <p class="font-bold">LaundryKu Store</p>
                                    <p class="text-sm text-gray-600">Jl. Sudirman No. 123</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">4.2 km</div>
                                <p class="text-sm text-gray-600">Jarak tempuh</p>
                            </div>
                            <div class="flex items-center">
                                <div>
                                    <p class="font-bold text-right">Rumah Anda</p>
                                    <p class="text-sm text-gray-600 text-right">{{ Str::limit($order->address, 20) }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold ml-3">B</div>
                            </div>
                        </div>
                        
                        <!-- Map Visualization -->
                        <div class="relative h-48 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-lg overflow-hidden">
                            <!-- Route Line -->
                            <div class="absolute top-1/2 left-4 right-4 h-1 bg-gradient-to-r from-blue-500 to-green-500 transform -translate-y-1/2"></div>
                            
                            <!-- Store Marker -->
                            <div class="absolute top-1/2 left-8 transform -translate-y-1/2">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-lg animate-pulse">
                                    <i class="fas fa-store"></i>
                                </div>
                            </div>
                            
                            <!-- Moving Delivery Truck -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                <div class="relative">
                                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center text-white shadow-xl animate-bounce">
                                        <i class="fas fa-truck text-xl"></i>
                                    </div>
                                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-3 py-1 rounded-lg text-sm font-bold">
                                        <i class="fas fa-clock mr-1"></i> 30m
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Home Marker -->
                            <div class="absolute top-1/2 right-8 transform -translate-y-1/2">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg">
                                    <i class="fas fa-home"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-3 gap-4 text-center">
                            <div class="p-3 bg-white rounded-lg shadow-sm">
                                <div class="text-2xl font-bold text-blue-600">15m</div>
                                <p class="text-sm text-gray-600">Waktu Jemput</p>
                            </div>
                            <div class="p-3 bg-white rounded-lg shadow-sm">
                                <div class="text-2xl font-bold text-purple-600">2.1km</div>
                                <p class="text-sm text-gray-600">Telah Ditempuh</p>
                            </div>
                            <div class="p-3 bg-white rounded-lg shadow-sm">
                                <div class="text-2xl font-bold text-green-600">30m</div>
                                <p class="text-sm text-gray-600">Estimasi Tiba</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endisset
    </div>
</div>

<!-- Floating Chat Assistant -->
<div id="chat-assistant" class="fixed bottom-6 right-6 z-50">
    <button onclick="toggleChat()"
            class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-2xl flex items-center justify-center hover:scale-110 transition duration-300">
        <i class="fas fa-comment-dots text-2xl"></i>
    </button>
</div>

<!-- QR Code Scanner Modal -->
<div id="qr-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-8 animate-scale-in">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Scan QR Code</h3>
            <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div class="bg-gray-100 rounded-xl p-8 flex items-center justify-center mb-6">
            <i class="fas fa-qrcode text-6xl text-gray-400"></i>
        </div>
        <p class="text-gray-600 text-center mb-6">Arahkan kamera ke QR Code pada invoice atau tagihan Anda</p>
        <button onclick="openCamera()"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-4 rounded-xl font-bold hover:shadow-lg transition duration-300">
            <i class="fas fa-camera mr-2"></i> Buka Kamera
        </button>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    
    @keyframes progress {
        from { width: 0%; }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }
    
    .animate-slide-up {
        animation: slideUp 0.6s ease-out;
    }
    
    .animate-scale-in {
        animation: scaleIn 0.3s ease-out;
    }
    
    .progress-animation {
        animation: progress 1.5s ease-out;
    }
    
    .timeline-item:hover .timeline-content {
        transform: translateX(10px);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #2563eb, #7c3aed);
    }
    
    /* Glow effect for inputs */
    .glow-input:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    
    /* Pulse animation for active elements */
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        50% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    }
    
    .pulse-glow {
        animation: pulse-glow 2s infinite;
    }
</style>

<script>
    // Animations on load
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bars
        document.querySelectorAll('.progress-animation').forEach(bar => {
            bar.style.animationPlayState = 'running';
        });
        
        // Add floating animation to timeline items
        const timelineItems = document.querySelectorAll('.timeline-item');
        timelineItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Initialize tooltips
        initializeTooltips();
    });
    
    // QR Code Scanner
    function scanQRCode() {
        document.getElementById('qr-modal').classList.remove('hidden');
        document.getElementById('qr-modal').classList.add('flex');
    }
    
    function closeQRModal() {
        document.getElementById('qr-modal').classList.add('hidden');
        document.getElementById('qr-modal').classList.remove('flex');
    }
    
    function openCamera() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    alert('Kamera berhasil diakses! Arahkan ke QR Code.');
                    // In a real app, you would process the QR code here
                })
                .catch(function(err) {
                    alert('Tidak dapat mengakses kamera: ' + err.message);
                });
        } else {
            alert('Browser Anda tidak mendukung akses kamera.');
        }
    }
    
    // Clipboard functions
    function pasteFromClipboard() {
        navigator.clipboard.readText().then(text => {
            document.getElementById('tracking-input').value = text;
            showNotification('Kode tracking berhasil ditempel!', 'success');
        }).catch(err => {
            showNotification('Gagal membaca clipboard', 'error');
        });
    }
    
    function fillMyNumber() {
        // In a real app, get user's phone from profile
        const userPhone = '{{ auth()->user()->phone ?? "" }}';
        if (userPhone) {
            document.getElementById('phone-input').value = userPhone;
        } else {
            showNotification('Masuk ke akun Anda untuk mengisi nomor otomatis', 'info');
        }
    }
    
    function fillTrackingCode(code) {
        document.getElementById('tracking-input').value = code;
        document.getElementById('tracking-form').submit();
    }
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Kode berhasil disalin!', 'success');
        });
    }
    
    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-6 right-6 px-6 py-4 rounded-xl shadow-2xl z-50 animate-slide-up ${getNotificationColor(type)}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${getNotificationIcon(type)} mr-3 text-xl"></i>
                <div>
                    <p class="font-bold">${getNotificationTitle(type)}</p>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    function getNotificationColor(type) {
        const colors = {
            success: 'bg-gradient-to-r from-green-500 to-emerald-600 text-white',
            error: 'bg-gradient-to-r from-red-500 to-pink-600 text-white',
            info: 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
            warning: 'bg-gradient-to-r from-yellow-500 to-orange-600 text-white'
        };
        return colors[type] || colors.info;
    }
    
    function getNotificationIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle',
            warning: 'fa-exclamation-triangle'
        };
        return icons[type] || icons.info;
    }
    
    function getNotificationTitle(type) {
        const titles = {
            success: 'Berhasil!',
            error: 'Error!',
            info: 'Informasi',
            warning: 'Peringatan'
        };
        return titles[type] || titles.info;
    }
    
    // Action functions
    function callDriver(phone) {
        window.location.href = `tel:${phone}`;
    }
    
    function shareTracking() {
        const shareData = {
            title: 'Tracking Pesanan LaundryKu',
            text: `Lacak pesanan saya dengan kode: {{ $order->tracking_code ?? '' }}`,
            url: window.location.href
        };
        
        if (navigator.share) {
            navigator.share(shareData);
        } else {
            copyToClipboard(window.location.href);
            showNotification('Link berhasil disalin!', 'success');
        }
    }
    
    function downloadReceipt() {
        showNotification('Mengunduh invoice...', 'info');
        // In a real app, this would trigger a PDF download
        setTimeout(() => {
            showNotification('Invoice berhasil diunduh!', 'success');
        }, 1500);
    }
    
    function scheduleReorder() {
        if (confirm('Ingin memesan ulang layanan yang sama?')) {
            window.location.href = "#";
        }
    }
    
    function contactSupport() {
        window.open('https://wa.me/6281234567890', '_blank');
    }
    
    function enableNotifications() {
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    showNotification('Notifikasi berhasil diaktifkan!', 'success');
                }
            });
        } else {
            showNotification('Notifikasi sudah diaktifkan sebelumnya', 'info');
        }
    }
    
    // Chat assistant
    function toggleChat() {
        showNotification('Fitur chat assistant sedang dalam pengembangan', 'info');
    }
    
    // Initialize tooltips
    function initializeTooltips() {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(el => {
            el.addEventListener('mouseenter', showTooltip);
            el.addEventListener('mouseleave', hideTooltip);
        });
    }
    
    function showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute bg-gray-900 text-white px-3 py-2 rounded-lg text-sm z-50';
        tooltip.textContent = e.target.title;
        tooltip.id = 'tooltip';
        
        const rect = e.target.getBoundingClientRect();
        tooltip.style.top = `${rect.top - 40}px`;
        tooltip.style.left = `${rect.left + (rect.width / 2)}px`;
        tooltip.style.transform = 'translateX(-50%)';
        
        document.body.appendChild(tooltip);
    }
    
    function hideTooltip() {
        const tooltip = document.getElementById('tooltip');
        if (tooltip) tooltip.remove();
    }
    
    // Form submission with animation
    document.getElementById('tracking-form')?.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        
        submitBtn.innerHTML = `
            <span class="relative flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-3"></i>
                Melacak...
            </span>
        `;
        submitBtn.disabled = true;
        
        // Add loading animation to the form
        this.classList.add('opacity-75');
        
        setTimeout(() => {
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
            this.classList.remove('opacity-75');
        }, 2000);
    });
    
    // Real-time updates for processing orders
    @isset($order)
    @if($order->status === 'processing' || $order->status === 'ready')
    function startRealTimeUpdates() {
        setInterval(() => {
            // Simulate progress updates
            const progressBar = document.querySelector('.progress-animation[style*="width: 65%"]');
            if (progressBar) {
                let width = parseInt(progressBar.style.width);
                if (width < 100) {
                    width += Math.random() * 5;
                    progressBar.style.width = Math.min(width, 100) + '%';
                }
            }
            
            // Update time display
            const timeDisplays = document.querySelectorAll('.text-xs.font-bold.text-blue-600');
            timeDisplays.forEach(display => {
                if (display.textContent.includes(':')) {
                    const [hours, minutes] = display.textContent.split(':');
                    let newMinutes = parseInt(minutes) + 1;
                    if (newMinutes >= 60) {
                        display.textContent = `${parseInt(hours) + 1}:00`;
                    } else {
                        display.textContent = `${hours}:${newMinutes.toString().padStart(2, '0')}`;
                    }
                }
            });
        }, 5000);
    }
    
    // Start updates after a delay
    setTimeout(startRealTimeUpdates, 3000);
    @endif
    @endisset
</script>
@endsection