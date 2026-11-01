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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('batch_number');
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->date('manufactured_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('active'); // active, near_expiry, expired, depleted
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'batch_number']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
