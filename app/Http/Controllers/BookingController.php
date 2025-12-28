<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create()
    {
        $services = [
            [
                'name' => 'Cuci Kering',
                'price' => 10000,
                'unit' => 'kg',
                'description' => 'Cuci biasa dengan pengeringan',
                'icon' => 'fas fa-washing-machine',
                'color' => 'from-blue-400 to-blue-600',
                'eta' => '1-2 hari'
            ],
            [
                'name' => 'Cuci Setrika',
                'price' => 15000,
                'unit' => 'kg',
                'description' => 'Cuci + setrika rapi',
                'icon' => 'fas fa-tshirt',
                'color' => 'from-green-400 to-green-600',
                'eta' => '2-3 hari'
            ],
            [
                'name' => 'Dry Cleaning',
                'price' => 25000,
                'unit' => 'pcs',
                'description' => 'Khusus pakaian formal & khusus',
                'icon' => 'fas fa-suitcase',
                'color' => 'from-purple-400 to-purple-600',
                'eta' => '3-4 hari'
            ],
            [
                'name' => 'Bed Cover',
                'price' => 30000,
                'unit' => 'pcs',
                'description' => 'Cuci sprei & bed cover',
                'icon' => 'fas fa-bed',
                'color' => 'from-pink-400 to-pink-600',
                'eta' => '2-3 hari'
            ],
        ];

        return view('booking', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service' => 'required|string',
            'quantity' => 'required|numeric|min:0.5',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|string',
            'phone' => 'required|string|min:10',
            'email' => 'nullable|email',
            'address' => 'required|string|min:10',
            'notes' => 'nullable|string',
        ]);

        // Save booking logic here
        
        return redirect()->route('booking.success')->with('success', 'Booking berhasil dibuat!');
    }
}