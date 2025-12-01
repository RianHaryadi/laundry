<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $outlets = [
            [
                'name' => 'LaundryMate Pusat',
                'address' => 'Jl. Margonda Raya No. 123, Pancoran Mas, Depok, Jawa Barat 16431',
                'phone' => '+62 812-3456-7890',
            ],
            [
                'name' => 'LaundryMate Beji',
                'address' => 'Jl. Raya Beji No. 45, Beji, Depok, Jawa Barat 16421',
                'phone' => '+62 812-3456-7891',
            ],
        ];

        foreach ($outlets as $outlet) {
            Outlet::create($outlet);
        }

        $this->command->info('Outlets seeded successfully! Total: ' . count($outlets) . ' outlets created.');
    }
}