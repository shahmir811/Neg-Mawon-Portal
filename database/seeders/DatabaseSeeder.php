<?php

namespace Database\Seeders;

use App\Enums\JobStatus;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\CleaningJob;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'James',
            'email' => 'jnguillaume4@gmail.com',
        ]);

        $cleaners = collect([
            ['name' => 'Marie Joseph', 'agreement_signed' => true, 'subscription_status' => SubscriptionStatus::Active, 'subscription_plan' => SubscriptionPlan::Annual],
            ['name' => 'Devon Pierre', 'agreement_signed' => true, 'subscription_status' => SubscriptionStatus::Active, 'subscription_plan' => SubscriptionPlan::Monthly],
            ['name' => 'Angela Saint-Louis', 'agreement_signed' => false, 'subscription_status' => SubscriptionStatus::Inactive, 'subscription_plan' => null],
        ])->map(function (array $attributes) {
            $user = User::factory()->cleaner()->create(['name' => $attributes['name']]);

            $user->cleanerProfile()->create([
                'phone' => fake()->phoneNumber(),
                'agreement_photo_path' => $attributes['agreement_signed'] ? 'agreements/placeholder.jpg' : null,
                'agreement_signed' => $attributes['agreement_signed'],
                'subscription_plan' => $attributes['subscription_plan'],
                'subscription_status' => $attributes['subscription_status'],
                'next_renewal_at' => $attributes['subscription_status'] === SubscriptionStatus::Active ? now()->addMonth() : null,
            ]);

            return $user;
        });

        $customers = User::factory()
            ->customer()
            ->count(5)
            ->create()
            ->each(fn (User $user) => $user->customerProfile()->create([
                'phone' => fake()->phoneNumber(),
            ]));

        // A mix of requested, assigned, and completed jobs so every job status is represented.
        $customers->each(function (User $customer) {
            CleaningJob::factory()->create([
                'customer_id' => $customer->id,
                'cleaner_id' => null,
                'status' => JobStatus::Requested,
            ]);
        });

        $customers->take(3)->each(function (User $customer) use ($cleaners) {
            CleaningJob::factory()->create([
                'customer_id' => $customer->id,
                'cleaner_id' => $cleaners->random()->id,
                'status' => JobStatus::Assigned,
            ]);
        });

        $customers->take(2)->each(function (User $customer) use ($cleaners) {
            CleaningJob::factory()->create([
                'customer_id' => $customer->id,
                'cleaner_id' => $cleaners->random()->id,
                'status' => JobStatus::Completed,
            ]);
        });
    }
}
