<?php

namespace App\Concerns;

use App\Enums\CleaningType;
use App\Enums\FloorType;
use App\Enums\JobFrequency;
use App\Enums\PetType;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use App\Models\CleaningJob;
use App\Services\JobPriceCalculator;
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

    public ?int $bedroom_count = null;

    public ?int $bathroom_count = null;

    public string $floor_type = '';

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
            'bedroom_count' => ['nullable', 'required_if:property_type,'.PropertyType::Residential->value, 'integer', 'min:0', 'max:20'],
            'bathroom_count' => ['nullable', 'required_if:property_type,'.PropertyType::Residential->value, 'integer', 'min:0', 'max:20'],
            'floor_type' => ['nullable', Rule::enum(FloorType::class)],
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
            'bedroom_count' => $validated['bedroom_count'] ?? null,
            'bathroom_count' => $validated['bathroom_count'] ?? null,
            'has_pets' => $validated['has_pets'],
            'pet_types' => $validated['has_pets'] ? $validated['pet_types'] : [],
            'pet_type_other' => $validated['has_pets'] && in_array(PetType::Other->value, $validated['pet_types'] ?? [], true)
                ? $validated['pet_type_other']
                : null,
            'pet_count' => $validated['has_pets'] ? $validated['pet_count'] : null,
            'laundry_addon' => $validated['laundry_addon'],
            'floor_type' => $validated['floor_type'] ?: null,
            'estimated_price' => JobPriceCalculator::estimate($validated),
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
     * A rough cost estimate computed live from the fields filled in so far
     * — shown to the customer before they submit. James can still override
     * the final number from the admin job page; this is a placeholder
     * formula until he provides real pricing rules (admin.pricing has the
     * editable numbers it draws from).
     */
    #[Computed]
    public function estimatedPrice(): ?float
    {
        if ($this->property_type === '' || $this->property_size === '' || $this->frequency === '') {
            return null;
        }

        return JobPriceCalculator::estimate([
            'property_type' => $this->property_type,
            'property_size' => $this->property_size,
            'bedroom_count' => $this->bedroom_count,
            'bathroom_count' => $this->bathroom_count,
            'cleaning_type' => $this->cleaning_type,
            'has_pets' => $this->has_pets,
            'pet_count' => $this->pet_count,
            'laundry_addon' => $this->laundry_addon,
            'frequency' => $this->frequency,
        ]);
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
