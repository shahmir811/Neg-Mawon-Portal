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
            $table->string('property_type')->nullable()->after('address');
            $table->string('service_type')->nullable()->after('property_type');
            $table->string('frequency')->nullable()->after('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->dropColumn(['property_type', 'service_type', 'frequency']);
        });
    }
};
