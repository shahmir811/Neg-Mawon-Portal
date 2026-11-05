<?php

use App\Enums\CommercialBusinessType;
use App\Enums\JobFrequency;
use App\Enums\QuoteType;
use App\Enums\ServiceType;
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
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', array_column(QuoteType::cases(), 'value'));
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('zip_code', 10)->nullable();
            $table->enum('service_type', array_column(ServiceType::cases(), 'value'))->nullable();
            $table->enum('business_type', array_column(CommercialBusinessType::cases(), 'value'))->nullable();
            $table->string('property_details', 500);
            $table->enum('frequency', array_column(JobFrequency::cases(), 'value'));
            $table->date('preferred_date')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
