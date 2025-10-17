<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Supplier::create([
            'name' => 'PT. Sinar Jaya Abadi',
            'phone' => '081234567890',
            'email' => 'info@sinarjaya.com',
            'address' => 'Jl. Industri Raya No. 1, Jakarta'
        ]);

        \App\Models\Supplier::create([
            'name' => 'CV. Mitra Pangan',
            'phone' => '082345678901',
            'email' => 'order@mitrapangan.co.id',
            'address' => 'Jl. Pergudangan No. 2, Surabaya'
        ]);

        \App\Models\Supplier::create([
            'name' => 'Toko Grosir Berkah',
            'phone' => '083456789012',
            'email' => 'grosirberkah@gmail.com',
            'address' => 'Jl. Pasar Induk No. 3, Bandung'
        ]);
    }
}
