<?php

namespace App\Concerns;

use App\Enums\CleaningType;
use App\Enums\JobFrequency;
use App\Enums\PetType;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use App\Models\CleaningJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * The job-request fields, validation, and cleaning-type eligibility logic
 * shared between the customer's "request a cleaning" form
 * (pages::customer.dashboard) and "edit a request" form
 * (pages::customer.job-edit) — everything here behaves identically for
 * create and update, so it's kept in one place rather than duplicated.
 */
trait CleaningJobFormFields
{
    public string $address = '';

    public string $requested_at = '';

    public string $property_type = '';

    public string $service_type = '';

    public string $frequency = '';

    public string $cleaning_type = '';

    public string $property_size = '';

    public bool $has_pets = false;

    /** @var array<int, string> */
    public array $pet_types = [];

    public string $pet_type_other = '';

    public ?int $pet_count = null;

    public bool $laundry_addon = false;

    public string $notes = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $photos = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'requested_at' => ['required', 'date', 'after:now'],
            'property_type' => ['required', Rule::enum(PropertyType::class)],
            'service_type' => ['required', Rule::enum(ServiceType::class)],
            'frequency' => ['required', Rule::enum(JobFrequency::class)],
            'cleaning_type' => ['required', Rule::in(array_map(fn (CleaningType $type) => $type->value, $this->allowedCleaningTypes()))],
            'property_size' => ['required', 'string', 'max:255'],
            'has_pets' => ['boolean'],
            'pet_types' => ['nullable', 'required_if_accepted:has_pets', 'array'],
            'pet_types.*' => [Rule::enum(PetType::class)],
            'pet_type_other' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => in_array(PetType::Other->value, $this->pet_types, true))],
            'pet_count' => ['nullable', 'required_if_accepted:has_pets', 'integer', 'min:1', 'max:20'],
            'laundry_addon' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function cleaningJobAttributes(array $validated): array
    {
        return [
            'address' => $validated['address'],
            'requested_at' => $validated['requested_at'],
            'property_type' => $validated['property_type'],
            'service_type' => $validated['service_type'],
            'frequency' => $validated['frequency'],
            'cleaning_type' => $validated['cleaning_type'],
            'property_size' => $validated['property_size'],
            'has_pets' => $validated['has_pets'],
            'pet_types' => $validated['has_pets'] ? $validated['pet_types'] : [],
            'pet_type_other' => $validated['has_pets'] && in_array(PetType::Other->value, $validated['pet_types'] ?? [], true)
                ? $validated['pet_type_other']
                : null,
            'pet_count' => $validated['has_pets'] ? $validated['pet_count'] : null,
            'laundry_addon' => $validated['laundry_addon'],
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function attachUploadedPhotos(CleaningJob $job): void
    {
        foreach ($this->photos as $photo) {
            $job->photos()->create([
                'path' => $photo->store('job-photos', 'public'),
            ]);
        }
    }

    /**
     * Repeat clients serviced within the last 30 days may choose Soft
     * Cleaning; everyone else (first-time clients, or anyone whose last
     * completed job was more than 30 days ago) is limited to Deep Cleaning.
     */
    #[Computed]
    public function eligibleForSoftCleaning(): bool
    {
        $lastJob = CleaningJob::lastCompletedFor(Auth::user());

        if (! $lastJob) {
            return false;
        }

        return $lastJob->requested_at->diffInDays(now()) <= 30;
    }

    /**
     * @return array<int, CleaningType>
     */
    #[Computed]
    public function allowedCleaningTypes(): array
    {
        return $this->eligibleForSoftCleaning ? CleaningType::cases() : [CleaningType::Deep];
    }

    /**
     * A free, keyless live Google Maps embed (same `output=embed` trick used
     * for the "View on map" link and the admin job-detail preview — see
     * CleaningJob::googleMapsUrl) so the customer can pan/zoom around the
     * typed address while filling out the form, without a billed Google
     * Cloud Maps API key.
     */
    #[Computed]
    public function addressMapEmbedUrl(): ?string
    {
        if (strlen(trim($this->address)) < 5) {
            return null;
        }

        return 'https://www.google.com/maps?q='.urlencode($this->address).'&output=embed';
    }
}
