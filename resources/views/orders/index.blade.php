@extends('layouts.app')

@section('title', 'My Orders - Rizki Laundry')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">My Orders</h1>
                    <p class="text-gray-600">View and manage all your laundry orders</p>
                </div>
                <a href="{{ route('booking') }}" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transform hover:-translate-y-0.5 transition">
                    <i class="fas fa-plus mr-2"></i>New Order
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
            <a href="{{ route('orders', ['status' => 'all']) }}" class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition {{ (!$status || $status == 'all') ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-list text-xl text-gray-500"></i>
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['all'] }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium">All</p>
            </a>
            
            <a href="{{ route('orders', ['status' => 'pending']) }}" class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition {{ $status == 'pending' ? 'ring-2 ring-yellow-500' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-clock text-xl text-yellow-500"></i>
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['pending'] }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium">Pending</p>
            </a>
            
            <a href="{{ route('orders', ['status' => 'confirmed']) }}" class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition {{ $status == 'confirmed' ? 'ring-2 ring-cyan-500' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-check text-xl text-cyan-500"></i>
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['confirmed'] }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium">Confirmed</p>
            </a>
            
            <a href="{{ route('orders', ['status' => 'processing']) }}" class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition {{ $status == 'processing' ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-spinner text-xl text-blue-500"></i>
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['processing'] }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium">Processing</p>
            </a>
            
            <a href="{{ route('orders', ['status' => 'ready']) }}" class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition {{ $status == 'ready' ? 'ring-2 ring-purple-500' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-check-circle text-xl text-purple-500"></i>
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['ready'] }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium">Ready</p>
            </a>
            
            <a href="{{ route('orders', ['status' => 'completed']) }}" class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition {{ $status == 'completed' ? 'ring-2 ring-green-500' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-check-double text-xl text-green-500"></i>
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['completed'] }}</span>
                </div>
                <p class="text-sm text-gray-600 font-medium">Completed</p>
            </a>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form action="{{ route('orders') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by order ID or service..." class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                @if($search || ($status && $status !== 'all'))
                <a href="{{ route('orders') }}" class="bg-gray-100 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-200 transition text-center">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
                @endif
            </form>
        </div>

        <!-- Orders List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $order->formatted_id }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="far fa-calendar mr-1"></i>
                                    {{ $order->created_at->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <i class="fas fa-tshirt text-blue-500 mr-2"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-700">{{ $order->service->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->outlet->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm space-y-1">
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-weight-hanging text-gray-400 mr-1 text-xs"></i>
                                        <span class="font-semibold">{{ $order->total_weight }} kg</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-tachometer-alt text-gray-400 mr-1 text-xs"></i>
                                        <span class="text-xs">{{ ucfirst(str_replace('_', ' ', $order->service_speed)) }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-truck text-gray-400 mr-1 text-xs"></i>
                                        <span class="text-xs">{{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-base font-bold text-blue-600">{{ $order->formatted_final_price }}</div>
                                @if($order->discount_amount > 0)
                                <div class="text-xs text-gray-500 line-through">{{ $order->formatted_total_price }}</div>
                                <div class="text-xs text-green-600 font-semibold">
                                    <i class="fas fa-tag mr-1"></i>-{{ $order->formatted_discount }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block
                                        @if($order->status == 'completed') bg-green-100 text-green-700
                                        @elseif($order->status == 'processing') bg-blue-100 text-blue-700
                                        @elseif($order->status == 'ready') bg-purple-100 text-purple-700
                                        @elseif($order->status == 'confirmed') bg-cyan-100 text-cyan-700
                                        @elseif($order->status == 'cancelled') bg-red-100 text-red-700
                                        @else bg-yellow-100 text-yellow-700
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    @if($order->payment_status == 'paid')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700 inline-block">
                                        <i class="fas fa-check-circle mr-1"></i>Paid
                                    </span>
                                    @elseif($order->payment_status == 'pending')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-700 inline-block">
                                        <i class="fas fa-clock mr-1"></i>Unpaid
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-700">
                                    {{ $order->created_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->created_at->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-2">
                                    @if(in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']))
                                    <a href="{{ route('tracking') }}?order={{ $order->id }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center" title="Track Order">
                                        <i class="fas fa-map-marker-alt mr-1"></i>Track
                                    </a>
                                    @endif
                                    <a href="{{ route('orders.show', $order->id) }}" class="text-gray-600 hover:text-gray-800 font-medium text-sm flex items-center" title="View Details">
                                        <i class="fas fa-eye mr-1"></i>Details
                                    </a>
                                    @if($order->status == 'completed')
                                    <button class="text-green-600 hover:text-green-800 font-medium text-sm text-left flex items-center" title="Order Again">
                                        <i class="fas fa-redo mr-1"></i>Reorder
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
            
            @else
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Orders Found</h3>
                <p class="text-gray-600 mb-6">
                    @if($search)
                        No orders match your search criteria.
                    @else
                        You haven't placed any orders yet.
                    @endif
                </p>
                <a href="{{ $search ? route('orders') : route('booking') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    @if($search)
                        <i class="fas fa-times mr-2"></i>Clear Search
                    @else
                        <i class="fas fa-plus mr-2"></i>Create New Order
                    @endif
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection