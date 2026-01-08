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
        // Get or create outlet
        $outlet = Outlet::firstOrCreate(
            ['name' => 'Rizki Laundry'],
            [
                'address' => 'Jl. Margonda Raya No. 123, Depok',
                'phone'   => '+62 812-3456-7890',
            ]
        );

        $users = [

            // ========== OWNER ==========
            [
                'name' => 'Rizki Maulana',
                'email' => 'owner.rizkilaundry@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'outlet_id' => null,
            ],

            // ========== ADMIN (2 user) ==========
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outlet->id,
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outlet->id,
            ],

            // ========== STAFF (3 user) ==========
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'outlet_id' => $outlet->id,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'outlet_id' => $outlet->id,
            ],
            [
                'name' => 'Rini Susanti',
                'email' => 'rini.susanti@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'outlet_id' => $outlet->id,
            ],

            // ========== COURIER (2 user) ==========
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outlet->id,
            ],
            [
                'name' => 'Doni Prasetyo',
                'email' => 'doni.prasetyo@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'outlet_id' => $outlet->id,
            ],

        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }

        $this->command->info("Users seeded successfully! Total created: " . count($users));
    }
}