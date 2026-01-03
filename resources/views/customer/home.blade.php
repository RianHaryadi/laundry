@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        @if($customer)
        <h1 class="text-3xl font-bold mb-2">Welcome back, {{ $customer->name }}! 👋</h1>
        @else
        <h1 class="text-3xl font-bold mb-2">Welcome to Rizki Laundry! 👋</h1>
        @endif
        <p class="text-gray-600">Ready to get your laundry done?</p>
    </div>
    
    <!-- Active Orders -->
    @if($customer && $activeOrders && count($activeOrders) > 0)
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Your Active Orders</h2>
        <div class="space-y-3">
            @foreach($activeOrders as $order)
            <div class="border rounded-lg p-4 hover:bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="font-semibold">Order #{{ $order->id }}</h3>
                    <p class="text-sm text-gray-600">{{ $order->service->name ?? 'N/A' }} - {{ $order->outlet->name ?? 'N/A' }}</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded-full text-sm
                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status == 'processing') bg-indigo-100 text-indigo-800
                        @elseif($order->status == 'ready') bg-green-100 text-green-800
                        @else bg-purple-100 text-purple-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <p class="text-sm text-gray-600 mt-1">Rp {{ number_format($order->final_price, 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('profile.index') }}" class="text-blue-500 hover:underline">View All Orders</a>
        </div>
    </div>
    @endif
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @if($customer)
        <a href="{{ route('booking') }}" class="bg-blue-500 text-white rounded-lg p-6 hover:bg-blue-600 transition">
        @else
        <a href="{{ route('login') }}" class="bg-blue-500 text-white rounded-lg p-6 hover:bg-blue-600 transition">
        @endif
            <div class="flex items-center">
                <div class="bg-white bg-opacity-20 rounded-full p-4 mr-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold">Create New Order</h3>
                    <p class="text-blue-100">Start a new laundry order</p>
                </div>
            </div>
        </a>
        
        @if($customer)
        <a href="{{ route('profile.index') }}" class="bg-green-500 text-white rounded-lg p-6 hover:bg-green-600 transition">
        @else
        <a href="{{ route('login') }}" class="bg-green-500 text-white rounded-lg p-6 hover:bg-green-600 transition">
        @endif
            <div class="flex items-center">
                <div class="bg-white bg-opacity-20 rounded-full p-4 mr-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold">{{ $customer ? 'My Profile' : 'Login' }}</h3>
                    <p class="text-green-100">{{ $customer ? 'View orders & update info' : 'Access your account' }}</p>
                </div>
            </div>
        </a>
    </div>
    
    <!-- Available Services -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Our Services</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($services as $service)
            <div class="border rounded-lg p-4 hover:shadow-md transition">
                <h3 class="font-semibold text-lg mb-2">{{ $service->name }}</h3>
                @if($service->description)
                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($service->description, 80) }}</p>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-blue-600 font-semibold">
                        Rp {{ number_format($service->base_price, 0, ',', '.') }}/kg
                    </span>
                    <span class="text-sm text-gray-500">{{ $service->estimated_duration }} hours</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-8 text-gray-500">
                No services available at the moment
            </div>
            @endforelse
        </div>
    </div>
    
    <!-- Available Outlets -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Our Outlets</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($outlets as $outlet)
            <div class="border rounded-lg p-4 hover:shadow-md transition">
                <h3 class="font-semibold text-lg mb-2">{{ $outlet->name }}</h3>
                @if($outlet->address)
                <p class="text-sm text-gray-600 mb-2">📍 {{ $outlet->address }}</p>
                @endif
                @if($outlet->phone)
                <p class="text-sm text-gray-600">📞 {{ $outlet->phone }}</p>
                @endif
            </div>
            @empty
            <div class="col-span-2 text-center py-8 text-gray-500">
                No outlets available at the moment
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection