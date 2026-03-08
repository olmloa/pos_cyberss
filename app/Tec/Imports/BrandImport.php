<?php

namespace App\Tec\Imports;

use App\Models\Sma\Product\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Http\Requests\Sma\Product\BrandRequest;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BrandImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function model(array $row)
    {
        if (! ($row['slug'] ?? null)) {
            $row['slug'] = str($row['name'])->slug();
        }

        return new Brand([
            'name'        => $row['name'],
            'slug'        => $row['slug'],
            'order'       => $row['order'],
            'photo'       => $row['photo'],
            'active'      => $row['active'],
            'title'       => $row['title'],
            'description' => $row['description'],
        ]);
    }

    public function rules(): array
    {
        $rules = (new BrandRequest)->rules();
        $rules['name'] = 'required';
        $rules['slug'] = 'nullable';
        $rules['photo'] = 'nullable|url';

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
        return 'name';
    }
}
