<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if required models exist
        if (!class_exists(Order::class)) {
            $this->command->warn('⚠️  Order model not found. Skipping OrderSeeder.');
            return;
        }

        $customers = Customer::all();
        
        if ($customers->isEmpty()) {
            $this->command->warn('⚠️  No customers found. Please run CustomerSeeder first.');
            return;
        }

        $outlets = Outlet::all();
        
        if ($outlets->isEmpty()) {
            $this->command->warn('⚠️  No outlets found. Please run OutletSeeder first.');
            return;
        }

        $services = Service::all();
        
        if ($services->isEmpty()) {
            $this->command->warn('⚠️  No services found. Please run ServiceSeeder first.');
            return;
        }

        // Get couriers (users with role courier)
        $couriers = User::where('role', 'courier')->pluck('id')->toArray();
        if (empty($couriers)) {
            $couriers = User::limit(3)->pluck('id')->toArray();
        }

        $this->command->info('🧺 Creating laundry orders for customers...');

        // Based on your database ENUM definitions
        $orderTypes = ['pickup', 'dropoff', 'delivery'];
        $orderStatuses = ['pending', 'processing', 'ready', 'delivered', 'completed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid', 'refunded'];
        
        $totalOrders = 0;
        $totalPointsDistributed = 0;

        foreach ($customers as $customer) {
            // Generate random number of orders based on membership level
            // Disesuaikan agar total spending match dengan points yang dimiliki
            $orderCount = match($customer->membership_level) {
                'vip' => rand(50, 80),        // VIP = 50-80 orders
                'platinum' => rand(30, 50),   // Platinum = 30-50 orders
                'gold' => rand(15, 30),       // Gold = 15-30 orders
                'silver' => rand(8, 15),      // Silver = 8-15 orders
                'bronze' => rand(0, 5),       // Bronze = 0-5 orders (masih baru)
                default => rand(0, 3),
            };

            // Calculate target total spending based on total_points_earned
            // Assumption: customer earns 1 point per Rp 10,000 spent
            $targetSpending = $customer->total_points_earned * 10000;
            $remainingSpending = $targetSpending;

            for ($i = 0; $i < $orderCount; $i++) {
                $outlet = $outlets->random();
                $service = $services->random();
                $type = $orderTypes[array_rand($orderTypes)];
                
                // Most orders are completed (85% success rate)
                $statusRandom = rand(0, 100);
                if ($statusRandom < 85) {
                    $status = 'completed';
                    $paymentStatus = 'paid';
                } elseif ($statusRandom < 88) {
                    $status = 'delivered';
                    $paymentStatus = 'paid';
                } elseif ($statusRandom < 92) {
                    $status = ['processing', 'ready'][array_rand(['processing', 'ready'])];
                    $paymentStatus = rand(0, 1) ? 'paid' : 'unpaid';
                } elseif ($statusRandom < 96) {
                    $status = 'pending';
                    $paymentStatus = 'unpaid';
                } else {
                    $status = 'cancelled';
                    $paymentStatus = rand(0, 1) ? 'unpaid' : 'refunded';
                }
                
                $orderDate = now()->subDays(rand(1, 365)); // Order dalam 1 tahun terakhir
                
                // Random weight (kg) - laundry weight
                $totalWeight = rand(20, 100) / 10; // 2.0 - 10.0 kg
                
                // Calculate base price based on service price
                $pricePerKg = $service->price_per_kg ?? 8000; // Default Rp 8,000/kg
                $basePrice = $totalWeight * $pricePerKg;
                
                // Type doesn't affect price in this system (pickup, dropoff, delivery)
                // All types have same base pricing
                $totalPrice = $basePrice;
                
                // Apply membership discount
                $discountAmount = 0;
                $discountType = null;
                if ($customer->membership_level && $status === 'completed') {
                    $discountPercentage = match($customer->membership_level) {
                        'vip' => 15,
                        'platinum' => 12,
                        'gold' => 10,
                        'silver' => 5,
                        default => 0,
                    };
                    
                    if ($discountPercentage > 0) {
                        $discountAmount = ($totalPrice * $discountPercentage) / 100;
                        $discountType = 'membership';
                    }
                }
                
                // Pickup/delivery fee
                $pickupDeliveryFee = 0;
                
                if (in_array($type, ['pickup', 'delivery'])) {
                    // Free for Gold, Platinum, VIP
                    if (!in_array($customer->membership_level, ['gold', 'platinum', 'vip'])) {
                        $pickupDeliveryFee = 10000; // Rp 10,000
                    }
                }
                
                $finalPrice = $totalPrice - $discountAmount + $pickupDeliveryFee;
                
                // Adjust final order to match target spending
                if ($i === $orderCount - 1 && $remainingSpending > 0) {
                    $adjustment = $remainingSpending / max(1, ($orderCount - $i));
                    $finalPrice = max(20000, min(500000, $adjustment));
                    $totalPrice = $finalPrice + $discountAmount - $pickupDeliveryFee;
                }
                
                $remainingSpending -= $finalPrice;
                
                // Assign courier for pickup/delivery orders
                $courierId = null;
                if (in_array($type, ['pickup', 'delivery']) && !empty($couriers)) {
                    $courierId = $couriers[array_rand($couriers)];
                }
                
                // Calculate pickup and delivery times
                $pickupTime = null;
                $deliveryTime = null;
                
                if (in_array($type, ['pickup', 'delivery'])) {
                    $pickupTime = $orderDate->copy()->addHours(rand(1, 4));
                    $deliveryTime = $pickupTime->copy()->addDays(rand(2, 4)); // 2-4 days
                }
                
                $orderData = [
                    'customer_id' => $customer->id,
                    'outlet_id' => $outlet->id,
                    'service_id' => $service->id,
                    'courier_id' => $courierId,
                    'type' => $type,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'total_weight' => $totalWeight,
                    'total_price' => round($totalPrice, 2),
                    'discount_amount' => round($discountAmount, 2),
                    'final_price' => round($finalPrice, 2),
                    'discount_type' => $discountType,
                    'pickup_delivery_fee' => round($pickupDeliveryFee, 2),
                    'pickup_time' => $pickupTime,
                    'delivery_time' => $deliveryTime,
                    'notes' => rand(0, 100) < 30 ? ['Separate whites and colors', 'No fabric softener', 'Extra rinse please', 'Iron clothes', 'Hang dry only'][array_rand(['Separate whites and colors', 'No fabric softener', 'Extra rinse please', 'Iron clothes', 'Hang dry only'])] : null,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];

                Order::create($orderData);

                // Calculate points earned for completed orders
                if ($status === 'completed' && $paymentStatus === 'paid') {
                    $pointsEarned = (int) floor($finalPrice / 10000); // 1 point per Rp 10,000
                    $totalPointsDistributed += $pointsEarned;
                }

                $totalOrders++;
            }
        }

        $this->command->info("✅ Created {$totalOrders} laundry orders successfully!");
        
        // Display statistics
        $this->command->info('');
        $this->command->info('📊 Order Statistics:');
        $this->command->info('Completed: ' . Order::where('status', 'completed')->count());
        $this->command->info('Delivered: ' . Order::where('status', 'delivered')->count());
        $this->command->info('Ready: ' . Order::where('status', 'ready')->count());
        $this->command->info('Processing: ' . Order::where('status', 'processing')->count());
        $this->command->info('Pending: ' . Order::where('status', 'pending')->count());
        $this->command->info('Cancelled: ' . Order::where('status', 'cancelled')->count());
        
        $this->command->info('');
        $this->command->info('💰 Payment Statistics:');
        $this->command->info('Paid: ' . Order::where('payment_status', 'paid')->count());
        $this->command->info('Unpaid: ' . Order::where('payment_status', 'unpaid')->count());
        $this->command->info('Refunded: ' . Order::where('payment_status', 'refunded')->count());
        
        $this->command->info('');
        $this->command->info('🎯 Points Distributed: ~' . $totalPointsDistributed . ' points');
        
        // Customer with most orders
        $topCustomer = Customer::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->first();
            
        if ($topCustomer) {
            $this->command->info('');
            $this->command->info("🏆 Top Customer: {$topCustomer->name} ({$topCustomer->orders_count} orders)");
        }
        
        // Total revenue
        $totalRevenue = Order::where('status', 'completed')->sum('final_price');
        $this->command->info('');
        $this->command->info('💵 Total Revenue: Rp ' . number_format($totalRevenue, 0, ',', '.'));
    }
}