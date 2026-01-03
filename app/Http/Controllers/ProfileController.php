<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Customer;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // FLEXIBLE: Check if customers table has user_id column
        $hasUserId = Schema::hasColumn('customers', 'user_id');
        
        // Get customer based on available structure
        if ($hasUserId) {
            // If table has user_id column, use it
            $customer = Customer::where('user_id', $user->id)->first();
        } else {
            // If no user_id, try to match by email or phone
            $customer = Customer::where('email', $user->email)->first();
            
            // Or create a temporary customer object from user data
            if (!$customer) {
                $customer = new Customer();
                $customer->id = null;
                $customer->name = $user->name;
                $customer->email = $user->email;
                $customer->phone = $user->phone ?? null;
                $customer->address = $user->address ?? null;
                $customer->reward_points = 0;
                $customer->membership_type = 'regular';
            }
        }
        
        // Get customer ID for queries (use email if no direct customer)
        $customerId = $customer->id;
        
        if (!$customerId) {
            // If no customer record, no orders can be found
            $totalOrders = 0;
            $completedOrders = 0;
            $activeOrders = 0;
            $totalSpent = 0;
            $recentOrders = collect([]);
            $rewardPoints = 0;
            $availableCoupons = 0;
        } else {
            // Get customer's order statistics
            $totalOrders = Order::where('customer_id', $customerId)->count();
            $completedOrders = Order::where('customer_id', $customerId)
                ->where('status', 'completed')
                ->count();
            $activeOrders = Order::where('customer_id', $customerId)
                ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready', 'picked_up', 'in_delivery'])
                ->count();
            $totalSpent = Order::where('customer_id', $customerId)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->sum('final_price');
            
            // Get recent orders (last 10)
            $recentOrders = Order::where('customer_id', $customerId)
                ->with(['service', 'outlet', 'courier', 'latestTracking'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            // Get reward points and available coupons
            $rewardPoints = $customer->reward_points ?? 0;
            
            // Check if customer has coupons relationship
            try {
                $availableCoupons = $customer->coupons()
                    ->where('is_used', false)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->count();
            } catch (\Exception $e) {
                $availableCoupons = 0;
            }
        }
        
        return view('profile.index', compact(
            'user',
            'customer',
            'totalOrders',
            'completedOrders',
            'activeOrders',
            'totalSpent',
            'recentOrders',
            'rewardPoints',
            'availableCoupons'
        ));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);
        
        // Update user info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // Update phone and address if user table has these columns
        if (Schema::hasColumn('users', 'phone')) {
            $user->phone = $validated['phone'] ?? $user->phone;
        }
        if (Schema::hasColumn('users', 'address')) {
            $user->address = $validated['address'] ?? $user->address;
        }
        
        // Update password if provided
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            
            $user->password = Hash::make($request->new_password);
        }
        
        $user->save();
        
        // Update customer info if exists
        $hasUserId = Schema::hasColumn('customers', 'user_id');
        
        if ($hasUserId) {
            $customer = Customer::where('user_id', $user->id)->first();
        } else {
            $customer = Customer::where('email', $user->email)->first();
        }
        
        if ($customer) {
            // Update customer fields that exist
            if (Schema::hasColumn('customers', 'name')) {
                $customer->name = $validated['name'];
            }
            if (Schema::hasColumn('customers', 'email')) {
                $customer->email = $validated['email'];
            }
            if (Schema::hasColumn('customers', 'phone')) {
                $customer->phone = $validated['phone'] ?? $customer->phone;
            }
            if (Schema::hasColumn('customers', 'address')) {
                $customer->address = $validated['address'] ?? $customer->address;
            }
            
            $customer->save();
        }
        
        return back()->with('success', 'Profile updated successfully!');
    }
}