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
        Schema::create('label_print_logs', function (Blueprint $table) {
            $table->id();
            $table->string('label_type'); // product, batch, location
            $table->unsignedBigInteger('reference_id');
            $table->unsignedInteger('copies')->default(1);
            $table->foreignId('printed_by')->constrained('users');
            $table->timestamps();
            $table->index('label_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_print_logs');
    }
};
