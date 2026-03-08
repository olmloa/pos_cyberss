<?php

namespace App\Http\Requests\Sma\Order;

use App\Tec\Rules\ExtraAttributes;
use App\Tec\Rules\ProductVariation;
use App\Tec\Services\OrderCalculator;
use Illuminate\Foundation\Http\FormRequest;

class ReturnOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'        => 'required|date',
            'type'        => 'required|in:Sale,Purchase',
            'type_ref'    => 'nullable|string',
            'reference'   => 'nullable|string|max:36',
            'sale_id'     => 'nullable|exists:sales,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'customer_id' => 'nullable|required_if:type,Sale|exists:customers,id',
            'supplier_id' => 'nullable|required_if:type,Purchase|exists:suppliers,id',
            'details'     => 'nullable',
            'surcharge'   => 'nullable|numeric',

            'items'                         => 'required|array|min:1',
            'items.*.id'                    => 'nullable',
            'items.*.discount'              => 'nullable',
            'items.*.product_id'            => 'required|exists:products,id',
            'items.*.sale_item_id'          => 'nullable|exists:sale_items,id',
            'items.*.purchase_item_id'      => 'nullable|exists:purchase_items,id',
            'items.*.quantity'              => 'required|numeric|min:0',
            'items.*.unit_id'               => 'nullable|exists:units,id',
            'items.*.price'                 => 'nullable|required_if:type,Sale|numeric',
            'items.*.cost'                  => 'nullable|required_if:type,Purchase|numeric',
            'items.*.taxes'                 => 'nullable|array',
            'items.*.variations'            => new ProductVariation,
            'items.*.variations.*.id'       => new ProductVariation,
            'items.*.variations.*.quantity' => 'required_with:items.*.variations|numeric|min:0',
            'items.*.variations.*.unit_id'  => 'nullable|exists:units,id',
            'items.*.variations.*.cost'     => 'nullable',
            'items.*.variations.*.price'    => 'nullable',
            'items.*.variations.*.discount' => 'nullable',
            'items.*.variations.*.taxes'    => 'nullable|array',

            'extra_attributes' => ['nullable', new ExtraAttributes('return_order')],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return OrderCalculator::calculate($data, $data['type'] == 'Purchase' ? 'cost' : 'price');
    }
}
