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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->decimal('system_qty', 15, 3);
            $table->decimal('physical_qty', 15, 3);
            $table->decimal('diff', 15, 3);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
