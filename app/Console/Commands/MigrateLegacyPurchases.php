<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('migrate:legacy-purchases')]
#[Description('Import legacy purchase rows into items, brands, purchases, and purchase_items')]
class MigrateLegacyPurchases extends Command
{
  /**
   * @var array<int, array{item_name: string, brand_name: string, qty: int, price: int|float}>
   */
  private array $legacyPurchases = [
    [
      'item_name' => 'Sugar',
      'brand_name' => 'ABC',
      'qty' => 10,
      'price' => 100,
    ],
    [
      'item_name' => 'Rice',
      'brand_name' => 'XYZ',
      'qty' => 5,
      'price' => 200,
    ],
    [
      'item_name' => 'Sugar',
      'brand_name' => 'DEF',
      'qty' => 8,
      'price' => 95,
    ],
  ];

  public function handle(): int
  {
    $createdPurchases = 0;
    $createdLines = 0;
    $skippedLines = 0;

    DB::transaction(function () use (&$createdPurchases, &$createdLines, &$skippedLines) {
      foreach ($this->legacyPurchases as $row) {
        $item = Item::firstOrCreate(['name' => $row['item_name']]);
        $brand = Brand::firstOrCreate(['name' => $row['brand_name']]);

        $existingLine = PurchaseItem::query()
          ->where('item_id', $item->id)
          ->where('brand_id', $brand->id)
          ->where('qty', $row['qty'])
          ->where('price', $row['price'])
          ->first();

        if ($existingLine) {
          $skippedLines++;
          continue;
        }

        $lineTotal = $row['qty'] * $row['price'];

        $purchase = Purchase::create(['total' => $lineTotal]);

        PurchaseItem::create([
          'purchase_id' => $purchase->id,
          'item_id' => $item->id,
          'brand_id' => $brand->id,
          'qty' => $row['qty'],
          'price' => $row['price'],
        ]);

        $createdPurchases++;
        $createdLines++;
      }
    });

    $this->info("Legacy purchases migrated. Purchases created: {$createdPurchases}, lines created: {$createdLines}, lines skipped: {$skippedLines}.");

    return self::SUCCESS;
  }
}
