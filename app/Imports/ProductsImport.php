<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            // Skip empty rows or rows without a code/name
            if (empty($row['code']) && empty($row['name'])) {
                continue;
            }

            // Look up Category ID
            $category_id = null;
            if (!empty($row['category_name'])) {
                $category = Category::where('name', $row['category_name'])->first();
                if ($category) {
                    $category_id = $category->id;
                }
            }

            // Look up Supplier ID
            $supplier_id = null;
            if (!empty($row['supplier_name'])) {
                $supplier = Supplier::where('name', $row['supplier_name'])->first();
                if ($supplier) {
                    $supplier_id = $supplier->id;
                }
            }

            // Look up Base Unit ID
            $base_unit_id = null;
            if (!empty($row['base_unit_name'])) {
                $baseUnit = Unit::where('name', $row['base_unit_name'])->first();
                if ($baseUnit) {
                    $base_unit_id = $baseUnit->id;
                }
            }

            // Look up Box Unit ID
            $box_unit_id = null;
            if (!empty($row['box_unit_name'])) {
                $boxUnit = Unit::where('name', $row['box_unit_name'])->first();
                if ($boxUnit) {
                    $box_unit_id = $boxUnit->id;
                }
            }

            // Find product by code OR name
            $product = Product::where('code', $row['code'])
                              ->orWhere('name', $row['name'])
                              ->first();

            $data = [
                'code'              => $row['code'] ?? null,
                'name'              => $row['name'] ?? null,
                'description'       => $row['description'] ?? null,
                'stock'             => $row['stock'] ?? 0,
                'retail_price'      => $row['retail_price'] ?? 0,
                'wholesale_price'   => $row['wholesale_price'] ?? 0,
                'wholesale_min_qty' => $row['wholesale_min_qty'] ?? 0,
                'cost_price'        => $row['cost_price'] ?? 0,
                'box_cost'          => $row['box_cost'] ?? 0,
                'units_in_box'      => $row['units_in_box'] ?? 1,
                'category_id'       => $category_id,
                'supplier_id'       => $supplier_id,
                'base_unit_id'      => $base_unit_id,
                'box_unit_id'       => $box_unit_id,
            ];

            if ($product) {
                // Update existing product
                $product->update($data);
            } else {
                // Create new product
                Product::create($data);
            }
        }
    }
}
