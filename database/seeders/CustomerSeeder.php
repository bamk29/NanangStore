<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Wak Lentol',
                'phone' => '081234567890',
                'points' => 150,
                'debt' => 50000,
            ],
            [
                'name' => 'Bu Sri',
                'phone' => '081298765432',
                'points' => 75,
                'debt' => 0,
            ],
            [
                'name' => 'Warung Pak Budi',
                'phone' => '085611223344',
                'points' => 320,
                'debt' => 250000,
            ],
            [
                'name' => 'Catering Bu Ida',
                'phone' => '087855667788',
                'points' => 50,
                'debt' => 0,
            ],
            [
                'name' => 'Pelanggan Umum',
                'phone' => null,
                'points' => 0,
                'debt' => 0,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
