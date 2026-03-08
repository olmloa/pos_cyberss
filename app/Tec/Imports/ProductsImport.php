<?php

namespace App\Tec\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsImport implements SkipsUnknownSheets, WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
            'Products'           => new ProductImport,
            'Product Taxes'      => new ProductTaxImport,
            'Product Variations' => new ProductVariationImport,
            'Combo Products'     => new ComboProductRowImport,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        logger()->info("Sheet {$sheetName} was skipped");
    }
}
