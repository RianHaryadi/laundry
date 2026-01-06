<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class TrackingController extends Controller
{
    public function index()
    {
        $recentSearches = [];
        
        // Get recent searches if user is authenticated
        if (auth()->check()) {
            $recentSearches = Order::where('customer_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
        
        return view('tracking', compact('recentSearches'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'tracking_code' => 'nullable|string',
            'phone' => 'nullable|string'
        ]);

        $query = Order::with(['customer', 'courier', 'service', 'outlet']);

        // Search by order ID (formatted as #000001) or phone
        if ($request->tracking_code) {
            // Remove # if exists and leading zeros
            $orderId = ltrim(str_replace('#', '', $request->tracking_code), '0');
            $query->where('id', $orderId);
        } elseif ($request->phone) {
            // Search by guest phone or customer phone
            $query->where(function($q) use ($request) {
                $q->where('guest_phone', $request->phone)
                  ->orWhereHas('customer', function($q2) use ($request) {
                      $q2->where('phone', $request->phone);
                  });
            });
        } else {
            return back()->with('error', 'Masukkan kode tracking atau nomor telepon');
        }

        $order = $query->first();

        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan');
        }

        // Get recent searches for authenticated users
        $recentSearches = [];
        if (auth()->check()) {
            $recentSearches = Order::where('customer_id', auth()->id())
                ->where('id', '!=', $order->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return view('tracking', compact('order', 'recentSearches'));
    }

    public function track(Request $request)
    {
        $number = str_replace('#', '', $request->query('number'));
        $orderId = ltrim($number, '0'); // Remove leading zeros
        
        // Get order with relations
        $order = Order::with(['customer', 'courier', 'service', 'outlet', 'trackings'])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false, 
                'message' => 'Nomor order tidak ditemukan.'
            ]);
        }

        // Map database status to timeline order
        $statuses = ['pending', 'confirmed', 'processing', 'ready', 'picked_up', 'in_delivery', 'completed'];
        $currentIndex = array_search($order->status, $statuses);

        // Define UI steps
        $steps = [
            [
                'title' => 'Order Received', 
                'desc' => 'Pesanan diterima & masuk antrean.', 
                'keys' => ['pending', 'confirmed'],
                'icon' => 'fas fa-inbox',
                'color' => 'green'
            ],
            [
                'title' => 'Processing', 
                'desc' => 'Pakaian sedang dicuci & disetrika.', 
                'keys' => ['processing'],
                'icon' => 'fas fa-washing-machine',
                'color' => 'blue'
            ],
            [
                'title' => 'Quality Check', 
                'desc' => 'Pemeriksaan akhir & pengemasan.', 
                'keys' => ['ready'],
                'icon' => 'fas fa-search',
                'color' => 'yellow'
            ],
            [
                'title' => 'Delivery', 
                'desc' => 'Laundry sedang menuju lokasi Anda.', 
                'keys' => ['picked_up', 'in_delivery', 'completed'],
                'icon' => 'fas fa-truck',
                'color' => 'purple'
            ],
        ];

        // Determine status for each step
        foreach ($steps as &$step) {
            $step['is_completed'] = false;
            $step['is_active'] = false;

            foreach ($step['keys'] as $key) {
                $keyIndex = array_search($key, $statuses);
                if ($order->status === $key) {
                    $step['is_active'] = true;
                }
                if ($currentIndex > $keyIndex || $order->status === 'completed') {
                    $step['is_completed'] = true;
                }
            }
        }

        // Get customer name
        $customerName = $order->customer_type === 'member' && $order->customer 
            ? $order->customer->name 
            : ($order->guest_name ?? 'Guest');

        return response()->json([
            'success' => true,
            'order_number' => $order->formatted_id,
            'customer_name' => $customerName,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
            'delivery_time' => $order->delivery_time ? $order->delivery_time->format('d M Y, H:i') : 'Estimasi segera',
            'courier_phone' => $order->courier?->phone,
            'service_name' => $order->service?->name ?? 'Regular Wash',
            'outlet_name' => $order->outlet?->name ?? 'Main Outlet',
            'total_weight' => $order->total_weight,
            'final_price' => $order->final_price,
            'steps' => $steps
        ]);
    }
}