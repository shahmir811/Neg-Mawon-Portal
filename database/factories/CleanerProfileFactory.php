<?php

namespace Database\Factories;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\CleanerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleanerProfile>
 */
class CleanerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->cleaner(),
            'phone' => fake()->phoneNumber(),
            'zip_code' => fake()->postcode(),
            'photo_path' => null,
            'agreement_photo_path' => null,
            'agreement_signed' => false,
            'subscription_plan' => fake()->randomElement(SubscriptionPlan::cases()),
            'subscription_status' => SubscriptionStatus::Inactive,
            'next_renewal_at' => null,
        ];
    }

    public function agreementSigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'agreement_photo_path' => 'agreements/placeholder.jpg',
            'agreement_signed' => true,
        ]);
    }

    public function subscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => SubscriptionStatus::Active,
            'next_renewal_at' => now()->addMonth(),
        ]);
    }
}
