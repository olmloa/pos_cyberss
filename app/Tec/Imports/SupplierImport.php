<?php

namespace App\Tec\Imports;

use Nnjeim\World\Models\State;
use Nnjeim\World\Models\Country;
use App\Models\Sma\People\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Http\Requests\Sma\People\SupplierRequest;

class SupplierImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function model(array $row)
    {
        $row['state_id'] = null;
        if ($row['state'] ?? null) {
            $row['state_id'] = State::where('name', $row['state'])->first()?->id;
        }
        $row['country_id'] = null;
        if ($row['country'] ?? null) {
            $row['country_id'] = Country::where('name', $row['country'])->first()?->id;
        }

        if (! ($row['company'] ?? null)) {
            $row['company'] = $row['name'];
        }

        return new Supplier([
            'name'      => $row['name'],
            'phone'     => $row['phone'],
            'email'     => $row['email'],
            'company'   => $row['company'],
            'due_limit' => $row['due_limit'],

            'lot_no'         => $row['lot_no'],
            'street'         => $row['street'],
            'address_line_1' => $row['address_line_1'],
            'address_line_2' => $row['address_line_2'],
            'city'           => $row['city'],
            'postal_code'    => $row['postal_code'],
            'state_id'       => $row['state_id'] ?? null,
            'country_id'     => $row['country_id'] ?? null,

            // 'opening_balance'  => $row['opening_balance'],
            // 'extra_attributes' => $row['extra_attributes'],

            'user_id' => $this->user?->id,
        ]);
    }

    public function rules(): array
    {
        $rules = (new SupplierRequest)->rules();
        $rules['company'] = 'nullable';

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
        return ['name', 'phone', 'email'];
    }
}
