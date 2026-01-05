@extends('layouts.app')

@section('content')
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4 text-gray-800">Semua Layanan Kami</h1>
        <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">Temukan berbagai solusi laundry profesional untuk kebutuhan harian Anda.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach($services as $service)
                @php
                    // Mendefinisikan warna agar tidak error
                    $colors = ['blue', 'purple', 'orange', 'indigo', 'pink', 'teal'];
                    $color = $colors[$loop->index % count($colors)];
                @endphp
                
                <div class="bg-white border-2 border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-14 h-14 rounded-2xl bg-{{ $color }}-100 flex items-center justify-center mb-6">
                        <i class="fas {{ $service->pricing_type == 'kg' ? 'fa-weight-hanging' : 'fa-tshirt' }} text-{{ $color }}-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-2 text-gray-800">{{ $service->name }}</h3>
                    <p class="text-gray-500 text-sm mb-6 line-clamp-3">{{ $service->description }}</p>
                    
                    <div class="bg-gray-50 rounded-2xl p-4 mb-6">
                        <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Harga Mulai</div>
                        <div class="text-2xl font-bold text-{{ $color }}-600">
                            Rp {{ number_format($service->price_per_kg ?? $service->price_per_unit, 0, ',', '.') }}
                            <span class="text-sm font-normal text-gray-400">/{{ $service->pricing_type }}</span>
                        </div>
                    </div>

                    <a href="{{ route('booking', ['service_id' => $service->id]) }}" 
                       class="w-full inline-block text-center bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white py-3 rounded-xl font-semibold shadow-lg shadow-{{ $color }}-200 transition-all">
                        Pesan Sekarang
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection