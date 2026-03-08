<?php

namespace App\Tec\Imports;

use App\Models\Sma\Product\Unit;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Http\Requests\Sma\Product\ProductRequest;

class ProductImport implements PersistRelations, ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public $user;

    public $units;

    public $brands;

    public $categories;

    public function __construct()
    {
        $this->user = auth()->user();

        $this->units = Unit::all();
        $this->brands = Brand::all();
        $this->categories = Category::all();
    }

    public function model(array $row)
    {
        $row['unit_code'] ??= null;
        $row['sale_unit'] ??= null;
        $row['purchase_unit'] ??= null;
        $row['category_name'] ??= null;

        $unit = $this->units->where('code', $row['unit_code'])->first();
        $row['brand_id'] = $this->brands->where('name', $row['brand_name'])->first()?->id;
        $row['category_id'] = $this->categories->where('name', $row['category_name'])->first()?->id;
        $row['subcategory_id'] = $this->categories->where('name', $row['subcategory_name'])->first()?->id;

        $row['unit_id'] = $unit?->id;
        $row['sale_unit_id'] = $unit?->subunits?->where('name', $row['sale_unit'])->first()?->id;
        $row['purchase_unit_id'] = $unit?->subunits?->where('name', $row['purchase_unit'])->first()?->id;

        if ($row['has_variants'] == 'yes') {
            $variants = [];
            $variants_str = array_map('trim', explode('|', $row['variants']));
            foreach ($variants_str as $variant) {
                $variant = array_map('trim', explode(':', $variant));
                $variants[] = [
                    'name'    => $variant[0],
                    'options' => array_map('trim', explode(',', $variant[1])),
                ];
            }
            $row['variants'] = $variants;
        } else {
            $row['variants'] = null;
        }

        $row['supplier_id'] = $row['supplier_company'] ? Supplier::select(['id', 'name'])->where('company', $row['supplier_company'])->first()?->id : null;

        // $stores = $data['stores'] ?? null;
        // $serials = $data['serials'] ?? null;
        // $products = $data['products'] ?? null;
        // $variations = $data['variations'] ?? null;
        // $unit_prices = $data['unit_prices'] ?? null;

        $product = new Product([
            'type'             => $row['type'],
            'name'             => $row['name'],
            'secondary_name'   => $row['secondary_name'],
            'code'             => (string) $row['code'],
            'symbology'        => $row['symbology'],
            'category_id'      => $row['category_id'],
            'subcategory_id'   => $row['subcategory_id'],
            'brand_id'         => $row['brand_id'],
            'unit_id'          => $row['unit_id'],
            'sale_unit_id'     => $row['sale_unit_id'],
            'purchase_unit_id' => $row['purchase_unit_id'],

            'cost'         => (float) $row['cost'],
            'price'        => (float) $row['price'],
            'min_price'    => (float) $row['min_price'],
            'max_price'    => (float) $row['max_price'],
            'max_discount' => (float) $row['max_discount'],

            'hsn_number'    => $row['hsn_number'],
            'sac_number'    => $row['sac_number'],
            'weight'        => (float) $row['weight'],
            'dimensions'    => $row['dimensions'],
            'rack_location' => $row['rack_location'],

            'supplier_id'      => $row['supplier_id'],
            'supplier_part_id' => $row['supplier_part_id'],

            'featured'         => $row['featured'] == 'yes',
            'hide_in_pos'      => $row['hide_in_pos'] == 'yes',
            'hide_in_shop'     => $row['hide_in_shop'] == 'yes',
            'tax_included'     => $row['tax_included'] == 'yes',
            'has_serials'      => $row['has_serials'] == 'yes',
            'can_edit_price'   => $row['can_edit_price'] == 'yes',
            'has_expiry_date'  => $row['has_expiry_date'] == 'yes',
            'dont_track_stock' => $row['dont_track_stock'] == 'yes',

            'video_url'      => $row['video_url'],
            'alert_quantity' => $row['alert_quantity'],
            'has_variants'   => $row['has_variants'] == 'yes',
            'variants'       => $row['variants'], // ? json_encode($row['variants']) : null,
            'slug'           => $row['slug'],
            'title'          => $row['title'],
            'keywords'       => $row['keywords'],
            'noindex'        => $row['noindex'] == 'yes',
            'nofollow'       => $row['nofollow'] == 'yes',
            'description'    => $row['description'],
            'details'        => $row['details'],

            'sku' => ulid(),
        ]);

        if ($row['photo'] ?? null) {
            $product->photo = $row['photo'];
        }
        if ($row['photos'] ?? null) {
            $row['photos'] = array_map('trim', explode(',', $row['photos']));
            foreach ($row['photos'] as $key => $photo) {
                logger()->info('Set photos #' . $key . ' to ' . $photo);
            }
        }

        return $product;
    }

    public function rules(): array
    {
        $rules = (new ProductRequest)->rules();
        $rules['photo'] = 'nullable|url';
        $rules['photos'] = 'nullable';
        $rules['photos.*'] = 'nullable|url';

        $rules['brand_id'] = 'nullable';
        $rules['brand_name'] = 'nullable';

        $rules['category_id'] = 'nullable';
        $rules['category_name'] = 'required';

        $rules['unit_id'] = 'nullable';
        $rules['unit_code'] = 'nullable';

        // $rules['supplier_id'] = 'nullable';
        // $rules['supplier_company'] = 'required';

        $rules['featured'] = 'nullable';
        $rules['hide_in_pos'] = 'nullable';
        $rules['hide_in_shop'] = 'nullable';
        $rules['tax_included'] = 'nullable';
        $rules['has_serials'] = 'nullable';
        $rules['can_edit_price'] = 'nullable';
        $rules['has_expiry_date'] = 'nullable';
        $rules['dont_track_stock'] = 'nullable';
        $rules['has_variants'] = 'nullable';
        $rules['noindex'] = 'nullable';
        $rules['nofollow'] = 'nullable';

        $rules['variants'] = 'nullable';
        $rules['products'] = 'nullable';

        return $rules;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return ['code'];
    }
}
