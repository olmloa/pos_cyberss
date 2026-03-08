<?php

namespace App\Tec\Imports;

use App\Models\Sma\Product\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Http\Requests\Sma\Product\CategoryRequest;

class CategoryImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function model(array $row)
    {
        $row['category_id'] = null;
        if ($row['parent_category'] ?? null) {
            $row['category_id'] = Category::where('name', $row['parent_category'])->first()?->id;
        }

        if (! ($row['slug'] ?? null)) {
            $row['slug'] = str($row['name'])->slug();
        }

        return new Category([
            'name'        => $row['name'],
            'slug'        => $row['slug'],
            'order'       => $row['order'],
            'photo'       => $row['photo'],
            'category_id' => $row['category_id'],
            'active'      => $row['active'],
            'title'       => $row['title'],
            'description' => $row['description'],
        ]);
    }

    public function rules(): array
    {
        $rules = (new CategoryRequest)->rules();
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
