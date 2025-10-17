<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'code' => 'PCS', 'description' => 'Satuan per item'],
            ['name' => 'Box', 'code' => 'BOX', 'description' => 'Kemasan box/dus'],
            ['name' => 'Karton', 'code' => 'KRT', 'description' => 'Kemasan karton'],
            ['name' => 'Pack', 'code' => 'PCK', 'description' => 'Kemasan pack'],
            ['name' => 'Renteng', 'code' => 'RTG', 'description' => 'Kemasan renteng'],
            ['name' => 'Kilogram', 'code' => 'KG', 'description' => 'Berat dalam kilogram'],
            ['name' => 'Gram', 'code' => 'GR', 'description' => 'Berat dalam gram'],
            ['name' => 'Liter', 'code' => 'LTR', 'description' => 'Volume dalam liter'],
            ['name' => 'Mililiter', 'code' => 'ML', 'description' => 'Volume dalam mililiter'],
            ['name' => 'Lusin', 'code' => 'LSN', 'description' => 'Satuan lusin (12 pieces)'],
            ['name' => 'Sack', 'code' => 'SCK', 'description' => 'Kemasan karung'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
