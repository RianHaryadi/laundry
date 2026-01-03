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
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0,
            ];
        } else {
            // Get orders with filters
            $query = Order::where('customer_id', $customer->id)
                ->with(['service', 'outlet', 'courier', 'latestTracking']);
            
            // Filter by status
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            
            // Search
            if ($request->has('search') && $request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('id', 'like', '%' . $request->search . '%')
                      ->orWhere('invoice_number', 'like', '%' . $request->search . '%');
                });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
            
            // Paginate
            $orders = $query->paginate(15);
            
            // Get statistics
            $stats = [
                'total' => Order::where('customer_id', $customer->id)->count(),
                'pending' => Order::where('customer_id', $customer->id)
                    ->whereIn('status', ['pending', 'confirmed', 'processing'])
                    ->count(),
                'completed' => Order::where('customer_id', $customer->id)
                    ->where('status', 'completed')
                    ->count(),
                'cancelled' => Order::where('customer_id', $customer->id)
                    ->where('status', 'cancelled')
                    ->count(),
            ];
        }
        
        return view('orders.index', compact('orders', 'stats'));
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
        
        // Load relationships
        $order->load([
            'service',
            'outlet',
            'customer',
            'courier',
            'items',
            'trackings' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'latestTracking'
        ]);
        
        return view('orders.show', compact('order'));
    }
}