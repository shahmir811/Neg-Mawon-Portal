<?php

namespace App\Models;

use App\Enums\JobFrequency;
use App\Enums\JobStatus;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use Database\Factories\CleaningJobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'customer_id',
    'cleaner_id',
    'address',
    'requested_at',
    'property_type',
    'service_type',
    'frequency',
    'notes',
    'property_size',
    'status',
])]
class CleaningJob extends Model
{
    /** @use HasFactory<CleaningJobFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'status' => JobStatus::class,
            'property_type' => PropertyType::class,
            'service_type' => ServiceType::class,
            'frequency' => JobFrequency::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function cleaner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleaner_id');
    }

    /** @return HasMany<CleaningJobPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(CleaningJobPhoto::class);
    }

    /**
     * The only piece of cleaner information a customer is ever allowed to see
     * (privacy rule, AGENTS.md Section 5) — no name, phone, email, or user ID.
     */
    public function cleanerPhotoUrl(): ?string
    {
        $photoPath = $this->cleaner?->cleanerProfile?->photo_path;

        return $photoPath ? Storage::url($photoPath) : null;
    }

    /**
     * A free, keyless link to the address on Google Maps — deliberately not
     * an embedded/interactive map, which would require a billed Google Cloud
     * Maps API key (at odds with this project's shared-hosting, low-cost
     * stack — see AGENTS.md Section 8).
     */
    public function googleMapsUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->address);
    }

    /**
     * Fields safe to expose to either portal (customer or cleaner) — job
     * details only, no other party's identity. Built as a plain array rather
     * than serializing the Eloquent model so no hidden relation data can leak
     * into a Livewire snapshot.
     *
     * @return array<string, mixed>
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'requested_at' => $this->requested_at,
            'notes' => $this->notes,
            'property_type' => $this->property_type,
            'service_type' => $this->service_type,
            'frequency' => $this->frequency,
            'property_size' => $this->property_size,
            'status' => $this->status,
            'google_maps_url' => $this->googleMapsUrl(),
            'photo_urls' => $this->photos->map(fn (CleaningJobPhoto $photo) => $photo->url())->all(),
        ];
    }

    /**
     * Customer-facing payload: job details plus the assigned cleaner's photo
     * only (AGENTS.md Section 5) — never the cleaner's name, phone, email, or ID.
     *
     * @return array<string, mixed>
     */
    public function toCustomerArray(): array
    {
        return [
            ...$this->toPortalArray(),
            'cleaner_photo_url' => $this->cleanerPhotoUrl(),
        ];
    }
}
