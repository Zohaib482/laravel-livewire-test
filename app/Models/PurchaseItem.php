<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
  public $timestamps = false;

  protected $fillable = [
    'purchase_id',
    'item_id',
    'brand_id',
    'qty',
    'price',
  ];

  public function purchase(): BelongsTo
  {
    return $this->belongsTo(Purchase::class);
  }

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }

  public function brand(): BelongsTo
  {
    return $this->belongsTo(Brand::class);
  }
}
