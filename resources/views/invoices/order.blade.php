<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
        @page { margin: 1.5cm; }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold mb-2">INVOICE</h1>
                    <p class="text-blue-100">Laundry Management System</p>
                </div>
                <div class="text-right">
                    <div class="bg-white text-blue-800 px-4 py-2 rounded-lg font-bold text-xl">
                        #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                    </div>
                    <p class="text-blue-100 mt-2 text-sm">
                        {{ $order->created_at->format('d F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons (No Print) -->
        <div class="p-6 bg-gray-50 border-b no-print flex gap-3 justify-end">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Invoice
            </button>
            <a href="{{ route('orders.invoice.download', $order) }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </a>
            <a href="{{ url()->previous() }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>

        <!-- Invoice Content -->
        <div class="p-8">
            <!-- Customer & Outlet Info -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <!-- Bill To -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Bill To:</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="font-bold text-lg text-gray-800 mb-1">
                            @if($order->customer_type === 'member' && $order->customer)
                                {{ $order->customer->name }}
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">⭐ Member</span>
                            @else
                                {{ $order->guest_name ?? 'Guest Customer' }}
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded ml-2">🚶 Guest</span>
                            @endif
                        </p>
                        <p class="text-gray-600 text-sm">
                            📞 {{ $order->customer_type === 'member' ? $order->customer?->phone : $order->guest_phone }}
                        </p>
                        @if($order->customer_type === 'member' && $order->customer?->address)
                            <p class="text-gray-600 text-sm mt-1">
                                📍 {{ $order->customer->address }}
                            </p>
                        @elseif($order->guest_address)
                            <p class="text-gray-600 text-sm mt-1">
                                📍 {{ $order->guest_address }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Outlet Info -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Outlet:</h3>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="font-bold text-lg text-blue-800 mb-1">{{ $order->outlet->name }}</p>
                        <p class="text-gray-600 text-sm">📍 {{ $order->outlet->address ?? '-' }}</p>
                        @if($order->outlet->phone)
                            <p class="text-gray-600 text-sm">📞 {{ $order->outlet->phone }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="mb-8">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Order Details:</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="font-semibold text-blue-600 capitalize">{{ $order->status }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Service Speed</p>
                        <p class="font-semibold capitalize">{{ str_replace('_', ' ', $order->service_speed) }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Delivery Method</p>
                        <p class="font-semibold capitalize">{{ str_replace('_', ' ', $order->delivery_method) }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Payment</p>
                        <p class="font-semibold text-green-600 capitalize">{{ $order->payment_status }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-8">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th class="text-left p-3 text-sm font-semibold text-gray-700">Service</th>
                            <th class="text-center p-3 text-sm font-semibold text-gray-700">Qty/Weight</th>
                            <th class="text-right p-3 text-sm font-semibold text-gray-700">Unit Price</th>
                            <th class="text-right p-3 text-sm font-semibold text-gray-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3">
                                <p class="font-medium text-gray-800">{{ $item->service->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->service->description ?? '' }}</p>
                            </td>
                            <td class="p-3 text-center text-gray-700">
                                @if($item->service->pricing_type === 'kg')
                                    {{ number_format($item->weight, 1) }} KG
                                @else
                                    {{ $item->quantity }} pcs
                                @endif
                            </td>
                            <td class="p-3 text-right text-gray-700">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-semibold text-gray-800">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="flex justify-end">
                <div class="w-80">
                    <div class="bg-gray-50 p-6 rounded-lg space-y-3">
                        <div class="flex justify-between text-gray-700">
                            <span>Base Price:</span>
                            <span class="font-medium">Rp {{ number_format($order->base_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                        
                        @if($order->service_speed !== 'regular' || $order->delivery_method !== 'walk_in')
                        <div class="flex justify-between text-gray-700">
                            <span>Surcharges:</span>
                            <span class="font-medium text-orange-600">
                                + Rp {{ number_format($order->total_price - ($order->base_price ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                        @endif

                        <div class="flex justify-between text-gray-700 pt-3 border-t border-gray-300">
                            <span>Subtotal:</span>
                            <span class="font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>

                        @if($order->discount_amount > 0 || $order->is_free_service)
                        <div class="flex justify-between text-green-600">
                            <span>
                                Discount
                                @if($order->is_free_service)
                                    (Free Service 🎁)
                                @elseif($order->coupon)
                                    ({{ $order->coupon->code }})
                                @endif
                            </span>
                            <span class="font-semibold">
                                @if($order->is_free_service)
                                    - 100%
                                @else
                                    - Rp {{ number_format($order->discount_amount, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        @endif

                        <div class="flex justify-between text-xl font-bold text-gray-900 pt-3 border-t-2 border-gray-400">
                            <span>Total:</span>
                            <span class="text-blue-600">
                                Rp {{ number_format($order->final_price, 0, ',', '.') }}
                            </span>
                        </div>

                        @if($order->is_free_service)
                        <div class="bg-green-100 text-green-800 p-3 rounded text-center text-sm font-semibold">
                            🎉 FREE SERVICE REWARD APPLIED
                        </div>
                        @endif
                    </div>

                    <!-- Payment Info -->
                    <div class="mt-4 bg-blue-50 p-4 rounded-lg">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-semibold text-blue-800 capitalize">
                                {{ str_replace('_', ' ', $order->payment_gateway) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm mt-2">
                            <span class="text-gray-600">Payment Status:</span>
                            <span class="font-semibold capitalize {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-orange-600' }}">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
            <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <h4 class="font-semibold text-gray-800 mb-2">📝 Notes:</h4>
                <p class="text-gray-700 text-sm">{{ $order->notes }}</p>
            </div>
            @endif

            <!-- Footer -->
            <div class="mt-12 pt-6 border-t border-gray-300 text-center text-gray-500 text-sm">
                <p class="mb-2">Thank you for your business!</p>
                <p>Invoice generated on {{ now()->format('d F Y, H:i') }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto print if ?print=1 in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>