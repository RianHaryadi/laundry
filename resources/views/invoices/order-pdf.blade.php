{{-- resources/views/invoices/order-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { padding: 20px; }
        
        /* Header */
        .header { background: #2563eb; color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header .subtitle { font-size: 14px; opacity: 0.9; }
        .invoice-number { background: white; color: #2563eb; padding: 8px 15px; display: inline-block; font-weight: bold; font-size: 18px; border-radius: 5px; float: right; }
        
        /* Info Boxes */
        .info-section { margin-bottom: 20px; }
        .info-box { background: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 10px; }
        .info-box h3 { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; font-weight: 600; }
        .info-box p { margin: 4px 0; }
        .info-box .name { font-size: 16px; font-weight: bold; color: #1f2937; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 9px; }
        .badge-member { background: #fef3c7; color: #92400e; }
        .badge-guest { background: #e5e7eb; color: #374151; }
        
        /* Grid */
        .grid { display: table; width: 100%; margin-bottom: 20px; }
        .grid-col { display: table-cell; width: 50%; padding-right: 10px; }
        .grid-col:last-child { padding-right: 0; padding-left: 10px; }
        
        .details-grid { display: table; width: 100%; margin-bottom: 20px; }
        .detail-item { display: table-cell; width: 25%; background: #f9fafb; padding: 10px; border-right: 1px solid #e5e7eb; }
        .detail-item:last-child { border-right: none; }
        .detail-item .label { font-size: 9px; color: #6b7280; display: block; margin-bottom: 4px; }
        .detail-item .value { font-weight: 600; font-size: 11px; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead { background: #f3f4f6; }
        th { padding: 10px; text-align: left; font-size: 11px; font-weight: 600; color: #374151; border-bottom: 2px solid #d1d5db; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Summary */
        .summary { float: right; width: 300px; background: #f9fafb; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-row.total { border-top: 2px solid #d1d5db; padding-top: 10px; margin-top: 10px; font-size: 16px; font-weight: bold; }
        .summary-row.discount { color: #059669; }
        
        /* Footer */
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #6b7280; }
        
        /* Notes */
        .notes { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0; }
        .notes h4 { font-size: 11px; margin-bottom: 5px; }
        .notes p { font-size: 10px; line-height: 1.5; }
        
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div style="float: left;">
                <h1>INVOICE</h1>
                <div class="subtitle">Laundry Management System</div>
            </div>
            <div class="invoice-number">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div style="clear: both;"></div>
            <div style="margin-top: 10px; font-size: 11px;">{{ $order->created_at->format('d F Y') }}</div>
        </div>

        <!-- Customer & Outlet Info -->
        <div class="grid">
            <div class="grid-col">
                <div class="info-box">
                    <h3>Bill To:</h3>
                    <p class="name">
                        @if($order->customer_type === 'member' && $order->customer)
                            {{ $order->customer->name }}
                            <span class="badge badge-member">⭐ Member</span>
                        @else
                            {{ $order->guest_name ?? 'Guest Customer' }}
                            <span class="badge badge-guest">🚶 Guest</span>
                        @endif
                    </p>
                    <p>📞 {{ $order->customer_type === 'member' ? $order->customer?->phone : $order->guest_phone }}</p>
                    @if($order->customer_type === 'member' && $order->customer?->address)
                        <p>📍 {{ $order->customer->address }}</p>
                    @elseif($order->guest_address)
                        <p>📍 {{ $order->guest_address }}</p>
                    @endif
                </div>
            </div>
            <div class="grid-col">
                <div class="info-box" style="background: #dbeafe;">
                    <h3>Outlet:</h3>
                    <p class="name" style="color: #1e40af;">{{ $order->outlet->name }}</p>
                    <p>📍 {{ $order->outlet->address ?? '-' }}</p>
                    @if($order->outlet->phone)
                        <p>📞 {{ $order->outlet->phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="details-grid">
            <div class="detail-item">
                <span class="label">Status</span>
                <span class="value" style="color: #2563eb;">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Service Speed</span>
                <span class="value">{{ ucfirst(str_replace('_', ' ', $order->service_speed)) }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Delivery</span>
                <span class="value">{{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Payment</span>
                <span class="value" style="color: #059669;">{{ ucfirst($order->payment_status) }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th class="text-center">Qty/Weight</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->service->name }}</strong><br>
                        <span style="font-size: 10px; color: #6b7280;">{{ $item->service->description ?? '' }}</span>
                    </td>
                    <td class="text-center">
                        @if($item->service->pricing_type === 'kg')
                            {{ number_format($item->weight, 1) }} KG
                        @else
                            {{ $item->quantity }} pcs
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>Base Price:</span>
                <span>Rp {{ number_format($order->base_price ?? 0, 0, ',', '.') }}</span>
            </div>
            
            @if($order->service_speed !== 'regular' || $order->delivery_method !== 'walk_in')
            <div class="summary-row" style="color: #ea580c;">
                <span>Surcharges:</span>
                <span>+ Rp {{ number_format($order->total_price - ($order->base_price ?? 0), 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="summary-row">
                <span>Subtotal:</span>
                <span><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></span>
            </div>

            @if($order->discount_amount > 0 || $order->is_free_service)
            <div class="summary-row discount">
                <span>
                    Discount
                    @if($order->is_free_service)
                        (Free 🎁)
                    @elseif($order->coupon)
                        ({{ $order->coupon->code }})
                    @endif
                </span>
                <span>
                    @if($order->is_free_service)
                        - 100%
                    @else
                        - Rp {{ number_format($order->discount_amount, 0, ',', '.') }}
                    @endif
                </span>
            </div>
            @endif

            <div class="summary-row total">
                <span>Total:</span>
                <span style="color: #2563eb;">Rp {{ number_format($order->final_price, 0, ',', '.') }}</span>
            </div>

            @if($order->is_free_service)
            <div style="background: #d1fae5; color: #065f46; padding: 8px; border-radius: 3px; text-align: center; margin-top: 10px; font-size: 10px; font-weight: 600;">
                🎉 FREE SERVICE REWARD
            </div>
            @endif

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 5px;">
                    <span style="color: #6b7280;">Payment Method:</span>
                    <strong style="color: #1f2937;">{{ ucfirst(str_replace('_', ' ', $order->payment_gateway)) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 10px;">
                    <span style="color: #6b7280;">Payment Status:</span>
                    <strong style="color: {{ $order->payment_status === 'paid' ? '#059669' : '#ea580c' }};">
                        {{ ucfirst($order->payment_status) }}
                    </strong>
                </div>
            </div>
        </div>

        <div style="clear: both;"></div>

        <!-- Notes -->
        @if($order->notes)
        <div class="notes">
            <h4>📝 Notes:</h4>
            <p>{{ $order->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>Invoice generated on {{ now()->format('d F Y, H:i') }}</p>
        </div>
    </div>
</body>
</html>0