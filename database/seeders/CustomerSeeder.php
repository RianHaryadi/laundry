<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for faster seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        Customer::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get all outlet IDs (if outlets exist)
        $outletIds = Outlet::pluck('id')->toArray();

        // Sample customer data
        $customers = [
            // VIP Customers (4000+ points)
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'phone' => '+62 812-3456-7890',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, 10220',
                'points' => 5500,
                'total_points_earned' => 5500,
                'membership_level' => 'vip',
                'member_since' => now()->subYears(2),
                'birthday' => now()->subYears(35)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => 'VIP customer, prefers almond milk for coffee',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@yahoo.com',
                'phone' => '+62 813-9876-5432',
                'address' => 'Jl. Gatot Subroto Kav. 52-53, Jakarta Selatan, 12950',
                'points' => 4200,
                'total_points_earned' => 6800,
                'membership_level' => 'vip',
                'member_since' => now()->subYears(3),
                'birthday' => now()->subYears(28)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => 'Loyal customer, often orders for office meetings',
            ],

            // Platinum Customers (2000-3999 points)
            [
                'name' => 'Ahmad Hidayat',
                'email' => 'ahmad.hidayat@hotmail.com',
                'phone' => '+62 821-1234-5678',
                'address' => 'Jl. Thamrin No. 45, Jakarta Pusat, 10350',
                'points' => 3200,
                'total_points_earned' => 3200,
                'membership_level' => 'platinum',
                'member_since' => now()->subYear(),
                'birthday' => now()->subYears(42)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => false,
                'notes' => 'No sugar in drinks',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@outlook.com',
                'phone' => '+62 822-8765-4321',
                'address' => 'Jl. Kuningan Raya No. 88, Jakarta Selatan, 12940',
                'points' => 2500,
                'total_points_earned' => 2500,
                'membership_level' => 'platinum',
                'member_since' => now()->subMonths(18),
                'birthday' => now()->subYears(31)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => null,
            ],

            // Gold Customers (1000-1999 points)
            [
                'name' => 'Rudi Hartono',
                'email' => 'rudi.hartono@gmail.com',
                'phone' => '+62 815-5555-1234',
                'address' => 'Jl. Rasuna Said Kav. 1-2, Jakarta Selatan, 12950',
                'points' => 1800,
                'total_points_earned' => 1800,
                'membership_level' => 'gold',
                'member_since' => now()->subMonths(14),
                'birthday' => now()->subYears(29)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => 'Prefers cold drinks',
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina.wijaya@yahoo.co.id',
                'phone' => '+62 816-7890-1234',
                'address' => 'Jl. Senopati Raya No. 100, Jakarta Selatan, 12190',
                'points' => 1350,
                'total_points_earned' => 1350,
                'membership_level' => 'gold',
                'member_since' => now()->subMonths(10),
                'birthday' => now()->subYears(26)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => false,
                'sms_notifications' => true,
                'notes' => null,
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@gmail.com',
                'phone' => '+62 817-2468-1357',
                'address' => 'Jl. Kemang Raya No. 25, Jakarta Selatan, 12730',
                'points' => 1100,
                'total_points_earned' => 1100,
                'membership_level' => 'gold',
                'member_since' => now()->subMonths(8),
                'birthday' => now()->subYears(33)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => false,
                'notes' => 'Allergic to nuts',
            ],

            // Silver Customers (500-999 points)
            [
                'name' => 'Ani Suryani',
                'email' => 'ani.suryani@hotmail.com',
                'phone' => '+62 818-3692-1478',
                'address' => 'Jl. Cilandak KKO No. 15, Jakarta Selatan, 12560',
                'points' => 850,
                'total_points_earned' => 850,
                'membership_level' => 'silver',
                'member_since' => now()->subMonths(7),
                'birthday' => now()->subYears(24)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => null,
            ],
            [
                'name' => 'Hendra Gunawan',
                'email' => 'hendra.gunawan@gmail.com',
                'phone' => '+62 819-7531-9246',
                'address' => 'Jl. Fatmawati Raya No. 77, Jakarta Selatan, 12420',
                'points' => 680,
                'total_points_earned' => 680,
                'membership_level' => 'silver',
                'member_since' => now()->subMonths(6),
                'birthday' => now()->subYears(38)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => false,
                'sms_notifications' => false,
                'notes' => null,
            ],
            [
                'name' => 'Maya Anggraini',
                'email' => 'maya.anggraini@yahoo.com',
                'phone' => '+62 851-2222-3333',
                'address' => 'Jl. Pejaten Raya No. 5, Jakarta Selatan, 12510',
                'points' => 550,
                'total_points_earned' => 550,
                'membership_level' => 'silver',
                'member_since' => now()->subMonths(5),
                'birthday' => now()->subYears(27)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => 'Prefers less sweet drinks',
            ],

            // Bronze Customers (0-499 points)
            [
                'name' => 'Fahmi Ramadhan',
                'email' => 'fahmi.ramadhan@gmail.com',
                'phone' => '+62 852-4444-5555',
                'address' => 'Jl. Tebet Raya No. 12, Jakarta Selatan, 12820',
                'points' => 350,
                'total_points_earned' => 350,
                'membership_level' => 'bronze',
                'member_since' => now()->subMonths(3),
                'birthday' => now()->subYears(22)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => null,
            ],
            [
                'name' => 'Linda Kartika',
                'email' => 'linda.kartika@outlook.com',
                'phone' => '+62 853-6666-7777',
                'address' => 'Jl. Kebayoran Lama No. 30, Jakarta Selatan, 12220',
                'points' => 200,
                'total_points_earned' => 200,
                'membership_level' => 'bronze',
                'member_since' => now()->subMonths(2),
                'birthday' => now()->subYears(30)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => false,
                'sms_notifications' => true,
                'notes' => null,
            ],
            [
                'name' => 'Yudi Setiawan',
                'email' => null,
                'phone' => '+62 854-8888-9999',
                'address' => 'Jl. Pondok Indah No. 88, Jakarta Selatan, 12310',
                'points' => 120,
                'total_points_earned' => 120,
                'membership_level' => 'bronze',
                'member_since' => now()->subMonth(),
                'birthday' => null,
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => false,
                'sms_notifications' => false,
                'notes' => null,
            ],
            [
                'name' => 'Putri Maharani',
                'email' => 'putri.maharani@gmail.com',
                'phone' => '+62 855-1111-2222',
                'address' => null,
                'points' => 50,
                'total_points_earned' => 50,
                'membership_level' => 'bronze',
                'member_since' => now()->subWeeks(2),
                'birthday' => now()->subYears(25)->format('Y-m-d'),
                'preferred_outlet_id' => null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => null,
            ],
            [
                'name' => 'Agus Salim',
                'email' => 'agus.salim@yahoo.co.id',
                'phone' => '+62 856-3333-4444',
                'address' => 'Jl. Pasar Minggu No. 99, Jakarta Selatan, 12780',
                'points' => 0,
                'total_points_earned' => 0,
                'membership_level' => 'bronze',
                'member_since' => now()->subDays(5),
                'birthday' => now()->subYears(40)->format('Y-m-d'),
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => false,
                'notes' => 'New customer',
            ],

            // Customers with birthday this month (for testing birthday features)
            [
                'name' => 'Birthday Customer 1',
                'email' => 'birthday1@example.com',
                'phone' => '+62 857-5555-6666',
                'address' => 'Jl. Birthday Street No. 1, Jakarta',
                'points' => 500,
                'total_points_earned' => 500,
                'membership_level' => 'silver',
                'member_since' => now()->subMonths(6),
                'birthday' => now()->format('Y-m-d'), // Birthday today!
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => 'Birthday is today!',
            ],
            [
                'name' => 'Birthday Customer 2',
                'email' => 'birthday2@example.com',
                'phone' => '+62 858-7777-8888',
                'address' => 'Jl. Birthday Street No. 2, Jakarta',
                'points' => 750,
                'total_points_earned' => 750,
                'membership_level' => 'silver',
                'member_since' => now()->subMonths(8),
                'birthday' => now()->addDays(5)->format('Y-m-d'), // Birthday in 5 days
                'preferred_outlet_id' => !empty($outletIds) ? $outletIds[array_rand($outletIds)] : null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'notes' => 'Birthday coming soon',
            ],
        ];

        // Insert all customers
        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('✅ Created ' . count($customers) . ' customers successfully!');
        
        // Display statistics
        $this->command->info('');
        $this->command->info('📊 Customer Statistics:');
        $this->command->info('VIP: ' . Customer::where('membership_level', 'vip')->count());
        $this->command->info('Platinum: ' . Customer::where('membership_level', 'platinum')->count());
        $this->command->info('Gold: ' . Customer::where('membership_level', 'gold')->count());
        $this->command->info('Silver: ' . Customer::where('membership_level', 'silver')->count());
        $this->command->info('Bronze: ' . Customer::where('membership_level', 'bronze')->count());
        $this->command->info('');
        $this->command->info('🎂 Birthdays today: ' . Customer::whereMonth('birthday', now()->month)->whereDay('birthday', now()->day)->count());
        $this->command->info('🎂 Birthdays this month: ' . Customer::whereMonth('birthday', now()->month)->count());
    }
}