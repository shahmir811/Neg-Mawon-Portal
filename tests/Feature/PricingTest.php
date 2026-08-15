<?php

use App\Enums\CleaningType;
use App\Enums\JobFrequency;
use App\Enums\PropertyType;
use App\Models\CleaningJob;
use App\Models\PricingSetting;
use App\Models\User;
use App\Services\JobPriceCalculator;
use Livewire\Livewire;

test('a residential job with bedroom and bathroom counts is priced off the per-room rates', function () {
    PricingSetting::current()->update([
        'base_flat_fee' => 50,
        'per_bedroom_rate' => 10,
        'per_bathroom_rate' => 5,
        'deep_cleaning_surcharge' => 0,
        'pet_fee_per_pet' => 0,
        'laundry_fee' => 0,
        'frequency_discounts' => ['one_time' => 0],
    ]);

    $price = JobPriceCalculator::estimate([
        'property_type' => PropertyType::Residential->value,
        'property_size' => '1,000-3,000 sq ft',
        'bedroom_count' => 3,
        'bathroom_count' => 2,
        'cleaning_type' => CleaningType::Soft->value,
        'has_pets' => false,
        'pet_count' => null,
        'laundry_addon' => false,
        'frequency' => JobFrequency::OneTime->value,
    ]);

    expect($price)->toBe(90.0); // 50 + 3*10 + 2*5
});

test('a non-residential job falls back to the size-band rate', function () {
    PricingSetting::current()->update([
        'base_rates_by_size' => ['1,000-3,000 sq ft' => 150],
        'deep_cleaning_surcharge' => 0,
        'pet_fee_per_pet' => 0,
        'laundry_fee' => 0,
        'frequency_discounts' => ['one_time' => 0],
    ]);

    $price = JobPriceCalculator::estimate([
        'property_type' => PropertyType::Commercial->value,
        'property_size' => '1,000-3,000 sq ft',
        'bedroom_count' => null,
        'bathroom_count' => null,
        'cleaning_type' => CleaningType::Soft->value,
        'has_pets' => false,
        'pet_count' => null,
        'laundry_addon' => false,
        'frequency' => JobFrequency::OneTime->value,
    ]);

    expect($price)->toBe(150.0);
});

test('deep cleaning, pets, laundry, and frequency discount all adjust the estimate', function () {
    PricingSetting::current()->update([
        'base_flat_fee' => 100,
        'per_bedroom_rate' => 0,
        'per_bathroom_rate' => 0,
        'deep_cleaning_surcharge' => 20,
        'pet_fee_per_pet' => 5,
        'laundry_fee' => 15,
        'frequency_discounts' => ['weekly' => 10],
    ]);

    $price = JobPriceCalculator::estimate([
        'property_type' => PropertyType::Residential->value,
        'property_size' => '1,000-3,000 sq ft',
        'bedroom_count' => 0,
        'bathroom_count' => 0,
        'cleaning_type' => CleaningType::Deep->value,
        'has_pets' => true,
        'pet_count' => 2,
        'laundry_addon' => true,
        'frequency' => JobFrequency::Weekly->value,
    ]);

    // (100 base + 20 deep + 10 pets + 15 laundry) = 145, minus 10% = 130.5
    expect($price)->toBe(130.5);
});

test('a job request stores the calculated estimated price', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->set('address', '1 Estimate Way, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', \App\Enums\ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::OneTime->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->set('bedroom_count', 2)
        ->set('bathroom_count', 1)
        ->call('submit');

    $job = CleaningJob::where('customer_id', $customer->id)->sole();

    expect((float) $job->estimated_price)->toBeGreaterThan(0)
        ->and($job->displayPrice())->toBe($job->estimated_price);
});

test('admin can override the estimated price with a final price', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'estimated_price' => 150,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.job', ['job' => $job])
        ->set('finalPrice', '175.50')
        ->call('saveFinalPrice');

    expect((float) $job->fresh()->final_price)->toBe(175.5)
        ->and($job->fresh()->displayPrice())->toBe($job->fresh()->final_price);
});

test('admin can edit and save pricing rules', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test('pages::admin.pricing')
        ->set('base_flat_fee', 60)
        ->set('per_bedroom_rate', 12)
        ->set('per_bathroom_rate', 8)
        ->call('save');

    $settings = PricingSetting::current();

    expect((float) $settings->base_flat_fee)->toBe(60.0)
        ->and((float) $settings->per_bedroom_rate)->toBe(12.0)
        ->and((float) $settings->per_bathroom_rate)->toBe(8.0);
});

test('a cleaner can set their zip code from settings', function () {
    $cleaner = User::factory()->cleaner()->create();
    $cleaner->cleanerProfile()->create();

    $this->actingAs($cleaner);

    Livewire::test('pages::settings.profile')
        ->set('zip_code', '19111')
        ->call('updateZipCode');

    expect($cleaner->cleanerProfile->fresh()->zip_code)->toBe('19111');
});
