<?php

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
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
        Schema::create('cleaner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('agreement_photo_path')->nullable();
            $table->boolean('agreement_signed')->default(false);
            $table->enum('subscription_plan', array_column(SubscriptionPlan::cases(), 'value'))->nullable();
            $table->enum('subscription_status', array_column(SubscriptionStatus::cases(), 'value'))->default(SubscriptionStatus::Inactive->value);
            $table->string('stripe_id')->nullable()->index();
            $table->string('stripe_status')->nullable();
            $table->timestamp('next_renewal_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaner_profiles');
    }
};
