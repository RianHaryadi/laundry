@extends('layouts.app')

@section('title', 'Tracking - LaundryKu')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="relative inline-block mb-8">
                <div class="absolute -inset-4 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full blur-xl opacity-30 animate-pulse"></div>
                <div class="relative w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                    <i class="fas fa-shipping-fast text-white text-4xl animate-bounce"></i>
                </div>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent">
                Lacak Pesanan Anda
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Pantau perjalanan laundry Anda secara real-time
            </p>
        </div>

        <!-- Search Section -->
        <div class="max-w-4xl mx-auto mb-16">
            <div class="relative">
                <div class="absolute -inset-8 bg-gradient-to-r from-blue-400/20 to-indigo-500/20 rounded-3xl blur-3xl"></div>
                
                <div class="relative bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 border border-white/20">
                    @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                    @endif

                    <div class="flex items-center mb-6">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-search text-white text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Cari Pesanan</h2>
                            <p class="text-gray-600">Masukkan nomor order atau nomor telepon</p>
                        </div>
                    </div>

                    <form action="{{ route('tracking.search') }}" method="POST" id="tracking-form">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Order Number Input -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-3">Nomor Order</label>
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                                    <div class="relative">
                                        <input type="text" name="tracking_code" 
                                               class="w-full pl-12 pr-4 py-4 bg-white rounded-xl border-0 focus:ring-4 focus:ring-blue-100 focus:outline-none text-lg font-medium shadow-inner"
                                               placeholder="#000001"
                                               value="{{ old('tracking_code') }}"
                                               id="tracking-input">
                                        <div class="absolute left-4 top-4">
                                            <i class="fas fa-hashtag text-blue-500 text-xl"></i>
                                        </div>
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button type="submit" 
                                class="w-full group relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-4 px-8 rounded-xl font-bold text-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                            <span class="relative flex items-center justify-center">
                                <i class="fas fa-satellite-dish mr-3"></i>
                                Lacak Sekarang
                                <i class="fas fa-arrow-right ml-3 group-hover:translate-x-2 transition-transform duration-300"></i>
                            </span>
                        </button>
                    </form>

                    <!-- Recent Searches -->
                    @if(isset($recentSearches) && count($recentSearches) > 0)
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-history mr-2 text-blue-500"></i>
                            Pencarian Terakhir
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($recentSearches as $search)
                            <button onclick="fillTrackingCode('{{ $search->formatted_id }}')"
                                    class="px-4 py-2 bg-gray-100 hover:bg-blue-100 rounded-lg text-gray-700 hover:text-blue-700 transition duration-300 flex items-center">
                                <span class="font-mono">{{ $search->formatted_id }}</span>
                                <span class="ml-2 text-xs px-2 py-1 rounded-full bg-{{ $search->getStatusColor() }}-100 text-{{ $search->getStatusColor() }}-800">
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
        <div class="animate-fade-in">
            <!-- Status Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-blue-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-weight text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold text-blue-600">{{ $order->total_weight }}</span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Berat (kg)</h3>
                    <p class="text-gray-600 text-sm">Total cucian</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-green-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold text-green-600">
                            {{ $order->service_speed === 'same_day' ? '1' : ($order->service_speed === 'express' ? '2' : '3') }}
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Hari Proses</h3>
                    <p class="text-gray-600 text-sm">{{ ucfirst($order->service_speed) }}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-purple-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold text-purple-600">
                            {{ number_format($order->final_price, 0, ',', '.') }}
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Total Biaya</h3>
                    <p class="text-gray-600 text-sm">Rp</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-orange-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-box text-orange-600 text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold text-orange-600">
                            {{ $order->service->name ?? 'Regular' }}
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Layanan</h3>
                    <p class="text-gray-600 text-sm">Jenis cucian</p>
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
                                        <h2 class="text-2xl font-bold text-white">Order {{ $order->formatted_id }}</h2>
                                        <p class="text-blue-100">{{ $order->created_at->isoFormat('dddd, D MMMM YYYY') }}</p>
                                    </div>
                                    <div class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full">
                                        <span class="text-white font-bold text-lg">{{ strtoupper($order->status) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mt-6">
                                    <div class="flex justify-between text-sm text-blue-100 mb-2">
                                        <span>Progress</span>
                                        <span>{{ $order->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full h-3 bg-white/20 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full transition-all duration-500"
                                             style="width: {{ $order->progress_percentage }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Timeline -->
                        <div class="p-8">
                            <div class="relative">
                                <div class="absolute left-8 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-indigo-500 to-purple-500"></div>
                                
                                @php
                                    $steps = [
                                        [
                                            'status' => 'received',
                                            'icon' => 'fas fa-inbox',
                                            'title' => 'Pesanan Diterima',
                                            'description' => 'Pesanan berhasil diregistrasi',
                                            'color' => 'from-green-400 to-emerald-500',
                                            'active' => in_array($order->status, ['pending', 'confirmed', 'processing', 'ready', 'picked_up', 'in_delivery', 'completed'])
                                        ],
                                        [
                                            'status' => 'processing',
                                            'icon' => 'fas fa-washing-machine',
                                            'title' => 'Sedang Diproses',
                                            'description' => 'Cucian sedang dalam proses',
                                            'color' => in_array($order->status, ['processing', 'ready', 'picked_up', 'in_delivery', 'completed']) ? 'from-blue-400 to-indigo-500' : 'from-gray-300 to-gray-400',
                                            'active' => in_array($order->status, ['processing', 'ready', 'picked_up', 'in_delivery', 'completed'])
                                        ],
                                        [
                                            'status' => 'quality_check',
                                            'icon' => 'fas fa-search',
                                            'title' => 'Quality Check',
                                            'description' => 'Pengecekan kualitas',
                                            'color' => in_array($order->status, ['ready', 'picked_up', 'in_delivery', 'completed']) ? 'from-yellow-400 to-orange-500' : 'from-gray-300 to-gray-400',
                                            'active' => in_array($order->status, ['ready', 'picked_up', 'in_delivery', 'completed'])
                                        ],
                                        [
                                            'status' => 'delivery',
                                            'icon' => 'fas fa-truck',
                                            'title' => 'Pengantaran',
                                            'description' => 'Pesanan dalam perjalanan',
                                            'color' => in_array($order->status, ['in_delivery', 'completed']) ? 'from-purple-400 to-pink-500' : 'from-gray-300 to-gray-400',
                                            'active' => in_array($order->status, ['in_delivery', 'completed'])
                                        ],
                                        [
                                            'status' => 'completed',
                                            'icon' => 'fas fa-check-circle',
                                            'title' => 'Selesai',
                                            'description' => 'Pesanan telah selesai',
                                            'color' => $order->status === 'completed' ? 'from-green-400 to-emerald-500' : 'from-gray-300 to-gray-400',
                                            'active' => $order->status === 'completed'
                                        ]
                                    ];
                                @endphp

                                <div class="space-y-12">
                                    @foreach($steps as $step)
                                    <div class="flex items-start group">
                                        <div class="flex-shrink-0 relative z-10">
                                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $step['color'] }} 
                                                flex items-center justify-center text-white shadow-lg transform group-hover:scale-110 
                                                transition duration-300 {{ $step['active'] ? 'ring-4 ring-opacity-30 ring-white' : '' }}">
                                                <i class="{{ $step['icon'] }} text-xl"></i>
                                            </div>
                                        </div>
                                        <div class="ml-8 bg-gray-50 group-hover:bg-white rounded-xl p-5 flex-grow 
                                            transform group-hover:-translate-y-1 transition duration-300 shadow-sm group-hover:shadow-md">
                                            <div class="flex justify-between items-center mb-2">
                                                <h4 class="font-bold text-lg text-gray-800">{{ $step['title'] }}</h4>
                                                @if($step['active'])
                                                <span class="px-3 py-1 bg-gradient-to-r from-green-100 to-emerald-100 
                                                    text-green-800 rounded-full text-xs font-bold">
                                                    ✓
                                                </span>
                                                @endif
                                            </div>
                                            <p class="text-gray-600">{{ $step['description'] }}</p>
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
                                <span class="text-gray-600">Nomor Order</span>
                                <span class="font-mono font-bold text-blue-600">{{ $order->formatted_id }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Layanan</span>
                                <span class="font-semibold">{{ $order->service->name ?? 'Regular' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Outlet</span>
                                <span class="font-semibold">{{ $order->outlet->name ?? 'Main' }}</span>
                            </div>
                            @if($order->delivery_time)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Estimasi Selesai</span>
                                <span class="font-semibold text-green-600">
                                    {{ $order->delivery_time->isoFormat('DD/MM/YY HH:mm') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($order->courier && in_array($order->status, ['picked_up', 'in_delivery']))
                    <!-- Courier Info -->
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl p-6 text-white">
                        <h3 class="font-bold text-xl mb-4 flex items-center">
                            <i class="fas fa-user-tie mr-3"></i>
                            Kurir
                        </h3>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="font-bold">{{ $order->courier->name }}</p>
                                    <p class="text-sm text-green-100">{{ $order->courier->phone }}</p>
                                </div>
                            </div>
                            @if($order->courier->phone)
                            <a href="tel:{{ $order->courier->phone }}"
                                    class="bg-white text-green-600 px-4 py-2 rounded-lg font-bold hover:bg-gray-100 transition duration-300">
                                <i class="fas fa-phone mr-1"></i> Hubungi
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endisset
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }
</style>

<script>
    function fillTrackingCode(code) {
        document.getElementById('tracking-input').value = code;
        document.getElementById('tracking-form').submit();
    }
</script>
@endsection