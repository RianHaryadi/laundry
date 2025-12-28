<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class TrackingController extends Controller
{
    public function index()
    {
        return view('tracking');
    }

    public function search(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string'
        ]);

        $order = Order::where('tracking_code', $request->tracking_code)->first();

        if (!$order) {
            return back()->with('error', 'Kode tracking tidak ditemukan');
        }

        return view('tracking', compact('order'));
    }
}