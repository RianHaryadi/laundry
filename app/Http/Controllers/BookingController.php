<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Outlet;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return $this->create($request);
    }

    public function create(Request $request)
    {
        $selectedServiceId = $request->query('service_id');

        $services = Service::active()
            ->select('id', 'name', 'description', 'price_per_kg', 'price_per_unit', 'pricing_type', 'duration_hours')
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price_per_kg' => $service->price_per_kg,
                    'price_per_unit' => $service->price_per_unit,
                    'pricing_type' => $service->pricing_type,
                    'pricing_label' => $service->pricing_type_label ?? $service->pricing_type,
                    'formatted_price' => $service->formatted_price ?? number_format($service->price_per_kg ?? $service->price_per_unit, 0, ',', '.'),
                    'duration' => $service->formatted_duration ?? $service->duration_hours . ' jam',
                    'icon' => $this->getServiceIcon($service->name),
                    'color' => $this->getServiceColor($service->name),
                ];
            });

        $outlets = Outlet::all();
        $customer = Auth::guard('customer')->user();

        return view('booking', compact('services', 'outlets', 'customer', 'selectedServiceId'));
    }

    public function store(Request $request)
    {
        $rules = [
            'service_id' => 'required|exists:services,id',
            'outlet_id' => 'required|exists:outlets,id',
            'delivery_method' => 'required|in:walk_in,pickup,delivery,pickup_delivery',
            'service_speed' => 'required|in:regular,express,same_day',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|string',
            'address' => 'required|string|min:10',
            'notes' => 'nullable|string',
            'is_free_service' => 'nullable|boolean',
        ];

        if (!Auth::guard('customer')->check()) {
            $rules['name'] = 'required|string|max:255';
            $rules['phone'] = 'required|string|min:10|max:20';
            $rules['email'] = 'nullable|email';
        }

        $validated = $request->validate($rules);

        try {
            $outlet = Outlet::findOrFail($validated['outlet_id']);
            $service = Service::findOrFail($validated['service_id']);
            
            $courier = User::where('outlet_id', $outlet->id)
                ->where('role', 'courier')
                ->where('is_active', true)
                ->first();

            // Prepare order data
            $orderData = [
                'outlet_id' => $validated['outlet_id'],
                'delivery_method' => $validated['delivery_method'],
                'service_speed' => $validated['service_speed'],
                'courier_id' => $courier?->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_gateway' => 'cash',
                'pickup_time' => $validated['pickup_date'] . ' ' . explode('-', $validated['pickup_time'])[0] . ':00',
                'notes' => $validated['notes'] ?? null,
                'is_free_service' => $request->has('is_free_service'),
                'total_weight' => 0,
                'total_price' => 0,
                'discount_amount' => 0,
                'final_price' => 0,
                'base_price' => 0,
            ];

            // Customer data
            if (Auth::guard('customer')->check()) {
                $customer = Auth::guard('customer')->user();
                $orderData['customer_id'] = $customer->id;
                $orderData['customer_type'] = 'member';
                $orderData['guest_name'] = null;
                $orderData['guest_phone'] = null;
                $orderData['guest_address'] = $validated['address'];
            } else {
                $orderData['customer_id'] = null;
                $orderData['customer_type'] = 'guest';
                $orderData['guest_name'] = $validated['name'];
                $orderData['guest_phone'] = $validated['phone'];
                $orderData['guest_address'] = $validated['address'];
            }

            // Create order
            $order = Order::create($orderData);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $validated['service_id'],
                'pricing_type' => $service->pricing_type ?? 'kg',
                'price_per_kg' => $service->price_per_kg ?? 0,
                'price_per_unit' => $service->price_per_unit ?? 0,
                'quantity' => 0,
                'weight' => 0,
                'price' => 0,
                'subtotal' => 0,
            ]);

            // Create tracking
            $deliveryMethod = $validated['delivery_method'];
            if (in_array($deliveryMethod, ['pickup', 'delivery', 'pickup_delivery'])) {
                $this->createTrackingRecords($order, $deliveryMethod, $courier, $validated['address']);
            }

            // Generate booking details for notification
            $bookingDetails = $this->generateBookingDetails($order, $service, $outlet, $validated);

            Log::info('Booking created successfully', [
                'order_id' => $order->id,
                'customer_type' => $order->customer_type,
                'service' => $service->name,
            ]);

            // Redirect dengan session flash data untuk modal
            return redirect()->route('home')->with([
                'booking_success' => true,
                'booking_details' => $bookingDetails,
            ]);

        } catch (\Exception $e) {
            Log::error('Booking creation failed: ' . $e->getMessage(), [
                'request_data' => $request->except(['_token']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat membuat booking. Silakan coba lagi.'
            ])->withInput();
        }
    }

    /**
     * Generate booking details untuk ditampilkan di modal
     */
    private function generateBookingDetails(Order $order, Service $service, Outlet $outlet, array $validated): array
    {
        $customerName = $order->customer_type === 'member' 
            ? $order->customer->name 
            : $order->guest_name;

        $deliveryMethodLabel = match($order->delivery_method) {
            'walk_in' => 'Walk-in (Antar Sendiri)',
            'pickup' => 'Pickup (Dijemput)',
            'delivery' => 'Delivery (Diantar)',
            'pickup_delivery' => 'Pickup & Delivery',
            default => ucfirst($order->delivery_method),
        };

        $serviceSpeedLabel = match($order->service_speed) {
            'regular' => 'Regular (2-3 hari)',
            'express' => 'Express (1 hari)',
            'same_day' => 'Same Day (Hari ini)',
            default => ucfirst($order->service_speed),
        };

        $pickupTime = Carbon::parse($order->pickup_time);
        $estimatedCompletion = $this->calculateEstimatedDelivery($pickupTime, $order->service_speed);

        return [
            'order_id' => '#' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'customer_name' => $customerName,
            'customer_type' => $order->customer_type === 'member' ? 'Member' : 'Guest',
            'service_name' => $service->name,
            'outlet_name' => $outlet->name,
            'outlet_address' => $outlet->address,
            'delivery_method' => $deliveryMethodLabel,
            'service_speed' => $serviceSpeedLabel,
            'pickup_date' => $pickupTime->format('d M Y'),
            'pickup_time' => $pickupTime->format('H:i'),
            'pickup_datetime' => $pickupTime->format('l, d F Y - H:i'),
            'estimated_completion' => $estimatedCompletion->format('d M Y - H:i'),
            'address' => $validated['address'],
            'notes' => $order->notes ?? '-',
            'is_free_service' => $order->is_free_service,
            'has_courier' => !is_null($order->courier_id),
        ];
    }

    private function createTrackingRecords(Order $order, string $deliveryMethod, ?User $courier, string $address): void
    {
        $pickupTime = Carbon::parse($order->pickup_time);
        
        if (in_array($deliveryMethod, ['pickup', 'pickup_delivery'])) {
            Tracking::create([
                'order_id' => $order->id,
                'courier_id' => $courier?->id,
                'type' => 'pickup',
                'status' => 'pending',
                'scheduled_time' => $pickupTime,
                'actual_time' => null,
                'pickup_address' => $address,
                'delivery_address' => null,
                'notes' => 'Penjemputan laundry dari customer - Menunggu konfirmasi',
            ]);
        }

        if (in_array($deliveryMethod, ['delivery', 'pickup_delivery'])) {
            $estimatedDelivery = $this->calculateEstimatedDelivery($pickupTime, $order->service_speed);
            
            Tracking::create([
                'order_id' => $order->id,
                'courier_id' => $courier?->id,
                'type' => 'delivery',
                'status' => 'pending',
                'scheduled_time' => $estimatedDelivery,
                'actual_time' => null,
                'pickup_address' => null,
                'delivery_address' => $address,
                'notes' => 'Pengiriman laundry ke customer - Menunggu proses selesai',
            ]);
        }
    }

    private function calculateEstimatedDelivery(Carbon $pickupTime, string $serviceSpeed): Carbon
    {
        return match ($serviceSpeed) {
            'same_day' => $pickupTime->copy()->addHours(8),
            'express' => $pickupTime->copy()->addDay(),
            'regular' => $pickupTime->copy()->addDays(2),
            default => $pickupTime->copy()->addDays(2),
        };
    }

    private function getServiceIcon(string $serviceName): string
    {
        $name = strtolower($serviceName);
        if (str_contains($name, 'cuci kering') || str_contains($name, 'kiloan')) return 'fas fa-washing-machine';
        if (str_contains($name, 'setrika') || str_contains($name, 'iron')) return 'fas fa-tshirt';
        if (str_contains($name, 'dry clean')) return 'fas fa-suitcase';
        if (str_contains($name, 'bed') || str_contains($name, 'sprei')) return 'fas fa-bed';
        if (str_contains($name, 'sepatu') || str_contains($name, 'shoes')) return 'fas fa-shoe-prints';
        if (str_contains($name, 'carpet') || str_contains($name, 'karpet')) return 'fas fa-th-large';
        if (str_contains($name, 'express') || str_contains($name, 'kilat')) return 'fas fa-bolt';
        return 'fas fa-tshirt';
    }

    private function getServiceColor(string $serviceName): string
    {
        $name = strtolower($serviceName);
        if (str_contains($name, 'cuci kering') || str_contains($name, 'kiloan')) return 'from-blue-400 to-blue-600';
        if (str_contains($name, 'setrika')) return 'from-green-400 to-green-600';
        if (str_contains($name, 'dry clean')) return 'from-purple-400 to-purple-600';
        if (str_contains($name, 'bed') || str_contains($name, 'sprei')) return 'from-pink-400 to-pink-600';
        if (str_contains($name, 'express') || str_contains($name, 'kilat')) return 'from-red-400 to-red-600';
        if (str_contains($name, 'sepatu')) return 'from-yellow-400 to-yellow-600';
        return 'from-indigo-400 to-indigo-600';
    }
}