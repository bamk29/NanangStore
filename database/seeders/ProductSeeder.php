<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get unit IDs
        $pcs = Unit::where('code', 'PCS')->first()->id;
        $kg = Unit::where('code', 'KG')->first()->id;
        $ltr = Unit::where('code', 'LTR')->first()->id;
        $box = Unit::where('code', 'BOX')->first()->id;
        $sck = Unit::where('code', 'SCK')->first()->id;
        $pck = Unit::where('code', 'PCK')->first()->id;

        // Base wholesale discount (10%)
        $wholesaleDiscount = 0.9;

        // Get category IDs
        $categories = Category::pluck('id', 'name');

        $products = [
            // Beras & Biji-bijian
            [
                'name' => 'Beras Premium',
                'code' => 'BRS001',
                'category_id' => $categories['Beras & Biji-bijian'],
                'box_unit_id' => $sck,
                'unit_price' => 13000, // per kg
                'box_price' => 625000, // per karung 50kg
                'units_in_box' => 50,
                'unit_cost' => 11000,
                'box_cost' => 540000,
                'stock' => 500,
                'box_stock' => 10,
                'retail_price' => 13000,
                'wholesale_price' => 12000,
                'wholesale_min_qty' => 100, // minimal 100kg untuk harga grosir
                'cost_price' => 11000  // harga beli normal
            ],
            [
                'name' => 'Ayam Giling',
                'code' => 'AYM001',
                'category_id' => $categories['Ayam dan Giling Bakso'],
                'box_unit_id' => $box,
                'unit_price' => 13000, // per kg
                'box_price' => 625000, // per karung 50kg
                'units_in_box' => 50,
                'unit_cost' => 11000,
                'box_cost' => 540000,
                'stock' => 500,
                'box_stock' => 10,
                'retail_price' => 13000,
                'wholesale_price' => 12000,
                'wholesale_min_qty' => 100, // minimal 100kg untuk harga grosir
                'cost_price' => 11000  // harga beli normal
            ],
            [
                'name' => 'Beras Medium',
                'code' => 'BRS002',
                'category_id' => $categories['Beras & Biji-bijian'],
                'base_unit_id' => $kg,
                'box_unit_id' => $sck,
                'unit_price' => 11500,
                'box_price' => 550000,
                'units_in_box' => 50,
                'unit_cost' => 9500,
                'box_cost' => 465000,
                'stock' => 750,
                'box_stock' => 15,
                'retail_price' => 11500,
                'wholesale_price' => 10500,
                'wholesale_min_qty' => 100, // minimal 100kg untuk harga grosir
                'cost_price' => 9500  // harga beli normal
            ],

            // Minyak & Lemak
            [
                'name' => 'Minyak Goreng Premium 1L',
                'code' => 'MYK001',
                'category_id' => $categories['Minyak & Lemak'],
                'base_unit_id' => $pcs,
                'box_unit_id' => $box,
                'unit_price' => 23000,
                'box_price' => 264000,
                'units_in_box' => 12,
                'unit_cost' => 19000,
                'box_cost' => 228000,
                'stock' => 240,
                'box_stock' => 20,
                'retail_price' => 23000,
                'wholesale_price' => 21000,
                'wholesale_min_qty' => 24, // minimal 2 box untuk harga grosir
                'cost_price' => 19000  // harga beli normal
            ],

            // Gula & Pemanis
            [
                'name' => 'Gula Pasir 1kg',
                'code' => 'GLP001',
                'category_id' => $categories['Gula & Pemanis'],
                'base_unit_id' => $kg,
                'box_unit_id' => $box,
                'unit_price' => 16000,
                'box_price' => 380000,
                'units_in_box' => 25,
                'unit_cost' => 14000,
                'box_cost' => 350000,
                'stock' => 500,
                'box_stock' => 20,
                'retail_price' => 16000,
                'wholesale_price' => 15000,
                'wholesale_min_qty' => 50,
                'cost_price' => 14000
            ],

            // Tepung
            [
                'name' => 'Tepung Terigu Premium 1kg',
                'code' => 'TPG001',
                'category_id' => $categories['Tepung'],
                'base_unit_id' => $kg,
                'box_unit_id' => $box,
                'unit_price' => 15000,
                'box_price' => 350000,
                'units_in_box' => 25,
                'unit_cost' => 12500,
                'box_cost' => 312500,
                'stock' => 250,
                'box_stock' => 10,
                'retail_price' => 15000,
                'wholesale_price' => 14000,
                'wholesale_min_qty' => 50,
                'cost_price' => 12500
            ],

            // Telur & Susu
            [
                'name' => 'Telur Ayam',
                'code' => 'TLR001',
                'category_id' => $categories['Telur & Susu'],
                'base_unit_id' => $kg,
                'box_unit_id' => $box,
                'unit_price' => 28000,
                'box_price' => 840000,
                'units_in_box' => 30,
                'unit_cost' => 25000,
                'box_cost' => 750000,
                'stock' => 300,
                'box_stock' => 10,
                'retail_price' => 28000,
                'wholesale_price' => 26500,
                'wholesale_min_qty' => 30,
                'cost_price' => 25000
            ],

            // Mie & Pasta
            [
                'name' => 'Mie Instan Goreng',
                'code' => 'MIE001',
                'category_id' => $categories['Mie & Pasta'],
                'base_unit_id' => $pcs,
                'box_unit_id' => $box,
                'unit_price' => 3500,
                'box_price' => 168000,
                'units_in_box' => 48,
                'unit_cost' => 3000,
                'box_cost' => 144000,
                'stock' => 960,
                'box_stock' => 20,
                'retail_price' => 3500,
                'wholesale_price' => 3200,
                'wholesale_min_qty' => 96,
                'cost_price' => 3000
            ],

            // Minyak Goreng Curah
            [
                'name' => 'Minyak Goreng Curah',
                'code' => 'MYK002',
                'category_id' => $categories['Minyak & Lemak'],
                'base_unit_id' => $ltr,
                'box_unit_id' => $box,
                'unit_price' => 16000,
                'box_price' => 760000,
                'units_in_box' => 50,
                'unit_cost' => 14000,
                'box_cost' => 700000,
                'stock' => 200,
                'box_stock' => 4,
                'retail_price' => 16000,
                'wholesale_price' => 15000,
                'wholesale_min_qty' => 25,
                'cost_price' => 14000
            ],
            [
                'name' => 'Tepung Bakso',
                'code' => 'TPG002',
                'category_id' => $categories['Ayam dan Giling Bakso'],
                'base_unit_id' => $pcs,
                'box_unit_id' => $box,
                'unit_price' => 13000,
                'box_price' => 300000,
                'units_in_box' => 25,
                'unit_cost' => 11000,
                'box_cost' => 275000,
                'stock' => 200,
                'box_stock' => 8,
                'retail_price' => 13000,
                'wholesale_price' => 12000,
                'cost_price' => 11000
            ],
            // Tambahan untuk kategori Ayam & Bakso
            [
                'name' => 'Bawang Goreng',
                'code' => 'BUM001',
                'category_id' => $categories['Ayam dan Giling Bakso'],
                'base_unit_id' => $pcs,
                'box_unit_id' => $pck,
                'units_in_box' => 10,
                'unit_cost' => 2000,
                'box_cost' => 20000,
                'stock' => 100,
                'box_stock' => 10,
                'retail_price' => 2500,
                'wholesale_price' => 2200,
                'wholesale_min_qty' => 20,
                'cost_price' => 2000
            ],
            [
                'name' => 'Lada Bubuk Sachet',
                'code' => 'BUM002',
                'category_id' => $categories['Ayam dan Giling Bakso'],
                'base_unit_id' => $pcs,
                'box_unit_id' => $pck,
                'units_in_box' => 12,
                'unit_cost' => 700,
                'box_cost' => 8400,
                'stock' => 240,
                'box_stock' => 20,
                'retail_price' => 1000,
                'wholesale_price' => 800,

                'wholesale_min_qty' => 24,
                'cost_price' => 700
            ],
            [
                'name' => 'Penyedap Rasa Sapi',
                'code' => 'BUM003',
                'category_id' => $categories['Ayam dan Giling Bakso'],
                'base_unit_id' => $pcs,
                'box_unit_id' => $pck,
                'units_in_box' => 50,
                'unit_cost' => 350,
                'box_cost' => 17500,
                'stock' => 500,
                'box_stock' => 10,
                'retail_price' => 500,
                'wholesale_price' => 400,
                'wholesale_min_qty' => 100,
                'cost_price' => 350
            ],
            [
                'name' => 'Daun Bawang & Seledri Ikat',
                'code' => 'SAY001',
                'category_id' => $categories['Ayam dan Giling Bakso'],
                'base_unit_id' => $pcs, // Ikat
                'box_unit_id' => $pcs,
                'units_in_box' => 1,
                'unit_cost' => 1000,
                'box_cost' => 1000,
                'stock' => 50,
                'box_stock' => 50,
                'retail_price' => 2000,
                'wholesale_price' => 1500,
                'wholesale_min_qty' => 10,
                'cost_price' => 1000
            ],
        ];
         // Tepung

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
