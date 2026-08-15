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
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_flat_fee', 8, 2)->default(75);
            $table->decimal('per_bedroom_rate', 8, 2)->default(15);
            $table->decimal('per_bathroom_rate', 8, 2)->default(10);
            $table->json('base_rates_by_size')->nullable();
            $table->decimal('pet_fee_per_pet', 8, 2)->default(10);
            $table->decimal('laundry_fee', 8, 2)->default(20);
            $table->decimal('deep_cleaning_surcharge', 8, 2)->default(30);
            $table->json('frequency_discounts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};
