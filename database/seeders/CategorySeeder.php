<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [

            ['name' => 'Ayam dan Giling Bakso', 'description' => 'Daging Ayam dan Giling Bakso'],
            ['name' => 'Beras & Biji-bijian', 'description' => 'Beras dan berbagai jenis biji-bijian'],
            ['name' => 'Minyak & Lemak', 'description' => 'Minyak goreng dan produk lemak'],
            ['name' => 'Gula & Pemanis', 'description' => 'Gula pasir dan pemanis lainnya'],
            ['name' => 'Tepung', 'description' => 'Berbagai jenis tepung'],
            ['name' => 'Telur & Susu', 'description' => 'Telur dan produk susu'],
            ['name' => 'Bumbu Dapur', 'description' => 'Bumbu-bumbu masakan'],
            ['name' => 'Mie & Pasta', 'description' => 'Mie instan dan pasta'],
            ['name' => 'Kacang-kacangan', 'description' => 'Berbagai jenis kacang'],
            ['name' => 'Makanan Kaleng', 'description' => 'Makanan dalam kemasan kaleng'],
            ['name' => 'Minuman', 'description' => 'Minuman dan serbuk minuman'],
            ['name' => 'Ayam potong dan Giling bakso', 'description' => 'Produk untuk toko kedua'],
            ['name' => 'Kebutuhan Dapur', 'description' => 'Peralatan dan perlengkapan dapur'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
