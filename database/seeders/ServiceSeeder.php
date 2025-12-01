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
                'description' => 'Layanan cuci saja tanpa setrika...',
                'pricing_type' => 'kg',
                'price_per_kg' => 5000,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Setrika (Wash & Iron)',
                'description' => 'Paket lengkap cuci dan setrika...',
                'pricing_type' => 'kg',
                'price_per_kg' => 8000,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Setrika Premium',
                'description' => 'Layanan cuci premium...',
                'pricing_type' => 'kg',
                'price_per_kg' => 12000,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Setrika Saja (Iron Only)',
                'description' => 'Khusus setrika saja...',
                'pricing_type' => 'kg',
                'price_per_kg' => 4000,
                'price_per_unit' => null,
                'duration_hours' => 12,
                'is_active' => true,
            ],

            // Dry Clean per KG
            [
                'name' => 'Dry Clean (Per KG)',
                'description' => 'Dry cleaning untuk pakaian berbahan khusus...',
                'pricing_type' => 'kg',
                'price_per_kg' => 18000,
                'price_per_unit' => null,
                'duration_hours' => 48,
                'is_active' => true,
            ],

            // Premium Care per KG
            [
                'name' => 'Premium Care (Per KG)',
                'description' => 'Perawatan eksklusif...',
                'pricing_type' => 'kg',
                'price_per_kg' => 25000,
                'price_per_unit' => null,
                'duration_hours' => 48,
                'is_active' => true,
            ],

            [
                'name' => 'Cuci Karpet (Per KG)',
                'description' => 'Deep cleaning karpet...',
                'pricing_type' => 'kg',
                'price_per_kg' => 10000,
                'price_per_unit' => null,
                'duration_hours' => 72,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Gorden (Per KG)',
                'description' => 'Cuci gorden...',
                'pricing_type' => 'kg',
                'price_per_kg' => 10000,
                'price_per_unit' => null,
                'duration_hours' => 72,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Selimut & Bed Cover (Per KG)',
                'description' => 'Cuci selimut dan bed cover...',
                'pricing_type' => 'kg',
                'price_per_kg' => 8000,
                'price_per_unit' => null,
                'duration_hours' => 48,
                'is_active' => true,
            ],

            // Add-on KG
            [
                'name' => 'Layanan Parfum & Softener',
                'description' => 'Add-on parfum...',
                'pricing_type' => 'kg',
                'price_per_kg' => 3000,
                'price_per_unit' => null,
                'duration_hours' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Treatment Anti Bakteri',
                'description' => 'Add-on antibakteri...',
                'pricing_type' => 'kg',
                'price_per_kg' => 2000,
                'price_per_unit' => null,
                'duration_hours' => 0,
                'is_active' => true,
            ],

            [
                'name' => 'Paket Hemat Keluarga',
                'description' => 'Paket cuci setrika hemat...',
                'pricing_type' => 'kg',
                'price_per_kg' => 7000,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Paket Bulanan Kos',
                'description' => 'Paket rutin untuk kos...',
                'pricing_type' => 'kg',
                'price_per_kg' => 6500,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],
            [
                'name' => 'Laundry Corporate',
                'description' => 'Paket laundry perusahaan...',
                'pricing_type' => 'kg',
                'price_per_kg' => 5500,
                'price_per_unit' => null,
                'duration_hours' => 24,
                'is_active' => true,
            ],

            // ============================
            // LAYANAN PER UNIT (Satuan)
            // ============================

            [
                'name' => 'Dry Clean (Per Unit)',
                'description' => 'Dry cleaning item seperti jas, gaun...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 25000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Care (Per Unit)',
                'description' => 'Premium care untuk item mewah...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 35000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Sepatu',
                'description' => 'Deep cleaning sepatu...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 25000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Tas & Ransel',
                'description' => 'Cuci tas profesional...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 30000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Karpet (Per Unit)',
                'description' => 'Cuci karpet ukuran besar...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 50000,
                'duration_hours' => 72,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Gorden (Per Unit)',
                'description' => 'Cuci gorden ukuran besar...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 40000,
                'duration_hours' => 72,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Selimut & Bed Cover (Per Unit)',
                'description' => 'Cuci selimut dan bed cover ukuran besar...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 35000,
                'duration_hours' => 48,
                           'is_active' => true,
            ],
            [
                'name' => 'Cuci Boneka',
                'description' => 'Cuci boneka aman hypoallergenic...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 15000,
                'duration_hours' => 48,
                'is_active' => true,
            ],
            [
                'name' => 'Packing Vakum',
                'description' => 'Packing vakum untuk penyimpanan...',
                'pricing_type' => 'unit',
                'price_per_kg' => null,
                'price_per_unit' => 10000,
                'duration_hours' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('Services seeded successfully!');
    }
}
