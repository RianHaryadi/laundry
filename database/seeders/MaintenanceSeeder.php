<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get machines
        $machines = Machine::all();
        
        if ($machines->isEmpty()) {
            $this->command->error('No machines found! Please run MachineSeeder first.');
            return;
        }

        // Get technicians (assuming users with technician role exist)
        // If you don't have technicians yet, create some dummy ones
        $technician1 = User::firstOrCreate(
            ['email' => 'technician1@laundrymate.com'],
            [
                'name' => 'Budi Santoso',
                'password' => bcrypt('password'),
            ]
        );

        $technician2 = User::firstOrCreate(
            ['email' => 'technician2@laundrymate.com'],
            [
                'name' => 'Ahmad Hidayat',
                'password' => bcrypt('password'),
            ]
        );

        $technician3 = User::firstOrCreate(
            ['email' => 'technician3@laundrymate.com'],
            [
                'name' => 'Dwi Prasetyo',
                'password' => bcrypt('password'),
            ]
        );

        $maintenances = [
            // ==================== PREVENTIVE MAINTENANCE ====================
            
            // Mesin Cuci Industrial A1
            [
                'machine_id' => Machine::where('serial_number', 'WM-2024-001')->first()?->id,
                'type' => 'preventive',
                'status' => 'completed',
                'priority' => 'medium',
                'description' => 'Perawatan rutin bulanan: Pembersihan filter, pengecekan seal pintu, pelumasan bearing, kalibrasi sensor air.',
                'issues_found' => 'Filter sedikit kotor namun masih dalam kondisi baik. Seal pintu normal.',
                'actions_taken' => 'Filter dibersihkan, bearing dilumasi dengan grease tahan air, sensor air dikalibrasi ulang.',
                'date' => Carbon::now()->subDays(30),
                'start_time' => '08:00:00',
                'end_time' => '10:30:00',
                'technician_id' => $technician1->id,
                'cost' => 350000,
                'cost_breakdown' => 'Grease bearing: Rp 150,000
Filter cleaning supplies: Rp 50,000
Labor (2.5 jam): Rp 150,000',
                'parts_replaced' => null,
                'materials_used' => 'Grease tahan air 500ml, Pembersih filter industrial, Kain microfiber',
                'next_maintenance_date' => Carbon::now()->addDays(60),
                'recommendations' => 'Kondisi mesin sangat baik. Maintenance berikutnya sesuai jadwal 90 hari.',
                'notes' => 'Mesin beroperasi optimal. Tidak ada masalah signifikan.',
            ],

            // Mesin Cuci Industrial A2
            [
                'machine_id' => Machine::where('serial_number', 'WM-2024-002')->first()?->id,
                'type' => 'preventive',
                'status' => 'completed',
                'priority' => 'medium',
                'description' => 'Maintenance preventif rutin: Pengecekan sistem elektrik, pembersihan drum, inspeksi hose dan connection.',
                'issues_found' => 'Hose inlet sedikit kendor, drum bersih, sistem elektrik normal.',
                'actions_taken' => 'Hose inlet dikencangkan, drum dibersihkan dengan descaler, koneksi dikencangkan.',
                'date' => Carbon::now()->subDays(32),
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'technician_id' => $technician1->id,
                'cost' => 300000,
                'cost_breakdown' => 'Descaler: Rp 100,000
Labor (2 jam): Rp 200,000',
                'parts_replaced' => null,
                'materials_used' => 'Descaler industrial 1L, Cleaning solution',
                'next_maintenance_date' => Carbon::now()->addDays(58),
                'recommendations' => 'Pertimbangkan ganti hose inlet dalam 6 bulan ke depan.',
                'notes' => 'Backup machine, usage rate rendah sehingga kondisi sangat baik.',
            ],

            // Mesin Cuci Delicate
            [
                'machine_id' => Machine::where('serial_number', 'WM-2024-003')->first()?->id,
                'type' => 'preventive',
                'status' => 'completed',
                'priority' => 'high',
                'description' => 'Perawatan khusus untuk mesin delicate: Pengecekan program delicate, kalibrasi temperature control, pembersihan sistem gentle wash.',
                'issues_found' => 'Temperature sensor sedikit drift dari setting optimal.',
                'actions_taken' => 'Temperature sensor dikalibrasi ulang, sistem gentle wash dibersihkan dan ditest.',
                'date' => Carbon::now()->subDays(45),
                'start_time' => '09:00:00',
                'end_time' => '11:30:00',
                'technician_id' => $technician2->id,
                'cost' => 500000,
                'cost_breakdown' => 'Kalibrasi sensor: Rp 200,000
Cleaning materials premium: Rp 100,000
Labor (2.5 jam): Rp 200,000',
                'parts_replaced' => null,
                'materials_used' => 'Premium cleaning solution for delicate machines, Calibration tools',
                'next_maintenance_date' => Carbon::now()->addDays(45),
                'recommendations' => 'Mesin premium, butuh maintenance lebih sering. Recommended setiap 90 hari.',
                'notes' => 'Mesin untuk handling premium garments, extra care diperlukan.',
            ],

            // ==================== CORRECTIVE MAINTENANCE ====================

            // Dryer Industrial D1 - Heating Element Issue
            [
                'machine_id' => Machine::where('serial_number', 'DR-2024-001')->first()?->id,
                'type' => 'corrective',
                'status' => 'completed',
                'priority' => 'high',
                'description' => 'Perbaikan heating element yang tidak mencapai temperature optimal. Customer complaint: pakaian tidak kering sempurna.',
                'issues_found' => 'Heating element kotor dengan lint dan debu, thermal sensor error, fan filter tersumbat.',
                'actions_taken' => 'Heating element dibersihkan total, thermal sensor diganti baru, fan filter dibersihkan dan dipasang kembali.',
                'date' => Carbon::now()->subDays(15),
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'technician_id' => $technician2->id,
                'cost' => 1250000,
                'cost_breakdown' => 'Thermal sensor baru: Rp 450,000
Cleaning materials: Rp 100,000
Labor (4 jam): Rp 700,000',
                'parts_replaced' => 'Thermal sensor unit (Part #: TS-5550-01)',
                'materials_used' => 'Lint removal tools, Industrial vacuum, Thermal paste',
                'next_maintenance_date' => Carbon::now()->addDays(75),
                'recommendations' => 'Lint filter harus dibersihkan setiap hari oleh operator untuk mencegah masalah serupa. Training operator diperlukan.',
                'notes' => 'Issue resolved. Dryer kembali beroperasi normal. Temperature stabil di 65°C.',
            ],

            // Dryer B2 - Motor Fan Failure
            [
                'machine_id' => Machine::where('serial_number', 'DR-2024-102')->first()?->id,
                'type' => 'corrective',
                'status' => 'in_progress',
                'priority' => 'critical',
                'description' => 'URGENT: Motor fan tidak berfungsi total. Dryer tidak bisa digunakan. Mesin mati total.',
                'issues_found' => 'Motor fan burnt out, bearing rusak, electrical connection ke motor putus.',
                'actions_taken' => 'Motor fan baru sudah dipesan. Sementara menunggu part datang. Estimated arrival 2 hari. Electrical connection akan diperbaiki saat penggantian motor.',
                'date' => Carbon::now()->subDays(3),
                'start_time' => '10:00:00',
                'end_time' => null,
                'technician_id' => $technician3->id,
                'cost' => 3500000,
                'cost_breakdown' => 'Motor fan baru: Rp 2,800,000
Bearing set: Rp 300,000
Labor (estimated 6 jam): Rp 400,000',
                'parts_replaced' => 'Motor fan (on order - Part #: MF-5350-MAIN)',
                'materials_used' => 'Diagnostic tools, Electrical tester',
                'next_maintenance_date' => Carbon::now()->addDays(87),
                'recommendations' => 'CRITICAL: Part masih dalam warranty. Claim warranty sudah diajukan. Setelah repair, perlu running test 24 jam.',
                'notes' => 'Mesin masih dalam warranty. Supplier akan cover biaya part. Outlet Beji menggunakan B1 dengan double shift sementara.',
            ],

            // ==================== EMERGENCY REPAIRS ====================

            // Steam Press Station 2 - Steam Valve Emergency
            [
                'machine_id' => Machine::where('serial_number', 'SP-2024-002')->first()?->id,
                'type' => 'emergency',
                'status' => 'completed',
                'priority' => 'critical',
                'description' => 'EMERGENCY: Steam valve bocor, uap panas keluar tidak terkontrol. Shutdown immediate untuk keselamatan operator.',
                'issues_found' => 'Steam valve seal rusak total, valve body retak kecil, pressure gauge tidak akurat.',
                'actions_taken' => 'Mesin di-shutdown immediate. Steam valve unit diganti total dengan unit baru. Pressure gauge dikalibrasi ulang. Safety test dilakukan sebelum operasi.',
                'date' => Carbon::now()->subDays(5),
                'start_time' => '14:30:00',
                'end_time' => '18:00:00',
                'technician_id' => $technician2->id,
                'cost' => 2100000,
                'cost_breakdown' => 'Steam valve assembly baru: Rp 1,200,000
Seal kit: Rp 300,000
Safety testing: Rp 200,000
Emergency labor (3.5 jam): Rp 400,000',
                'parts_replaced' => 'Steam valve assembly complete (Part #: SV-VT1000-01), Pressure gauge',
                'materials_used' => 'PTFE tape, Pipe sealant high temp, Safety testing equipment',
                'next_maintenance_date' => Carbon::now()->addDays(85),
                'recommendations' => 'SAFETY FIRST: Operator harus trained untuk recognisi tanda-tanda steam leak. Install pressure relief valve tambahan.',
                'notes' => 'Emergency handled dengan baik. No injuries. Mesin sudah aman untuk operasi. Safety inspection passed.',
            ],

            // ==================== INSPECTION ====================

            // Boiler Steam Generator - Monthly Safety Inspection
            [
                'machine_id' => Machine::where('serial_number', 'BL-2024-001')->first()?->id,
                'type' => 'inspection',
                'status' => 'completed',
                'priority' => 'critical',
                'description' => 'Inspeksi safety bulanan MANDATORY untuk boiler. Termasuk pressure test, safety valve test, water treatment check.',
                'issues_found' => 'Pressure gauge perlu kalibrasi minor, water hardness sedikit tinggi, safety valve OK.',
                'actions_taken' => 'Pressure gauge dikalibrasi, water softener chemical ditambah, safety valve ditest dan berfungsi normal.',
                'date' => Carbon::now()->subDays(15),
                'start_time' => '07:00:00',
                'end_time' => '09:30:00',
                'technician_id' => $technician1->id,
                'cost' => 800000,
                'cost_breakdown' => 'Kalibrasi pressure gauge: Rp 300,000
Water treatment chemicals: Rp 200,000
Safety inspection labor: Rp 300,000',
                'parts_replaced' => null,
                'materials_used' => 'Water treatment chemicals, Testing solutions, Calibration tools',
                'next_maintenance_date' => Carbon::now()->addDays(15),
                'recommendations' => 'CRITICAL EQUIPMENT: Monthly inspection adalah MANDATORY dan tidak bisa dilewati. Water hardness harus dimonitor weekly.',
                'notes' => 'Boiler dalam kondisi excellent. Safety systems semua berfungsi. Certificate valid untuk 30 hari.',
            ],

            // Boiler Beji - Monthly Inspection
            [
                'machine_id' => Machine::where('serial_number', 'BL-2024-101')->first()?->id,
                'type' => 'inspection',
                'status' => 'completed',
                'priority' => 'critical',
                'description' => 'Monthly safety inspection boiler outlet Beji. Full pressure test dan safety compliance check.',
                'issues_found' => 'Semua parameter normal. Water quality excellent. Safety systems berfungsi sempurna.',
                'actions_taken' => 'Routine inspection completed. Documentation updated. Safety certificate issued.',
                'date' => Carbon::now()->subDays(20),
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'technician_id' => $technician3->id,
                'cost' => 750000,
                'cost_breakdown' => 'Inspection fee: Rp 400,000
Water testing: Rp 150,000
Labor (2 jam): Rp 200,000',
                'parts_replaced' => null,
                'materials_used' => 'Testing equipment, Documentation materials',
                'next_maintenance_date' => Carbon::now()->addDays(10),
                'recommendations' => 'Continue current maintenance schedule. Boiler performing excellently.',
                'notes' => 'Outlet Beji boiler kondisi sangat baik. Operator sangat attentive terhadap daily checks.',
            ],

            // ==================== CALIBRATION ====================

            // Flatwork Ironer - Temperature Calibration
            [
                'machine_id' => Machine::where('serial_number', 'IR-2024-001')->first()?->id,
                'type' => 'calibration',
                'status' => 'completed',
                'priority' => 'high',
                'description' => 'Kalibrasi temperature control system. Customer complaint: beberapa linen ada bercak gosong.',
                'issues_found' => 'Temperature sensor drift +15°C dari setting. Roller pressure tidak merata.',
                'actions_taken' => 'Temperature sensor dikalibrasi ulang dengan precision tools. Roller pressure adjustment dilakukan. Test run dengan berbagai jenis fabric.',
                'date' => Carbon::now()->subDays(25),
                'start_time' => '06:00:00',
                'end_time' => '09:00:00',
                'technician_id' => $technician2->id,
                'cost' => 950000,
                'cost_breakdown' => 'Calibration service: Rp 500,000
Test materials: Rp 150,000
Labor (3 jam): Rp 300,000',
                'parts_replaced' => null,
                'materials_used' => 'Precision temperature calibration tools, Test fabrics, Pressure gauge',
                'next_maintenance_date' => Carbon::now()->addDays(35),
                'recommendations' => 'Temperature perlu dimonitor daily oleh operator. Calibration check setiap 2 bulan.',
                'notes' => 'Kalibrasi completed successfully. Test run passed. No more burnt marks.',
            ],

            // ==================== CLEANING ====================

            // Conveyor System - Deep Cleaning
            [
                'machine_id' => Machine::where('serial_number', 'CV-2024-001')->first()?->id,
                'type' => 'cleaning',
                'status' => 'scheduled',
                'priority' => 'medium',
                'description' => 'Deep cleaning scheduled untuk conveyor system. Pembersihan total belt, roller, dan motor area.',
                'issues_found' => null,
                'actions_taken' => null,
                'date' => Carbon::now()->addDays(7),
                'start_time' => '19:00:00',
                'end_time' => '23:00:00',
                'technician_id' => $technician1->id,
                'cost' => 1200000,
                'cost_breakdown' => 'Cleaning materials: Rp 400,000
Lubricants: Rp 300,000
Labor (4 jam off-hours): Rp 500,000',
                'parts_replaced' => null,
                'materials_used' => 'Industrial cleaners, Belt lubricant, Degreaser',
                'next_maintenance_date' => Carbon::now()->addDays(187),
                'recommendations' => 'Deep cleaning dijadwalkan saat off-hours untuk tidak mengganggu operasi.',
                'notes' => 'Scheduled untuk next week. Akan dilakukan malam hari.',
            ],

            // ==================== UPGRADE ====================

            // Vacuum Packing Machine - Software Upgrade
            [
                'machine_id' => Machine::where('serial_number', 'VP-2024-001')->first()?->id,
                'type' => 'upgrade',
                'status' => 'scheduled',
                'priority' => 'low',
                'description' => 'Software upgrade ke versi terbaru. Menambahkan fitur auto-detect thickness dan improved seal control.',
                'issues_found' => null,
                'actions_taken' => null,
                'date' => Carbon::now()->addDays(14),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'technician_id' => $technician2->id,
                'cost' => 500000,
                'cost_breakdown' => 'Software license: Rp 300,000
Installation & training: Rp 200,000',
                'parts_replaced' => null,
                'materials_used' => 'Software installation media, USB drive',
                'next_maintenance_date' => null,
                'recommendations' => 'Upgrade akan meningkatkan efficiency packing hingga 20%. Operator training included.',
                'notes' => 'Optional upgrade. Recommended untuk improve productivity.',
            ],

            // ==================== PENDING APPROVAL ====================

            // Washer B1 - Bearing Replacement Proposal
            [
                'machine_id' => Machine::where('serial_number', 'WM-2024-101')->first()?->id,
                'type' => 'corrective',
                'status' => 'pending',
                'priority' => 'medium',
                'description' => 'Proposal penggantian bearing drum. Terdeteksi suara noise abnormal saat spin cycle.',
                'issues_found' => 'Bearing menunjukkan tanda-tanda wear. Noise level meningkat. Belum critical tapi perlu action soon.',
                'actions_taken' => 'Diagnostic completed. Menunggu approval management untuk proceed dengan penggantian bearing.',
                'date' => Carbon::now()->addDays(3),
                'start_time' => null,
                'end_time' => null,
                'technician_id' => $technician3->id,
                'cost' => 2500000,
                'cost_breakdown' => 'Bearing set original: Rp 1,800,000
Seal kit: Rp 200,000
Labor (5 jam): Rp 500,000',
                'parts_replaced' => 'Main drum bearing set (pending approval)',
                'materials_used' => null,
                'next_maintenance_date' => null,
                'recommendations' => 'Recommended untuk dilakukan dalam 2 minggu. Jika ditunda, risk bearing failure meningkat yang bisa cause damage lebih besar.',
                'notes' => 'WAITING APPROVAL: Cost significant tapi necessary untuk prevent major breakdown. Machine still operational.',
            ],
        ];

        $created = 0;
        foreach ($maintenances as $maintenance) {
            if ($maintenance['machine_id']) {
                Maintenance::create($maintenance);
                $created++;
            }
        }

        $this->command->info('Maintenance records seeded successfully!');
        $this->command->info('Total maintenance records created: ' . $created);
        $this->command->info('');
        $this->command->info('========== SUMMARY BY TYPE ==========');
        $this->command->info('Preventive: ' . collect($maintenances)->where('type', 'preventive')->count());
        $this->command->info('Corrective: ' . collect($maintenances)->where('type', 'corrective')->count());
        $this->command->info('Emergency: ' . collect($maintenances)->where('type', 'emergency')->count());
        $this->command->info('Inspection: ' . collect($maintenances)->where('type', 'inspection')->count());
        $this->command->info('Calibration: ' . collect($maintenances)->where('type', 'calibration')->count());
        $this->command->info('Cleaning: ' . collect($maintenances)->where('type', 'cleaning')->count());
        $this->command->info('Upgrade: ' . collect($maintenances)->where('type', 'upgrade')->count());
        $this->command->info('');
        $this->command->info('========== SUMMARY BY STATUS ==========');
        $this->command->info('Completed: ' . collect($maintenances)->where('status', 'completed')->count());
        $this->command->info('In Progress: ' . collect($maintenances)->where('status', 'in_progress')->count());
        $this->command->info('Scheduled: ' . collect($maintenances)->where('status', 'scheduled')->count());
        $this->command->info('Pending: ' . collect($maintenances)->where('status', 'pending')->count());
        $this->command->info('=====================================');
    }
}