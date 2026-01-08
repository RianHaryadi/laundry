<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [

            // ============================
            // LAYANAN PER KG
            // ============================

            [
                'name' => 'Cuci Kering (Wash Only)',
                'description' => 'Layanan cuci saja tanpa setrika',
                'pricing_type' => 'kg',
                'price_per_kg' => 5000,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Setrika (Wash & Iron)',
                'description' => 'Paket lengkap cuci dan setrika',
                'pricing_type' => 'kg',
                'price_per_kg' => 7000,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Setrika Saja (Iron Only)',
                'description' => 'Khusus setrika saja',
                'pricing_type' => 'kg',
                'price_per_kg' => 5000,
                'price_per_unit' => null,
                'duration_hours' => 12,
                'is_active' => true,
            ],

            // ============================
            // LAYANAN PER UNIT (Satuan)
            // ============================

            [
                'name' => 'Cuci Bed Cover (Per Unit)',
                'description' => 'Cuci bed cover per unit',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 20000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Sprei (Per Unit)',
                'description' => 'Cuci sprei per unit',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 15000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Sepatu (Per Unit)',
                'description' => 'Deep cleaning sepatu per pasang',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 25000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Jaket (Per Unit)',
                'description' => 'Cuci jaket per unit',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 20000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Tas (Per Unit)',
                'description' => 'Cuci tas dan ransel per unit',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 30000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Kebaya (Per Unit)',
                'description' => 'Cuci kebaya dengan perawatan khusus',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 35000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Jas (Per Unit)',
                'description' => 'Cuci jas dengan perawatan profesional',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 30000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Karpet (Per Unit)',
                'description' => 'Deep cleaning karpet per unit',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 50000,
                'duration_hours' => 72,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('Services seeded successfully!');
    }
}