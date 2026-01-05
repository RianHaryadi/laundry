<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Customer;

class OrderController extends Controller
{
    /**
     * Display a listing of user's orders
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        
        // Get customer ID
        $hasUserId = Schema::hasColumn('customers', 'user_id');
        
        if ($hasUserId) {
            $customer = Customer::where('user_id', $user->id)->first();
        } else {
            $customer = Customer::where('email', $user->email)->first();
        }
        
        if (!$customer || !$customer->id) {
            // No customer found, show empty orders
            $orders = collect([]);
            $stats = [
                'all' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'processing' => 0,
                'ready' => 0,
                'completed' => 0,
                'cancelled' => 0,
            ];
        } else {
            // ✅ Get orders with PROPER eager loading
            $query = Order::where('customer_id', $customer->id)
                ->with([
                    'service',              // Load direct service relation
                    'outlet',               // Load outlet relation
                    'items.service',        // Load items with their services
                    'courier',              // Load courier
                    'latestTracking',       // Load latest tracking
                    'coupon'                // Load coupon if exists
                ]);
            
            // Filter by status
            if ($status && $status != 'all') {
                $query->where('status', $status);
            }
            
            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhere('invoice_number', 'like', '%' . $search . '%')
                      // Search in direct service relation
                      ->orWhereHas('service', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      })
                      // Search in items.service relation
                      ->orWhereHas('items.service', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
                });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
            
            // Paginate
            $orders = $query->paginate(15)->appends($request->except('page'));
            
            // Get statistics for all status types
            $stats = [
                'all' => Order::where('customer_id', $customer->id)->count(),
                'pending' => Order::where('customer_id', $customer->id)
                    ->where('status', 'pending')
                    ->count(),
                'confirmed' => Order::where('customer_id', $customer->id)
                    ->where('status', 'confirmed')
                    ->count(),
                'processing' => Order::where('customer_id', $customer->id)
                    ->where('status', 'processing')
                    ->count(),
                'ready' => Order::where('customer_id', $customer->id)
                    ->where('status', 'ready')
                    ->count(),
                'completed' => Order::where('customer_id', $customer->id)
                    ->where('status', 'completed')
                    ->count(),
                'cancelled' => Order::where('customer_id', $customer->id)
                    ->where('status', 'cancelled')
                    ->count(),
            ];
        }
        
        return view('orders.index', compact('orders', 'stats', 'status', 'search'));
    }
    
    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Get customer ID
        $hasUserId = Schema::hasColumn('customers', 'user_id');
        
        if ($hasUserId) {
            $customer = Customer::where('user_id', $user->id)->first();
        } else {
            $customer = Customer::where('email', $user->email)->first();
        }
        
        // Check if order belongs to this customer
        if (!$customer || $order->customer_id != $customer->id) {
            abort(403, 'Unauthorized access to this order.');
        }
        
        // ✅ Load all necessary relationships
        $order->load([
            'service',
            'items.service',
            'outlet',
            'customer',
            'courier',
            'coupon',
            'trackings' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'latestTracking'
        ]);
        
        return view('orders.show', compact('order'));
    }
}