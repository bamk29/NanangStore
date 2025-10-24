<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductsDataSheet(), // Main product data sheet
            new CategoriesExport(),
            new SuppliersExport(),
            new UnitsExport(),
        ];
    }
}

// Create a new class for the main product data sheet
class ProductsDataSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Product::with(['category', 'supplier', 'baseUnit', 'boxUnit'])->get()->map(function ($product) {
            return [
                'code'              => $product->code,
                'name'              => $product->name,
                'description'       => $product->description,
                'stock'             => $product->stock,
                'retail_price'      => $product->retail_price,
                'wholesale_price'   => $product->wholesale_price,
                'wholesale_min_qty' => $product->wholesale_min_qty,
                'cost_price'        => $product->cost_price,
                'box_cost'          => $product->box_cost, // Added
                'units_in_box'      => $product->units_in_box,
                'category_name'     => $product->category->name ?? null,
                'supplier_name'     => $product->supplier->name ?? null,
                'base_unit_name'    => $product->baseUnit->name ?? null,
                'box_unit_name'     => $product->boxUnit->name ?? null,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'code',
            'name',
            'description',
            'stock',
            'retail_price',
            'wholesale_price',
            'wholesale_min_qty',
            'cost_price',
            'box_cost',
            'units_in_box',
            'category_name',
            'supplier_name',
            'base_unit_name',
            'box_unit_name',
        ];
    }

    public function title(): string
    {
        return 'Produk';
    }
}
