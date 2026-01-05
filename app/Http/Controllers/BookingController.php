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
use Illuminate\Support\Facades\Log; // Ditambahkan untuk logging
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Tampilkan halaman booking
     */
    public function index(Request $request)
    {
        return $this->create($request);
    }

    public function create(Request $request)
    {
        // Fitur: Ambil ID layanan dari URL (?service_id=...) untuk auto-select
        $selectedServiceId = $request->query('service_id');

        // Ambil semua service yang aktif dari database
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
                    'pricing_label' => $service->pricing_type_label,
                    'formatted_price' => $service->formatted_price,
                    'duration' => $service->formatted_duration,
                    'icon' => $this->getServiceIcon($service->name),
                    'color' => $this->getServiceColor($service->name),
                ];
            });

        // Ambil outlet
        $outlets = Outlet::all();

        // Ambil data customer jika sudah login
        $customer = Auth::guard('customer')->user();

        // Kirim data ke view termasuk selectedServiceId
        return view('booking', compact('services', 'outlets', 'customer', 'selectedServiceId'));
    }

    public function store(Request $request)
    {
        // Validation rules (Ditambahkan is_free_service)
        $rules = [
            'service_id' => 'required|exists:services,id',
            'outlet_id' => 'required|exists:outlets,id',
            'delivery_method' => 'required|in:walk_in,pickup,delivery,pickup_delivery',
            'service_speed' => 'required|in:regular,express,same_day',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|string',
            'address' => 'required|string|min:10',
            'notes' => 'nullable|string',
            'is_free_service' => 'nullable|boolean', // Ditambahkan untuk Reward System
        ];

        // Additional rules for guest customers
        if (!Auth::guard('customer')->check()) {
            $rules['name'] = 'required|string|max:255';
            $rules['phone'] = 'required|string|min:10|max:20';
            $rules['email'] = 'nullable|email';
        }

        $validated = $request->validate($rules);

        // Get outlet and find available courier
        $outlet = Outlet::findOrFail($validated['outlet_id']);
        
        $courier = User::where('outlet_id', $outlet->id)
            ->where('role', 'courier')
            ->where('is_active', true)
            ->first();

        // Prepare order data (Ditambahkan service_id & is_free_service)
        $orderData = [
            'outlet_id' => $validated['outlet_id'],
            'service_id' => $validated['service_id'], // Penting agar Model bisa hitung harga
            'delivery_method' => $validated['delivery_method'],
            'service_speed' => $validated['service_speed'],
            'courier_id' => $courier?->id, 
            'status' => 'pending',
            'payment_status' => 'pending',
            'pickup_time' => $validated['pickup_date'] . ' ' . explode('-', $validated['pickup_time'])[0] . ':00',
            'notes' => $validated['notes'] ?? null,
            'is_free_service' => $request->has('is_free_service'), // Flag dari checkbox reward
            'total_weight' => 0,
            'total_price' => 0,
            'discount_amount' => 0,
            'final_price' => 0,
            'base_price' => 0,
        ];

        // Set customer data based on login status
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

        // Create order (Model boot() akan menangani perhitungan harga & pemotongan kupon)
        $order = Order::create($orderData);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $validated['service_id'],
            'pricing_type' => 'kg',
            'quantity' => 0,
            'weight' => 0,
            'price' => 0,
            'subtotal' => 0,
        ]);

        // Create tracking records if delivery method requires it
        $deliveryMethod = $validated['delivery_method'];
        if (in_array($deliveryMethod, ['pickup', 'delivery', 'pickup_delivery'])) {
            $this->createTrackingRecords($order, $deliveryMethod, $courier, $validated['address']);
        }

        return redirect()->route('home')->with('success', 'Booking berhasil! Tim kami akan segera menghubungi Anda untuk konfirmasi penjemputan.');
    }

    /**
     * Create tracking records based on delivery method
     */
    private function createTrackingRecords(Order $order, string $deliveryMethod, ?User $courier, string $address): void
    {
        $pickupTime = Carbon::parse($order->pickup_time);
        
        // Create PICKUP tracking record
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

        // Create DELIVERY tracking record
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

    /**
     * Calculate estimated delivery time based on service speed
     */
    private function calculateEstimatedDelivery(Carbon $pickupTime, string $serviceSpeed): Carbon
    {
        return match ($serviceSpeed) {
            'same_day' => $pickupTime->copy()->addHours(8),
            'express' => $pickupTime->copy()->addDay(),
            'regular' => $pickupTime->copy()->addDays(2),
            default => $pickupTime->copy()->addDays(2),
        };
    }

    /**
     * Helper: Get icon for service based on name
     */
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

    /**
     * Helper: Get color gradient for service based on name
     */
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