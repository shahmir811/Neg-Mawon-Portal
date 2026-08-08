<?php

use App\Enums\CleaningType;
use App\Enums\JobFrequency;
use App\Enums\JobStatus;
use App\Enums\PetType;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use App\Models\CleaningJob;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('a customer can edit their own requested job', function () {
    $customer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '123 Old St, Philadelphia, PA 19111',
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($customer);

    Livewire::test('pages::customer.job-edit', ['job' => $job->id])
        ->assertSet('address', '123 Old St, Philadelphia, PA 19111')
        ->set('address', '456 New St, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Commercial->value)
        ->set('service_type', ServiceType::WindowCleaning->value)
        ->set('frequency', JobFrequency::Weekly->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '3,000-5,000 sq ft')
        ->set('has_pets', true)
        ->set('pet_types', [PetType::Cat->value])
        ->set('pet_count', 1)
        ->set('laundry_addon', true)
        ->set('notes', 'Updated notes')
        ->call('submit')
        ->assertHasNoErrors();

    $job->refresh();

    expect($job->address)->toBe('456 New St, Philadelphia, PA 19111')
        ->and($job->property_type)->toBe(PropertyType::Commercial)
        ->and($job->service_type)->toBe(ServiceType::WindowCleaning)
        ->and($job->frequency)->toBe(JobFrequency::Weekly)
        ->and($job->property_size)->toBe('3,000-5,000 sq ft')
        ->and($job->has_pets)->toBeTrue()
        ->and($job->pet_types)->toBe([PetType::Cat->value])
        ->and($job->laundry_addon)->toBeTrue()
        ->and($job->notes)->toBe('Updated notes')
        ->and($job->status)->toBe(JobStatus::Requested);
});

test('a customer cannot edit another customers job', function () {
    $customer = User::factory()->customer()->create();
    $otherCustomer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $otherCustomer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($customer);

    expect(fn () => Livewire::test('pages::customer.job-edit', ['job' => $job->id]))
        ->toThrow(ModelNotFoundException::class);
});

test('a customer cannot edit a job that has already been assigned', function () {
    $customer = User::factory()->customer()->create();
    $cleaner = User::factory()->cleaner()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'cleaner_id' => $cleaner->id,
        'address' => '789 Locked Ave, Philadelphia, PA 19111',
        'status' => JobStatus::Assigned,
    ]);

    $this->actingAs($customer);

    Livewire::test('pages::customer.job-edit', ['job' => $job->id])
        ->assertRedirect(route('customer.upcoming-jobs'));

    expect($job->fresh()->address)->toBe('789 Locked Ave, Philadelphia, PA 19111');
});

test('a job assigned after the edit form was opened cannot be saved', function () {
    $customer = User::factory()->customer()->create();
    $cleaner = User::factory()->cleaner()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '10 Race Condition Rd, Philadelphia, PA 19111',
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($customer);

    $component = Livewire::test('pages::customer.job-edit', ['job' => $job->id])
        ->set('address', '20 Should Not Save Rd, Philadelphia, PA 19111');

    $job->update(['cleaner_id' => $cleaner->id, 'status' => JobStatus::Assigned]);

    $component->call('submit')->assertRedirect(route('customer.upcoming-jobs'));

    expect($job->fresh()->address)->toBe('10 Race Condition Rd, Philadelphia, PA 19111');
});

test('the 30-day cleaning-type eligibility rule still applies when editing', function () {
    $customer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($customer);

    Livewire::test('pages::customer.job-edit', ['job' => $job->id])
        ->set('cleaning_type', CleaningType::Soft->value)
        ->call('submit')
        ->assertHasErrors('cleaning_type');
});

test('the edit link only appears for requested jobs on the upcoming jobs page', function () {
    $customer = User::factory()->customer()->create();

    $requested = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '1 Requested Way, Philadelphia, PA 19111',
        'status' => JobStatus::Requested,
    ]);

    $assigned = CleaningJob::factory()->assigned()->create([
        'customer_id' => $customer->id,
        'address' => '2 Assigned Way, Philadelphia, PA 19111',
    ]);

    $this->actingAs($customer);

    $response = $this->get(route('customer.upcoming-jobs'));

    $response->assertOk();
    $response->assertSee(route('customer.jobs.edit', $requested->id));
    $response->assertDontSee(route('customer.jobs.edit', $assigned->id));
});
