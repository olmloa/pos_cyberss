<?php

namespace App\Http\Controllers\Sma;

use Inertia\Inertia;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Order\Quotation;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Transfer;
use App\Models\Sma\Order\ReturnOrder;
use App\Models\Sma\Setting\CustomField;

class PublicController extends Controller
{
    public function sale($id, $hash = null)
    {
        $sale = Sale::withoutGlobalScopes()
            ->with(['customer', 'items.product', 'items.variations', 'store'])->findOrFail($id);

        if ($hash && $sale->hash !== $hash) {
            abort(403);
        }

        return Inertia::render('Sma/Order/Sale/View', [
            'sale'          => $sale,
            'custom_fields' => CustomField::ofModel('sale')->get(),
        ]);
    }

    public function payment($id, $hash = null)
    {
        $payment = Payment::withoutGlobalScopes()
            ->with(['customer', 'supplier', 'store'])->findOrFail($id);

        if ($hash && $payment->hash !== $hash) {
            abort(403);
        }

        return Inertia::render('Sma/Order/Payment/View', [
            'payment'       => $payment,
            'custom_fields' => CustomField::ofModel('payment')->get(),
        ]);
    }

    public function purchase($id, $hash = null)
    {
        $purchase = Purchase::withoutGlobalScopes()
            ->with(['supplier',  'items.product', 'items.variations', 'store'])->findOrFail($id);

        if ($hash && $purchase->hash !== $hash) {
            abort(403);
        }

        return Inertia::render('Sma/Order/Purchase/View', [
            'purchase'      => $purchase,
            'custom_fields' => CustomField::ofModel('purchase')->get(),
        ]);
    }

    public function quotation($id, $hash = null)
    {
        $quotation = Quotation::withoutGlobalScopes()
            ->with(['customer', 'items.product', 'items.variations', 'store'])->findOrFail($id);

        if ($hash && $quotation->hash !== $hash) {
            abort(403);
        }

        return Inertia::render('Sma/Order/Quotation/View', [
            'quotation'     => $quotation,
            'custom_fields' => CustomField::ofModel('quotation')->get(),
        ]);
    }

    public function returnOrder($id, $hash = null)
    {
        $return_order = ReturnOrder::withoutGlobalScopes()
            ->with(['customer', 'supplier', 'items.product', 'items.variations', 'store'])->findOrFail($id);

        if ($hash && $return_order->hash !== $hash) {
            abort(403);
        }

        return Inertia::render('Sma/Order/ReturnOrder/View', [
            'return_order'  => $return_order,
            'custom_fields' => CustomField::ofModel('return_order')->get(),
        ]);
    }

    public function transfer($id, $hash = null)
    {
        $transfer = Transfer::withoutGlobalScopes()
            ->with(['toStore', 'items.product', 'items.variations', 'store'])->findOrFail($id);

        if ($hash && $transfer->hash !== $hash) {
            abort(403);
        }

        return Inertia::render('Sma/Product/Transfer/View', [
            'transfer'      => $transfer,
            'custom_fields' => CustomField::ofModel('transfer')->get(),
        ]);
    }
}
