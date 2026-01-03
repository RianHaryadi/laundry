<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->take(3)->get();
        return view('home', compact('services'));
    }

    public function track(Request $request)
    {
        $number = str_replace('#', '', $request->query('number'));
        
        // Ambil order dengan relasi customer dan kurir
        $order = Order::with(['customer', 'courier'])->find($number);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Nomor order tidak ditemukan.']);
        }

        // Mapping status database ke urutan timeline
        $statuses = ['pending', 'confirmed', 'processing', 'ready', 'picked_up', 'in_delivery', 'completed'];
        $currentIndex = array_search($order->status, $statuses);

        // Definisi langkah-langkah di UI
        $steps = [
            ['title' => 'Order Received', 'desc' => 'Pesanan diterima & masuk antrean.', 'keys' => ['pending', 'confirmed']],
            ['title' => 'Processing', 'desc' => 'Pakaian sedang dicuci & disetrika.', 'keys' => ['processing']],
            ['title' => 'Quality Check', 'desc' => 'Pemeriksaan akhir & pengemasan.', 'keys' => ['ready']],
            ['title' => 'Delivery', 'desc' => 'Laundry sedang menuju lokasi Anda.', 'keys' => ['picked_up', 'in_delivery', 'completed']],
        ];

        // Tentukan status tiap step (completed/active)
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

        return response()->json([
            'success' => true,
            'order_number' => $order->formatted_id,
            'customer_name' => $order->customer_id ? ($order->customer?->name) : $order->guest_name,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
            'delivery_time' => $order->delivery_time ? $order->delivery_time->format('d M Y, H:i') : 'Estimasi segera',
            'courier_phone' => $order->courier?->phone,
            'steps' => $steps
        ]);
    }
}