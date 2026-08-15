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
            $table->unsignedTinyInteger('bedroom_count')->nullable()->after('property_size');
            $table->unsignedTinyInteger('bathroom_count')->nullable()->after('bedroom_count');
            $table->string('floor_type')->nullable()->after('laundry_addon');
            $table->decimal('estimated_price', 8, 2)->nullable()->after('floor_type');
            $table->decimal('final_price', 8, 2)->nullable()->after('estimated_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->dropColumn(['bedroom_count', 'bathroom_count', 'floor_type', 'estimated_price', 'final_price']);
        });
    }
};
