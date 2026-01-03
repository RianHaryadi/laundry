<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Service;
use App\Models\Outlet;
use App\Models\Order;

class CustomerController extends Controller
{
    /**
     * Show customer home page (public)
     */
    public function index()
    {
        // Get authenticated customer (if logged in)
        $customer = Auth::guard('customer')->user();
        
        // Get available services (check if is_active column exists)
        $servicesQuery = Service::query();
        if (Schema::hasColumn('services', 'is_active')) {
            $servicesQuery->where('is_active', true);
        }
        $services = $servicesQuery->orderBy('name')->get();
        
        // Get available outlets (check if is_active column exists)
        $outletsQuery = Outlet::query();
        if (Schema::hasColumn('outlets', 'is_active')) {
            $outletsQuery->where('is_active', true);
        }
        $outlets = $outletsQuery->orderBy('name')->get();
        
        // Get customer's active orders (if logged in)
        $activeOrders = [];
        if ($customer) {
            $activeOrders = Order::where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready', 'picked_up', 'in_delivery'])
                ->with(['service', 'outlet'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
        
        return view('customer.home', compact('services', 'outlets', 'activeOrders', 'customer'));
    }
    
    /**
     * Show order creation form (protected)
     */
    public function createOrder()
    {
        $customer = Auth::guard('customer')->user();
        
        // Get available services (check if is_active column exists)
        $servicesQuery = Service::query();
        if (Schema::hasColumn('services', 'is_active')) {
            $servicesQuery->where('is_active', true);
        }
        $services = $servicesQuery->orderBy('name')->get();
        
        // Get available outlets (check if is_active column exists)
        $outletsQuery = Outlet::query();
        if (Schema::hasColumn('outlets', 'is_active')) {
            $outletsQuery->where('is_active', true);
        }
        $outlets = $outletsQuery->orderBy('name')->get();
        
        return view('customer.create-order', compact('services', 'outlets', 'customer'));
    }
    
    /**
     * Store new order (protected)
     */
    public function storeOrder(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'outlet_id' => 'required|exists:outlets,id',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Create order
        $order = Order::create([
            'customer_id' => $customer->id,
            'service_id' => $validated['service_id'],
            'outlet_id' => $validated['outlet_id'],
            'pickup_date' => $validated['pickup_date'],
            'pickup_time' => $validated['pickup_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        
        return redirect()->route('orders.index')
            ->with('success', 'Order created successfully! Order ID: #' . $order->id);
    }
}