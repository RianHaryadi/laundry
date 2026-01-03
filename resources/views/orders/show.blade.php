@extends('layouts.app')

@section('title', 'Order Details - Rizki Laundry')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('orders') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Orders
            </a>
        </div>

        <!-- Page Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Order {{ $order->formatted_id }}</h1>
                    <p class="text-gray-600">
                        <i class="far fa-calendar mr-2"></i>
                        {{ $order->created_at->format('l, d F Y - H:i') }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="space-y-2">
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                            @if($order->status == 'completed') bg-green-100 text-green-700
                            @elseif($order->status == 'processing') bg-blue-100 text-blue-700
                            @elseif($order->status == 'ready') bg-purple-100 text-purple-700
                            @elseif($order->status == 'confirmed') bg-cyan-100 text-cyan-700
                            @elseif($order->status == 'cancelled') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700
                            @endif">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ ucfirst($order->status) }}
                        </span>
                        @if($order->payment_status == 'paid')
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                            <i class="fas fa-check-circle mr-1"></i>Payment Completed
                        </span>
                        @else
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">
                            <i class="fas fa-clock mr-1"></i>Payment Pending
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-blue-600 font-medium mb-1">Service</p>
                    <p class="text-lg font-bold text-gray-900">{{ $order->service->name ?? 'N/A' }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-purple-600 font-medium mb-1">Weight</p>
                    <p class="text-lg font-bold text-gray-900">{{ $order->total_weight }} kg</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-green-600 font-medium mb-1">Speed</p>
                    <p class="text-lg font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->service_speed)) }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <p class="text-sm text-orange-600 font-medium mb-1">Delivery</p>
                    <p class="text-lg font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Details -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Order Items -->
                @if($order->orderItems && $order->orderItems->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-box text-blue-600 mr-3"></i>
                        Order Items
                    </h2>
                    <div class="space-y-3">
                        @foreach($order->orderItems as $item)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-tshirt text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $item->item_name ?? 'Laundry Item' }}</p>
                                    <p class="text-sm text-gray-500">Qty: {{ $item->quantity ?? 1 }}</p>
                                </div>
                            </div>
                            <p class="font-bold text-gray-900">
                                Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Tracking Information -->
                @if(in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']) && $order->trackings->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-map-marker-alt text-blue-600 mr-3"></i>
                        Tracking History
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($order->trackings->sortByDesc('created_at') as $tracking)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                @if($tracking->status == 'delivered') bg-green-100
                                @elseif($tracking->status == 'in_transit') bg-blue-100
                                @elseif($tracking->status == 'picked_up') bg-purple-100
                                @else bg-gray-100
                                @endif">
                                <i class="fas 
                                    @if($tracking->status == 'delivered') fa-check-circle text-green-600
                                    @elseif($tracking->status == 'in_transit') fa-truck text-blue-600
                                    @elseif($tracking->status == 'picked_up') fa-box text-purple-600
                                    @else fa-clock text-gray-600
                                    @endif"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $tracking->status)) }}</p>
                                    <p class="text-sm text-gray-500">{{ $tracking->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <p class="text-sm text-gray-600">{{ $tracking->notes }}</p>
                                @if($tracking->courier)
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-user-circle mr-1"></i>
                                    Courier: {{ $tracking->courier->name }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if(in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']))
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route('tracking') }}?order={{ $order->id }}" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition text-center inline-block">
                            <i class="fas fa-route mr-2"></i>
                            View Live Tracking
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Delivery Information -->
                @if($order->pickup_time || $order->delivery_time)
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-truck text-blue-600 mr-3"></i>
                        Delivery Schedule
                    </h2>
                    
                    <div class="space-y-4">
                        @if($order->pickup_time)
                        <div class="flex items-center bg-blue-50 rounded-lg p-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-check text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-600">Pickup Time</p>
                                <p class="font-bold text-gray-900">{{ $order->pickup_time->format('l, d F Y - H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($order->delivery_time)
                        <div class="flex items-center bg-green-50 rounded-lg p-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-alt text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-green-600">Delivery Time</p>
                                <p class="font-bold text-gray-900">{{ $order->delivery_time->format('l, d F Y - H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Additional Information -->
                @if($order->notes || $order->outlet)
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        Additional Information
                    </h2>
                    
                    <div class="space-y-4">
                        @if($order->outlet)
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Outlet</p>
                            <p class="text-gray-900">{{ $order->outlet->name }}</p>
                            @if($order->outlet->address)
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $order->outlet->address }}
                            </p>
                            @endif
                        </div>
                        @endif

                        @if($order->notes)
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Notes</p>
                            <p class="text-gray-900 bg-gray-50 rounded-lg p-3">{{ $order->notes }}</p>
                        </div>
                        @endif

                        @if($order->courier)
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Assigned Courier</p>
                            <div class="flex items-center bg-blue-50 rounded-lg p-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold mr-3">
                                    {{ substr($order->courier->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $order->courier->name }}</p>
                                    @if($order->courier->phone)
                                    <p class="text-sm text-gray-600">{{ $order->courier->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            <!-- Right Column - Summary -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Price Summary -->
                <div class="bg-white rounded-2xl shadow-lg p-8 sticky top-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Price Summary</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-600">Base Price</span>
                            <span class="font-semibold text-gray-900">{{ $order->formatted_total_price }}</span>
                        </div>

                        @if($order->discount_amount > 0)
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-600">
                                Discount
                                @if($order->coupon_code)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded ml-1">{{ $order->coupon_code }}</span>
                                @endif
                            </span>
                            <span class="font-semibold text-green-600">-{{ $order->formatted_discount }}</span>
                        </div>
                        @endif

                        @if($order->pickup_delivery_fee > 0)
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-600">Pickup/Delivery Fee</span>
                            <span class="font-semibold text-gray-900">{{ $order->formatted_pickup_fee }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between py-4 border-t-2 border-gray-200">
                            <span class="text-lg font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-blue-600">{{ $order->formatted_final_price }}</span>
                        </div>

                        @if($order->payment_status == 'paid')
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                            <p class="text-green-700 font-semibold">Payment Completed</p>
                            @if($order->latestSuccessfulPayment)
                            <p class="text-xs text-green-600 mt-1">
                                Paid via {{ ucfirst($order->latestSuccessfulPayment->gateway) }}
                            </p>
                            @endif
                        </div>
                        @else
                        <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:shadow-lg transform hover:-translate-y-0.5 transition">
                            <i class="fas fa-credit-card mr-2"></i>
                            Pay Now
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Member Benefits -->
                @if($customer->isMember())
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl shadow-lg p-6 border border-yellow-200">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-crown text-yellow-600 mr-2"></i>
                        Member Benefits
                    </h3>
                    
                    <div class="space-y-3 text-sm">
                        @if($order->discount_amount > 0 && $order->discount_type == 'membership')
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Member discount applied</span>
                        </div>
                        @endif
                        
                        @if($order->pickup_delivery_fee == 0 && in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']))
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Free pickup/delivery</span>
                        </div>
                        @endif

                        @if($order->coupon_earned)
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-gift mr-2"></i>
                            <span>Reward point earned!</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($order->status == 'completed')
                        <button class="w-full bg-green-50 text-green-700 py-3 px-4 rounded-lg font-semibold hover:bg-green-100 transition">
                            <i class="fas fa-redo mr-2"></i>
                            Order Again
                        </button>
                        @endif
                        
                        <a href="{{ route('orders') }}" class="block w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition text-center">
                            <i class="fas fa-list mr-2"></i>
                            View All Orders
                        </a>

                        @if($order->canBeCancelled())
                        <button onclick="if(confirm('Are you sure you want to cancel this order?')) { /* Cancel logic */ }" class="w-full bg-red-50 text-red-700 py-3 px-4 rounded-lg font-semibold hover:bg-red-100 transition">
                            <i class="fas fa-times-circle mr-2"></i>
                            Cancel Order
                        </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection