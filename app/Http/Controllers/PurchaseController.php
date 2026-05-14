<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function show(Purchase $purchase): View
    {
        $this->authorize('view-purchases');

        $purchase->load(['purchaseItems.item', 'purchaseItems.brand']);

        return view('purchases.show', compact('purchase'));
    }
}
