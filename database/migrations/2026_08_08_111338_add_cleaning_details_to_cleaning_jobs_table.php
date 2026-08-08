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
        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->string('cleaning_type')->nullable()->after('frequency');
            $table->boolean('has_pets')->default(false)->after('property_size');
            $table->json('pet_types')->nullable()->after('has_pets');
            $table->unsignedTinyInteger('pet_count')->nullable()->after('pet_types');
            $table->boolean('laundry_addon')->default(false)->after('pet_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->dropColumn(['cleaning_type', 'has_pets', 'pet_types', 'pet_count', 'laundry_addon']);
        });
    }
};
