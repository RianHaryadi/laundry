<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get outlets
        $outletPusat = Outlet::where('name', 'LaundryMate Pusat')->first();
        $outletBeji = Outlet::where('name', 'LaundryMate Beji')->first();

        // Jika outlet belum ada, buat dulu
        if (!$outletPusat) {
            $outletPusat = Outlet::create([
                'name' => 'LaundryMate Pusat',
                'address' => 'Jl. Margonda Raya No. 123, Pancoran Mas, Depok, Jawa Barat 16431',
                'phone' => '+62 812-3456-7890',
            ]);
        }

        if (!$outletBeji) {
            $outletBeji = Outlet::create([
                'name' => 'LaundryMate Beji',
                'address' => 'Jl. Raya Beji No. 45, Beji, Depok, Jawa Barat 16421',
                'phone' => '+62 812-3456-7891',
            ]);
        }

        $users = [
            // Owner (Akses ke semua outlet)
            [
                'name' => 'Owner LaundryMate',
                'email' => 'owner@laundrymate.com',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'outlet_id' => null, // Owner tidak terikat ke outlet tertentu
            ],

            // ==================== OUTLET PUSAT ====================
            
            // Admin Outlet Pusat
            [
                'name' => 'Admin Pusat',
                'email' => 'admin.pusat@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outletPusat->id,
            ],

            // Cashier Outlet Pusat (2 orang untuk shift)
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.cashier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'outlet_id' => $outletPusat->id,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.cashier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'outlet_id' => $outletPusat->id,
            ],

            // Courier Outlet Pusat (3 orang)
            [
                'name' => 'Ahmad Courier',
                'email' => 'ahmad.courier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletPusat->id,
            ],
            [
                'name' => 'Rudi Kurir',
                'email' => 'rudi.courier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletPusat->id,
            ],
            [
                'name' => 'Doni Delivery',
                'email' => 'doni.courier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletPusat->id,
            ],

            // ==================== OUTLET BEJI ====================
            
            // Admin Outlet Beji
            [
                'name' => 'Admin Beji',
                'email' => 'admin.beji@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outletBeji->id,
            ],

            // Cashier Outlet Beji (2 orang untuk shift)
            [
                'name' => 'Dewi Kasir',
                'email' => 'dewi.cashier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'outlet_id' => $outletBeji->id,
            ],
            [
                'name' => 'Rina Kasir',
                'email' => 'rina.cashier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'outlet_id' => $outletBeji->id,
            ],

            // Courier Outlet Beji (2 orang)
            [
                'name' => 'Agus Kurir',
                'email' => 'agus.courier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletBeji->id,
            ],
            [
                'name' => 'Yanto Courier',
                'email' => 'yanto.courier@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletBeji->id,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Total users created: ' . count($users));
        $this->command->info('');
        $this->command->info('========== LOGIN CREDENTIALS ==========');
        $this->command->info('Owner: owner@laundrymate.com / password');
        $this->command->info('Admin Pusat: admin.pusat@laundrymate.com / password');
        $this->command->info('Admin Beji: admin.beji@laundrymate.com / password');
        $this->command->info('All passwords: password');
        $this->command->info('=======================================');
    }
}