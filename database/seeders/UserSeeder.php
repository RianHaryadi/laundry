<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create outlets
        $outletPusat = Outlet::firstOrCreate(
            ['name' => 'LaundryMate Pusat'],
            [
                'address' => 'Jl. Margonda Raya No. 123, Depok',
                'phone'   => '+62 812-3456-7890',
            ]
        );

        $outletBeji = Outlet::firstOrCreate(
            ['name' => 'LaundryMate Beji'],
            [
                'address' => 'Jl. Raya Beji No. 45, Depok',
                'phone'   => '+62 812-3456-7891',
            ]
        );

        $users = [

            // ========== OWNER ==========
            [
                'name' => 'Owner LaundryMate',
                'email' => 'owner@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'outlet_id' => null,
            ],

            // ========== OUTLET PUSAT (3 user) ==========
            [
                'name' => 'Admin Pusat',
                'email' => 'admin.pusat@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outletPusat->id,
            ],
            [
                'name' => 'Staff Pusat',
                'email' => 'staff.pusat@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'outlet_id' => $outletPusat->id,
            ],
            [
                'name' => 'Courier Pusat',
                'email' => 'courier.pusat@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletPusat->id,
            ],

            // ========== OUTLET BEJI (3 user) ==========
            [
                'name' => 'Admin Beji',
                'email' => 'admin.beji@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outletBeji->id,
            ],
            [
                'name' => 'Staff Beji',
                'email' => 'staff.beji@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'outlet_id' => $outletBeji->id,
            ],
            [
                'name' => 'Courier Beji',
                'email' => 'courier.beji@laundrymate.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outletBeji->id,
            ],

        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }

        $this->command->info("Users seeded successfully! Total created: " . count($users));
    }
}
