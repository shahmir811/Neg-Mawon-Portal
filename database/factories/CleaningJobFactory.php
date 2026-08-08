<?php

namespace Database\Factories;

use App\Enums\JobFrequency;
use App\Enums\JobStatus;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use App\Models\CleaningJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningJob>
 */
class CleaningJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory()->customer(),
            'cleaner_id' => null,
            'address' => fake()->streetAddress().', Philadelphia, PA '.fake()->numberBetween(19111, 19154),
            'requested_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'notes' => fake()->optional()->sentence(),
            'property_type' => fake()->randomElement(PropertyType::cases()),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'frequency' => fake()->randomElement(JobFrequency::cases()),
            'property_size' => fake()->randomElement(['Under 1,000 sq ft', '1,000-3,000 sq ft', '3,000-5,000 sq ft', '5,000+ sq ft']),
            'status' => JobStatus::Requested,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'cleaner_id' => User::factory()->cleaner(),
            'status' => JobStatus::Assigned,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'cleaner_id' => User::factory()->cleaner(),
            'status' => JobStatus::Completed,
        ]);
    }
}
