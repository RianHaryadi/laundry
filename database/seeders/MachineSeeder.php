<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Machine;
use Carbon\Carbon;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = [
            // ============================
            // MESIN CUCI (WASHER)
            // ============================
            [
                'outlet_id' => 1,
                'name' => 'Washer A',
                'serial_number' => 'WS-001',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'LG',
                'model' => 'TurboWash 8kg',
                'purchase_date' => '2024-01-12',
                'purchase_price' => 3800000,
                'warranty_until' => '2026-01-12',
                'supplier' => 'PT Laundry Supply',
                'last_maintenance' => Carbon::now()->subDays(40),
                'maintenance_interval' => 90,
                'specifications' => 'Capacity 8kg, 220V, 500W',
                'notes' => 'Mesin utama untuk cuci kering dan cuci setrika',
            ],

            [
                'outlet_id' => 1,
                'name' => 'Washer B',
                'serial_number' => 'WS-002',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'Samsung',
                'model' => 'EcoBubble 7kg',
                'purchase_date' => '2024-02-02',
                'purchase_price' => 3500000,
                'warranty_until' => '2026-02-02',
                'supplier' => 'PT Laundry Supply',
                'last_maintenance' => Carbon::now()->subDays(75),
                'maintenance_interval' => 90,
                'specifications' => 'Capacity 7kg, EcoBubble, 450W',
                'notes' => 'Untuk cucian kiloan dan satuan (bed cover, sprei, jaket, tas, kebaya, jas)',
            ],

            // ============================
            // MESIN PENGERING (DRYER)
            // ============================
            [
                'outlet_id' => 1,
                'name' => 'Dryer A',
                'serial_number' => 'DR-001',
                'type' => 'dryer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'DryPro 6kg',
                'purchase_date' => '2023-10-01',
                'purchase_price' => 4200000,
                'warranty_until' => '2025-10-01',
                'supplier' => 'PT Mesin Laundry',
                'last_maintenance' => Carbon::now()->subDays(35),
                'maintenance_interval' => 90,
                'specifications' => 'Capacity 6kg, 220V, Heat Pump',
                'notes' => 'Untuk pengeringan semua jenis cucian',
            ],

            // ============================
            // SETRIKA (IRONER)
            // ============================
            [
                'outlet_id' => 1,
                'name' => 'Ironer Press',
                'serial_number' => 'IR-001',
                'type' => 'ironer',
                'status' => 'operational',
                'manufacturer' => 'Rinnai',
                'model' => 'SteamPress Mini',
                'purchase_date' => '2023-12-10',
                'purchase_price' => 2500000,
                'warranty_until' => '2025-12-10',
                'supplier' => 'PT Rinnai Distributor',
                'last_maintenance' => Carbon::now()->subDays(20),
                'maintenance_interval' => 60,
                'specifications' => 'Mini press, steam function',
                'notes' => 'Untuk setrika cucian kiloan dan satuan',
            ],

            [
                'outlet_id' => 1,
                'name' => 'Steam Iron',
                'serial_number' => 'IR-002',
                'type' => 'ironer',
                'status' => 'operational',
                'manufacturer' => 'Philips',
                'model' => 'PerfectCare',
                'purchase_date' => '2024-03-05',
                'purchase_price' => 1200000,
                'warranty_until' => '2026-03-05',
                'supplier' => 'PT Electronic Supply',
                'last_maintenance' => Carbon::now()->subDays(15),
                'maintenance_interval' => 60,
                'specifications' => 'Steam iron, 2000W',
                'notes' => 'Untuk finishing dan setrika detail (jas, kebaya)',
            ],
        ];

        foreach ($machines as $machine) {
            Machine::create($machine);
        }

        $this->command->info('Machines seeded successfully!');
        $this->command->info('Total machines created: ' . count($machines));
    }
}