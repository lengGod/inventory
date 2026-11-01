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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->string('type'); // in, out, adjust, void
            $table->decimal('qty', 15, 3); // signed: positive for in, negative for out
            $table->decimal('balance_after', 15, 3);
            $table->string('reference_type')->nullable(); // StockIn, StockOut, StockAdjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
