<?php

namespace Database\Factories;

use App\Enums\CommercialBusinessType;
use App\Enums\JobFrequency;
use App\Enums\QuoteType;
use App\Enums\ServiceType;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteRequest>
 */
class QuoteRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => QuoteType::Residential,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'zip_code' => fake()->postcode(),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'business_type' => null,
            'property_details' => fake()->sentence(),
            'frequency' => fake()->randomElement(JobFrequency::cases()),
            'preferred_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'description' => fake()->paragraph(),
        ];
    }

    public function residential(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuoteType::Residential,
            'zip_code' => fake()->postcode(),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'business_type' => null,
        ]);
    }

    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuoteType::Commercial,
            'zip_code' => null,
            'service_type' => null,
            'business_type' => fake()->randomElement(CommercialBusinessType::cases()),
            'preferred_date' => null,
        ]);
    }
}
