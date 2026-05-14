<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('purchase_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
      $table->foreignId('item_id')->constrained();
      $table->foreignId('brand_id')->constrained();
      $table->unsignedInteger('qty');
      $table->decimal('price', 12, 2);
      $table->unique(['purchase_id', 'item_id', 'brand_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchase_items');
  }
};
