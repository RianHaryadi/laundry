@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">My Profile</h1>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Profile Info -->
        <div class="lg:col-span-1">
            <!-- User Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="text-center mb-4">
                    <div class="w-24 h-24 bg-blue-500 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-semibold mt-4">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    @if($customer && $customer->membership_type)
                    <span class="inline-block mt-2 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        {{ ucfirst($customer->membership_type) }} Member
                    </span>
                    @endif
                </div>
            </div>
            
            <!-- Statistics Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Orders</span>
                        <span class="font-semibold">{{ $totalOrders }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Completed</span>
                        <span class="font-semibold text-green-600">{{ $completedOrders }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Orders</span>
                        <span class="font-semibold text-blue-600">{{ $activeOrders }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Spent</span>
                        <span class="font-semibold">Rp {{ number_format($totalSpent, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Reward Points</span>
                        <span class="font-semibold text-purple-600">{{ $rewardPoints }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Available Coupons</span>
                        <span class="font-semibold text-orange-600">{{ $availableCoupons }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Profile Form & Orders -->
        <div class="lg:col-span-2">
            <!-- Edit Profile Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Edit Profile</h3>
                
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                                   required>
                            @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" 
                                   required>
                            @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? $customer->phone ?? '') }}" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Address</label>
                            <input type="text" name="address" value="{{ old('address', $user->address ?? $customer->address ?? '') }}" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <hr class="my-6">
                    
                    <h4 class="text-lg font-semibold mb-4">Change Password (Optional)</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Current Password</label>
                            <input type="password" name="current_password" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('current_password') border-red-500 @enderror">
                            @error('current_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">New Password</label>
                            <input type="password" name="new_password" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('new_password') border-red-500 @enderror">
                            @error('new_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Recent Orders</h3>
                
                @if($recentOrders->count() > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                    <div class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-semibold">Order #{{ $order->id }}</h4>
                                <p class="text-sm text-gray-600">{{ $order->service->name ?? 'N/A' }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm
                                @if($order->status == 'completed') bg-green-100 text-green-800
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                            <div>Date: {{ $order->created_at->format('d M Y') }}</div>
                            <div>Price: Rp {{ number_format($order->final_price, 0, ',', '.') }}</div>
                            <div>Outlet: {{ $order->outlet->name ?? 'N/A' }}</div>
                            <div>Payment: {{ ucfirst($order->payment_status) }}</div>
                        </div>
                        @if($order->latestTracking)
                        <div class="mt-2 text-sm text-blue-600">
                            Latest: {{ $order->latestTracking->status }} - {{ $order->latestTracking->notes }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                
                @if($totalOrders > 10)
                <div class="mt-4 text-center">
                    <a href="{{ url('/orders') }}" class="text-blue-500 hover:underline">
                        View All Orders ({{ $totalOrders }})
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-8 text-gray-500">
                    <p>No orders yet</p>
                    <a href="{{ url('/') }}" class="text-blue-500 hover:underline mt-2 inline-block">
                        Place your first order
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection