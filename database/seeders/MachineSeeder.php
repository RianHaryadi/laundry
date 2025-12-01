<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get outlets
        $outletPusat = Outlet::where('name', 'LaundryMate Pusat')->first();
        $outletBeji = Outlet::where('name', 'LaundryMate Beji')->first();

        // Jika outlet belum ada, skip
        if (!$outletPusat || !$outletBeji) {
            $this->command->error('Outlets not found! Please run OutletSeeder first.');
            return;
        }

        $machines = [
            // ==================== OUTLET PUSAT ====================
            
            // Mesin Cuci Industrial
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Mesin Cuci Industrial A1',
                'serial_number' => 'WM-2024-001',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'W5180H',
                'purchase_date' => Carbon::now()->subMonths(18),
                'purchase_price' => 85000000,
                'warranty_until' => Carbon::now()->addMonths(6),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(30),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 18 kg, Kecepatan spin: 1000 rpm, Power: 5.5 kW, Dimensi: 850x900x1200 mm',
                'notes' => 'Mesin cuci utama untuk beban berat. Digunakan untuk cucian regular dan express.',
            ],
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Mesin Cuci Industrial A2',
                'serial_number' => 'WM-2024-002',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'W5180H',
                'purchase_date' => Carbon::now()->subMonths(18),
                'purchase_price' => 85000000,
                'warranty_until' => Carbon::now()->addMonths(6),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(32),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 18 kg, Kecepatan spin: 1000 rpm, Power: 5.5 kW, Dimensi: 850x900x1200 mm',
                'notes' => 'Backup mesin cuci utama. Kondisi sangat baik.',
            ],
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Mesin Cuci Khusus Delicate',
                'serial_number' => 'WM-2024-003',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'Miele',
                'model' => 'PW 6137',
                'purchase_date' => Carbon::now()->subMonths(12),
                'purchase_price' => 95000000,
                'warranty_until' => Carbon::now()->addMonths(12),
                'supplier' => 'PT Premium Laundry Solutions',
                'last_maintenance' => Carbon::now()->subDays(45),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 13 kg, Program khusus untuk bahan delicate, wool, silk. Power: 4.2 kW',
                'notes' => 'Khusus untuk dry clean dan premium care. Handle with care.',
            ],

            // Mesin Pengering
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Dryer Industrial D1',
                'serial_number' => 'DR-2024-001',
                'type' => 'dryer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'T5550',
                'purchase_date' => Carbon::now()->subMonths(18),
                'purchase_price' => 75000000,
                'warranty_until' => Carbon::now()->addMonths(6),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(28),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 20 kg, Heat pump system, Power: 6 kW, Dimensi: 850x1000x1300 mm',
                'notes' => 'Dryer utama dengan teknologi heat pump untuk efisiensi energi.',
            ],
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Dryer Industrial D2',
                'serial_number' => 'DR-2024-002',
                'type' => 'dryer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'T5550',
                'purchase_date' => Carbon::now()->subMonths(18),
                'purchase_price' => 75000000,
                'warranty_until' => Carbon::now()->addMonths(6),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(30),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 20 kg, Heat pump system, Power: 6 kW, Dimensi: 850x1000x1300 mm',
                'notes' => 'Backup dryer. Kondisi excellent.',
            ],

            // Mesin Setrika
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Flatwork Ironer Pro',
                'serial_number' => 'IR-2024-001',
                'type' => 'ironer',
                'status' => 'operational',
                'manufacturer' => 'Chicago',
                'model' => 'FI-130',
                'purchase_date' => Carbon::now()->subMonths(24),
                'purchase_price' => 120000000,
                'warranty_until' => Carbon::now()->subMonths(12), // Already expired
                'supplier' => 'PT Industrial Laundry Indonesia',
                'last_maintenance' => Carbon::now()->subDays(60),
                'maintenance_interval' => 60,
                'specifications' => 'Roller width: 130 cm, Speed: 0-5 m/min, Temperature: max 230°C, Power: 15 kW',
                'notes' => 'Mesin setrika profesional untuk linen besar seperti bed cover, gorden, dll.',
            ],
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Steam Press Station 1',
                'serial_number' => 'SP-2024-001',
                'type' => 'ironer',
                'status' => 'operational',
                'manufacturer' => 'Pony',
                'model' => 'VT-1000',
                'purchase_date' => Carbon::now()->subMonths(15),
                'purchase_price' => 35000000,
                'warranty_until' => Carbon::now()->addMonths(9),
                'supplier' => 'CV Laundry Sukses',
                'last_maintenance' => Carbon::now()->subDays(40),
                'maintenance_interval' => 90,
                'specifications' => 'Steam pressure: 4.5 bar, Temperature: 160°C, Power: 3.5 kW',
                'notes' => 'Untuk setrika pakaian delicate dan premium.',
            ],
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Steam Press Station 2',
                'serial_number' => 'SP-2024-002',
                'type' => 'ironer',
                'status' => 'maintenance',
                'manufacturer' => 'Pony',
                'model' => 'VT-1000',
                'purchase_date' => Carbon::now()->subMonths(15),
                'purchase_price' => 35000000,
                'warranty_until' => Carbon::now()->addMonths(9),
                'supplier' => 'CV Laundry Sukses',
                'last_maintenance' => Carbon::now()->subDays(5),
                'maintenance_interval' => 90,
                'specifications' => 'Steam pressure: 4.5 bar, Temperature: 160°C, Power: 3.5 kW',
                'notes' => 'Sedang maintenance rutin. Steam valve diganti.',
            ],

            // Peralatan Pendukung
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Boiler Steam Generator',
                'serial_number' => 'BL-2024-001',
                'type' => 'boiler',
                'status' => 'operational',
                'manufacturer' => 'Vaporax',
                'model' => 'VS-200',
                'purchase_date' => Carbon::now()->subMonths(20),
                'purchase_price' => 65000000,
                'warranty_until' => Carbon::now()->addMonths(4),
                'supplier' => 'PT Steam Indonesia',
                'last_maintenance' => Carbon::now()->subDays(15),
                'maintenance_interval' => 30,
                'specifications' => 'Kapasitas: 200 kg/h, Pressure: 8 bar, Power: 150 kW, Fuel: Gas LPG',
                'notes' => 'Boiler utama untuk supply steam ke semua mesin. CRITICAL EQUIPMENT - maintenance ketat setiap bulan.',
            ],
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Conveyor System',
                'serial_number' => 'CV-2024-001',
                'type' => 'conveyor',
                'status' => 'operational',
                'manufacturer' => 'Jensen',
                'model' => 'C-3000',
                'purchase_date' => Carbon::now()->subMonths(24),
                'purchase_price' => 45000000,
                'warranty_until' => Carbon::now()->subMonths(12),
                'supplier' => 'PT Automation Laundry',
                'last_maintenance' => Carbon::now()->subDays(90),
                'maintenance_interval' => 180,
                'specifications' => 'Length: 30 meter, Load capacity: 200 kg, Speed: variable 0-2 m/s, Power: 2.5 kW',
                'notes' => 'Sistem conveyor otomatis untuk distribusi pakaian antar area. Sudah waktunya maintenance.',
            ],

            // ==================== OUTLET BEJI ====================
            
            // Mesin Cuci
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Mesin Cuci Industrial B1',
                'serial_number' => 'WM-2024-101',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'W5130H',
                'purchase_date' => Carbon::now()->subMonths(10),
                'purchase_price' => 72000000,
                'warranty_until' => Carbon::now()->addMonths(14),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(25),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 13 kg, Kecepatan spin: 900 rpm, Power: 4.5 kW, Dimensi: 800x850x1150 mm',
                'notes' => 'Mesin cuci utama outlet Beji. Kondisi prima.',
            ],
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Mesin Cuci Industrial B2',
                'serial_number' => 'WM-2024-102',
                'type' => 'washer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'W5130H',
                'purchase_date' => Carbon::now()->subMonths(10),
                'purchase_price' => 72000000,
                'warranty_until' => Carbon::now()->addMonths(14),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(27),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 13 kg, Kecepatan spin: 900 rpm, Power: 4.5 kW, Dimensi: 800x850x1150 mm',
                'notes' => 'Backup mesin cuci. Usage rendah.',
            ],

            // Mesin Pengering
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Dryer Industrial B1',
                'serial_number' => 'DR-2024-101',
                'type' => 'dryer',
                'status' => 'operational',
                'manufacturer' => 'Electrolux',
                'model' => 'T5350',
                'purchase_date' => Carbon::now()->subMonths(10),
                'purchase_price' => 65000000,
                'warranty_until' => Carbon::now()->addMonths(14),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(24),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 15 kg, Gas heating, Power: 5 kW, Dimensi: 800x950x1250 mm',
                'notes' => 'Dryer utama outlet Beji.',
            ],
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Dryer Industrial B2',
                'serial_number' => 'DR-2024-102',
                'type' => 'dryer',
                'status' => 'broken',
                'manufacturer' => 'Electrolux',
                'model' => 'T5350',
                'purchase_date' => Carbon::now()->subMonths(10),
                'purchase_price' => 65000000,
                'warranty_until' => Carbon::now()->addMonths(14),
                'supplier' => 'PT Laundry Equipment Indonesia',
                'last_maintenance' => Carbon::now()->subDays(120),
                'maintenance_interval' => 90,
                'specifications' => 'Kapasitas: 15 kg, Gas heating, Power: 5 kW, Dimensi: 800x950x1250 mm',
                'notes' => 'RUSAK - Motor fan tidak berfungsi. Sudah request service technician. Target perbaikan 3 hari.',
            ],

            // Mesin Setrika
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Steam Press Station B1',
                'serial_number' => 'SP-2024-101',
                'type' => 'ironer',
                'status' => 'operational',
                'manufacturer' => 'Pony',
                'model' => 'VT-800',
                'purchase_date' => Carbon::now()->subMonths(8),
                'purchase_price' => 28000000,
                'warranty_until' => Carbon::now()->addMonths(16),
                'supplier' => 'CV Laundry Sukses',
                'last_maintenance' => Carbon::now()->subDays(35),
                'maintenance_interval' => 90,
                'specifications' => 'Steam pressure: 4 bar, Temperature: 150°C, Power: 3 kW',
                'notes' => 'Setrika utama outlet Beji.',
            ],
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Steam Press Station B2',
                'serial_number' => 'SP-2024-102',
                'type' => 'ironer',
                'status' => 'operational',
                'manufacturer' => 'Pony',
                'model' => 'VT-800',
                'purchase_date' => Carbon::now()->subMonths(8),
                'purchase_price' => 28000000,
                'warranty_until' => Carbon::now()->addMonths(16),
                'supplier' => 'CV Laundry Sukses',
                'last_maintenance' => Carbon::now()->subDays(38),
                'maintenance_interval' => 90,
                'specifications' => 'Steam pressure: 4 bar, Temperature: 150°C, Power: 3 kW',
                'notes' => 'Setrika backup.',
            ],

            // Peralatan Pendukung
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Boiler Steam Generator B',
                'serial_number' => 'BL-2024-101',
                'type' => 'boiler',
                'status' => 'operational',
                'manufacturer' => 'Vaporax',
                'model' => 'VS-100',
                'purchase_date' => Carbon::now()->subMonths(12),
                'purchase_price' => 42000000,
                'warranty_until' => Carbon::now()->addMonths(12),
                'supplier' => 'PT Steam Indonesia',
                'last_maintenance' => Carbon::now()->subDays(20),
                'maintenance_interval' => 30,
                'specifications' => 'Kapasitas: 100 kg/h, Pressure: 6 bar, Power: 100 kW, Fuel: Gas LPG',
                'notes' => 'Boiler outlet Beji. CRITICAL EQUIPMENT - cek pressure harian.',
            ],

            // Packaging & Quality Control
            [
                'outlet_id' => $outletPusat->id,
                'name' => 'Vacuum Packing Machine',
                'serial_number' => 'VP-2024-001',
                'type' => 'packing',
                'status' => 'operational',
                'manufacturer' => 'Henkovac',
                'model' => 'Titan 520',
                'purchase_date' => Carbon::now()->subMonths(6),
                'purchase_price' => 18000000,
                'warranty_until' => Carbon::now()->addMonths(18),
                'supplier' => 'PT Packaging Solutions',
                'last_maintenance' => Carbon::now()->subDays(45),
                'maintenance_interval' => 180,
                'specifications' => 'Chamber size: 520mm, Vacuum pump: 20 m³/h, Seal bar: 460mm, Power: 1.2 kW',
                'notes' => 'Untuk packing vakum pakaian musiman dan storage.',
            ],
            [
                'outlet_id' => $outletBeji->id,
                'name' => 'Vacuum Packing Machine B',
                'serial_number' => 'VP-2024-101',
                'type' => 'packing',
                'status' => 'operational',
                'manufacturer' => 'Henkovac',
                'model' => 'Titan 420',
                'purchase_date' => Carbon::now()->subMonths(4),
                'purchase_price' => 15000000,
                'warranty_until' => Carbon::now()->addMonths(20),
                'supplier' => 'PT Packaging Solutions',
                'last_maintenance' => Carbon::now()->subDays(30),
                'maintenance_interval' => 180,
                'specifications' => 'Chamber size: 420mm, Vacuum pump: 16 m³/h, Seal bar: 380mm, Power: 1 kW',
                'notes' => 'Untuk layanan packing vakum outlet Beji.',
            ],
        ];

        foreach ($machines as $machine) {
            Machine::create($machine);
        }

        $this->command->info('Machines seeded successfully!');
        $this->command->info('Total machines created: ' . count($machines));
        $this->command->info('');
        $this->command->info('========== SUMMARY ==========');
        $this->command->info('LaundryMate Pusat: ' . collect($machines)->where('outlet_id', $outletPusat->id)->count() . ' machines');
        $this->command->info('LaundryMate Beji: ' . collect($machines)->where('outlet_id', $outletBeji->id)->count() . ' machines');
        $this->command->info('============================');
    }
}