<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get outlets
        $outletPusat = Outlet::where('name', 'LaundryMate Pusat')->first();
        $outletBeji = Outlet::where('name', 'LaundryMate Beji')->first();

        if (!$outletPusat || !$outletBeji) {
            $this->command->error('Outlets not found! Please run OutletSeeder first.');
            return;
        }

        $coupons = [
            // ==================== ACTIVE COUPONS ====================
            
            // Welcome Discount
            [
                'code' => 'WELCOME2024',
                'description' => 'Welcome discount for new customers. Get 20% off your first order!',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'max_discount' => 50000.00,
                'min_order' => 100000.00,
                'max_uses' => 100,
                'max_uses_per_user' => 1,
                'used_count' => 23,
                'starts_at' => Carbon::now()->subMonths(2),
                'expires_at' => Carbon::now()->addMonths(4),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => true,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Weekend Promo
            [
                'code' => 'WEEKEND15',
                'description' => 'Weekend special! 15% off for all laundry services on Saturday and Sunday.',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'max_discount' => 75000.00,
                'min_order' => 150000.00,
                'max_uses' => 200,
                'max_uses_per_user' => 4,
                'used_count' => 87,
                'starts_at' => Carbon::now()->subMonth(),
                'expires_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Premium Customer
            [
                'code' => 'PREMIUM25',
                'description' => 'Exclusive 25% discount for premium customers. VIP treatment!',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'max_discount' => 100000.00,
                'min_order' => 200000.00,
                'max_uses' => 50,
                'max_uses_per_user' => 2,
                'used_count' => 12,
                'starts_at' => Carbon::now()->subWeeks(3),
                'expires_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'is_public' => false, // Private/VIP only
                'first_order_only' => false,
                'exclude_discounted_items' => true,
                'outlets' => [$outletPusat->id],
            ],

            // Flash Sale
            [
                'code' => 'FLASH30',
                'description' => 'FLASH SALE! 30% off for the next 7 days only. Limited slots!',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'max_discount' => 120000.00,
                'min_order' => 250000.00,
                'max_uses' => 75,
                'max_uses_per_user' => 1,
                'used_count' => 45,
                'starts_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addDays(5), // Expires soon!
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Fixed Amount Discount
            [
                'code' => 'HEMAT50K',
                'description' => 'Save Rp 50,000 instantly! Valid for orders above Rp 300,000.',
                'discount_type' => 'fixed',
                'discount_value' => 50000.00,
                'max_discount' => null,
                'min_order' => 300000.00,
                'max_uses' => 150,
                'max_uses_per_user' => 3,
                'used_count' => 62,
                'starts_at' => Carbon::now()->subWeeks(2),
                'expires_at' => Carbon::now()->addMonth(),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Big Order Discount
            [
                'code' => 'BIGORDER100',
                'description' => 'Huge discount for bulk orders! Get Rp 100,000 off for orders above Rp 500,000.',
                'discount_type' => 'fixed',
                'discount_value' => 100000.00,
                'max_discount' => null,
                'min_order' => 500000.00,
                'max_uses' => 100,
                'max_uses_per_user' => 5,
                'used_count' => 28,
                'starts_at' => Carbon::now()->subWeeks(4),
                'expires_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id],
            ],

            // Free Shipping
            [
                'code' => 'FREESHIP',
                'description' => 'Free delivery! No delivery charges for orders above Rp 100,000.',
                'discount_type' => 'free_shipping',
                'discount_value' => 0.00,
                'max_discount' => null,
                'min_order' => 100000.00,
                'max_uses' => 500,
                'max_uses_per_user' => 10,
                'used_count' => 234,
                'starts_at' => Carbon::now()->subMonths(3),
                'expires_at' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Monthly Member
            [
                'code' => 'MEMBER10',
                'description' => 'Monthly member exclusive! 10% off for all services.',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'max_discount' => 50000.00,
                'min_order' => 50000.00,
                'max_uses' => null, // Unlimited
                'max_uses_per_user' => 20,
                'used_count' => 156,
                'starts_at' => Carbon::now()->subMonths(1),
                'expires_at' => null, // No expiration
                'is_active' => true,
                'is_public' => false,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Beji Exclusive
            [
                'code' => 'BEJI20',
                'description' => 'Exclusive for LaundryMate Beji customers! 20% off all services.',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'max_discount' => 60000.00,
                'min_order' => 120000.00,
                'max_uses' => 80,
                'max_uses_per_user' => 2,
                'used_count' => 34,
                'starts_at' => Carbon::now()->subWeeks(3),
                'expires_at' => Carbon::now()->addWeeks(5),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletBeji->id], // Beji only
            ],

            // ==================== SCHEDULED COUPONS ====================

            // New Year 2025
            [
                'code' => 'NEWYEAR2025',
                'description' => 'Happy New Year 2025! Special 35% discount to celebrate the new year.',
                'discount_type' => 'percentage',
                'discount_value' => 35.00,
                'max_discount' => 150000.00,
                'min_order' => 200000.00,
                'max_uses' => 200,
                'max_uses_per_user' => 1,
                'used_count' => 0,
                'starts_at' => Carbon::now()->addDays(30), // Scheduled for future
                'expires_at' => Carbon::now()->addDays(45),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Christmas Special
            [
                'code' => 'XMAS2024',
                'description' => 'Merry Christmas! 40% off special holiday discount.',
                'discount_type' => 'percentage',
                'discount_value' => 40.00,
                'max_discount' => 200000.00,
                'min_order' => 300000.00,
                'max_uses' => 100,
                'max_uses_per_user' => 1,
                'used_count' => 0,
                'starts_at' => Carbon::now()->addDays(20), // Scheduled
                'expires_at' => Carbon::now()->addDays(27),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // ==================== EXPIRED COUPONS ====================

            // Independence Day (Expired)
            [
                'code' => 'MERDEKA17',
                'description' => 'Indonesia Independence Day special! 17% discount for all Indonesians.',
                'discount_type' => 'percentage',
                'discount_value' => 17.00,
                'max_discount' => 85000.00,
                'min_order' => 150000.00,
                'max_uses' => 170,
                'max_uses_per_user' => 2,
                'used_count' => 163,
                'starts_at' => Carbon::now()->subMonths(4),
                'expires_at' => Carbon::now()->subMonths(3), // Expired
                'is_active' => false,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // Ramadan Promo (Expired)
            [
                'code' => 'RAMADAN50K',
                'description' => 'Ramadan special! Rp 50,000 off for all customers during fasting month.',
                'discount_type' => 'fixed',
                'discount_value' => 50000.00,
                'max_discount' => null,
                'min_order' => 200000.00,
                'max_uses' => 250,
                'max_uses_per_user' => 3,
                'used_count' => 241,
                'starts_at' => Carbon::now()->subMonths(6),
                'expires_at' => Carbon::now()->subMonths(5), // Expired
                'is_active' => false,
                'is_public' => true,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id, $outletBeji->id],
            ],

            // ==================== FULLY USED COUPONS ====================

            // Grand Opening (Fully Used)
            [
                'code' => 'GRANDOPEN50',
                'description' => 'Grand Opening special! Limited to first 50 customers only.',
                'discount_type' => 'percentage',
                'discount_value' => 50.00,
                'max_discount' => 150000.00,
                'min_order' => 100000.00,
                'max_uses' => 50,
                'max_uses_per_user' => 1,
                'used_count' => 50, // Fully used
                'starts_at' => Carbon::now()->subMonths(8),
                'expires_at' => Carbon::now()->addMonth(),
                'is_active' => true,
                'is_public' => true,
                'first_order_only' => true,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id],
            ],

            // ==================== INACTIVE COUPONS ====================

            // Test Coupon (Inactive)
            [
                'code' => 'TEST99',
                'description' => 'Test coupon for internal testing. Do not use in production.',
                'discount_type' => 'percentage',
                'discount_value' => 99.00,
                'max_discount' => 999999.00,
                'min_order' => 1000.00,
                'max_uses' => 10,
                'max_uses_per_user' => 1,
                'used_count' => 3,
                'starts_at' => Carbon::now()->subWeek(),
                'expires_at' => Carbon::now()->addMonth(),
                'is_active' => false, // Inactive
                'is_public' => false,
                'first_order_only' => false,
                'exclude_discounted_items' => false,
                'outlets' => [$outletPusat->id],
            ],
        ];

        foreach ($coupons as $couponData) {
            $outlets = $couponData['outlets'];
            unset($couponData['outlets']);

            $coupon = Coupon::create($couponData);
            
            // Attach outlets to coupon
            $coupon->outlets()->attach($outlets);
        }

        $this->command->info('Coupons seeded successfully!');
        $this->command->info('Total coupons created: ' . count($coupons));
        $this->command->info('');
        $this->command->info('========== SUMMARY BY TYPE ==========');
        $this->command->info('Percentage: ' . collect($coupons)->where('discount_type', 'percentage')->count());
        $this->command->info('Fixed Amount: ' . collect($coupons)->where('discount_type', 'fixed')->count());
        $this->command->info('Free Shipping: ' . collect($coupons)->where('discount_type', 'free_shipping')->count());
        $this->command->info('');
        $this->command->info('========== SUMMARY BY STATUS ==========');
        $this->command->info('Active: ' . collect($coupons)->where('is_active', true)->count());
        $this->command->info('Inactive: ' . collect($coupons)->where('is_active', false)->count());
        $this->command->info('Public: ' . collect($coupons)->where('is_public', true)->count());
        $this->command->info('Private/VIP: ' . collect($coupons)->where('is_public', false)->count());
        $this->command->info('=====================================');
    }
}