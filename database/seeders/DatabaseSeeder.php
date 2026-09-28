<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@klinik.test'],
            ['name' => 'Administrator Klinik', 'password' => 'password', 'role' => User::ROLE_ADMIN, 'is_active' => true]
        );

        $medicines = [
            ['code' => 'OBT-PAR500', 'name' => 'Parasetamol 500 mg', 'unit' => 'tablet', 'stock' => 300, 'buy_price' => 300, 'sell_price' => 600],
            ['code' => 'OBT-AMX500', 'name' => 'Amoksisilin 500 mg', 'unit' => 'kapsul', 'stock' => 200, 'buy_price' => 900, 'sell_price' => 1800],
            ['code' => 'OBT-CET10',  'name' => 'Cetirizine 10 mg',   'unit' => 'tablet', 'stock' => 150, 'buy_price' => 500, 'sell_price' => 1000],
            ['code' => 'OBT-OMP20',  'name' => 'Omeprazole 20 mg',   'unit' => 'kapsul', 'stock' => 120, 'buy_price' => 1200, 'sell_price' => 2500],
            ['code' => 'OBT-VTC',    'name' => 'Vitamin C 50 mg',    'unit' => 'tablet', 'stock' => 400, 'buy_price' => 200, 'sell_price' => 400],
            ['code' => 'OBT-ORT',    'name' => 'Oralit 200 ml',      'unit' => 'sachet', 'stock' => 90,  'buy_price' => 1500, 'sell_price' => 3000],
        ];
        foreach ($medicines as $m) {
            Medicine::firstOrCreate(['code' => $m['code']], $m + ['is_active' => true]);
        }

        $services = [
            ['code' => 'SVC-KON',    'name' => 'Konsultasi Dokter Umum',  'category' => 'konsultasi',   'price' => 100000],
            ['code' => 'SVC-KON-SP', 'name' => 'Konsultasi Spesialis',    'category' => 'konsultasi',   'price' => 150000],
            ['code' => 'SVC-SUN',    'name' => 'Injeksi / Suntik',        'category' => 'tindakan',     'price' => 25000],
            ['code' => 'SVC-LAB',    'name' => 'Pemeriksaan Laboratorium Dasar', 'category' => 'laboratorium', 'price' => 75000],
        ];
        foreach ($services as $s) {
            Service::firstOrCreate(['code' => $s['code']], $s + ['is_active' => true]);
        }
    }
}