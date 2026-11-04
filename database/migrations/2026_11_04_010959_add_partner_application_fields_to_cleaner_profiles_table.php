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
        Schema::table('cleaner_profiles', function (Blueprint $table) {
            $table->string('service_area')->nullable()->after('zip_code');
            $table->text('experience')->nullable()->after('service_area');
            $table->json('services_offered')->nullable()->after('experience');
            $table->string('availability')->nullable()->after('services_offered');
            $table->string('business_name')->nullable()->after('availability');
            $table->string('insurance_provider')->nullable()->after('business_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'service_area',
                'experience',
                'services_offered',
                'availability',
                'business_name',
                'insurance_provider',
            ]);
        });
    }
};
