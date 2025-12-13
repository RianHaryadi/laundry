<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderInvoiceController extends Controller
{
    /**
     * Display invoice in browser
     */
    public function show(Order $order)
    {
        // Load relationships
        $order->load([
            'customer',
            'outlet',
            'courier',
            'orderItems.service',
            'coupon'
        ]);

        return view('invoices.order', compact('order'));
    }

    /**
     * Download invoice as PDF
     */
    public function download(Order $order)
    {
        $order->load([
            'customer',
            'outlet',
            'courier',
            'orderItems.service',
            'coupon'
        ]);

        $pdf = Pdf::loadView('invoices.order-pdf', compact('order'))
            ->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Print-optimized view
     */
    public function print(Order $order)
    {
        $order->load([
            'customer',
            'outlet',
            'courier',
            'orderItems.service',
            'coupon'
        ]);

        return view('invoices.order-print', compact('order'));
    }

    /**
     * Get invoice data as JSON (for API)
     */
    public function json(Order $order)
    {
        $order->load([
            'customer',
            'outlet',
            'courier',
            'orderItems.service',
            'coupon'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'invoice_number' => $this->generateInvoiceNumber($order),
                'customer_name' => $this->getCustomerName($order),
                'customer_phone' => $this->getCustomerPhone($order),
                'customer_address' => $this->getCustomerAddress($order),
            ]
        ]);
    }

    /**
     * Helper: Generate invoice number
     */
    private function generateInvoiceNumber(Order $order): string
    {
        return 'INV-' . $order->created_at->format('Ymd') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Helper: Get customer name
     */
    private function getCustomerName(Order $order): string
    {
        return $order->customer_type === 'member'
            ? ($order->customer?->name ?? 'Unknown Member')
            : ($order->guest_name ?? 'Guest Customer');
    }

    /**
     * Helper: Get customer phone
     */
    private function getCustomerPhone(Order $order): ?string
    {
        return $order->customer_type === 'member'
            ? $order->customer?->phone
            : $order->guest_phone;
    }

    /**
     * Helper: Get customer address
     */
    private function getCustomerAddress(Order $order): ?string
    {
        return $order->customer_type === 'member'
            ? $order->customer?->address
            : $order->guest_address;
    }
}