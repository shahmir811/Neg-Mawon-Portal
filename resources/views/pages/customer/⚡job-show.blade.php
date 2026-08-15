<?php

use App\Enums\JobStatus;
use App\Enums\PetType;
use App\Models\CleaningJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Job Details')] class extends Component
{
    public CleaningJob $cleaningJob;

    public function mount(int $job): void
    {
        // Scoped to the authenticated customer's own jobs — a mismatched id
        // 404s instead of leaking whether it belongs to someone else, same
        // pattern as job-edit's mount().
        $this->cleaningJob = Auth::user()->jobsAsCustomer()->with('photos')->findOrFail($job);
    }

    /**
     * Customer-facing payload only (AGENTS.md Section 5) — job details plus
     * the assigned cleaner's photo, never the cleaner's name, phone, email,
     * or ID.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function details(): array
    {
        return $this->cleaningJob->toCustomerArray();
    }

    /**
     * Free, keyless map embed (same `output=embed` trick used for the
     * "View on map" link — see CleaningJob::googleMapsUrl) so the details
     * page can show an inline map without a billed Google Cloud API key.
     */
    #[Computed]
    public function mapEmbedUrl(): string
    {
        return 'https://www.google.com/maps?q='.urlencode($this->cleaningJob->address).'&output=embed';
    }

    #[Computed]
    public function backRoute(): string
    {
        return $this->cleaningJob->status === JobStatus::Completed ? 'customer.job-history' : 'customer.upcoming-jobs';
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <flux:link :href="route($this->backRoute)" wire:navigate class="inline-flex w-fit items-center gap-1 text-sm">
            &larr; {{ __('Back') }}
        </flux:link>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="xl" class="text-primary">{{ $this->details['address'] }}</flux:heading>

            <flux:badge :color="match ($this->details['status']) {
                JobStatus::Requested => 'zinc',
                JobStatus::Assigned => 'blue',
                JobStatus::Completed => 'green',
            }" size="lg">
                {{ ucfirst($this->details['status']->value) }}
            </flux:badge>
        </div>

        <flux:subheading>
            {{ __('Requested :date', ['date' => $this->details['requested_at']->format('M j, Y \a\t g:i A')]) }}
        </flux:subheading>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="flex flex-col gap-4 rounded-2xl border border-secondary bg-surface p-5">
            <flux:heading class="text-primary">{{ __('Job details') }}</flux:heading>

            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Home or business type') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['property_type']?->label() ?? __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Service needed') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['service_type']?->label() ?? __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('How often') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['frequency']?->label() ?? __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Type of cleaning') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['cleaning_type']?->label() ?? __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Property size') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['property_size'] ?: __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Bedrooms / bathrooms') }}</dt>
                    <dd class="mt-1">
                        <flux:text>
                            @if ($this->details['bedroom_count'] !== null || $this->details['bathroom_count'] !== null)
                                {{ $this->details['bedroom_count'] ?? '—' }} bd &middot; {{ $this->details['bathroom_count'] ?? '—' }} ba
                            @else
                                {{ __('Not specified') }}
                            @endif
                        </flux:text>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Floor type') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['floor_type']?->label() ?? __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Pets') }}</dt>
                    <dd class="mt-1">
                        <flux:text>
                            @if ($this->details['has_pets'])
                                {{ $this->details['pet_count'] }} &middot;
                                {{ collect($this->details['pet_types'] ?? [])->map(fn ($type) => $type === PetType::Other->value && $this->details['pet_type_other']
                                    ? __('Other (:description)', ['description' => $this->details['pet_type_other']])
                                    : PetType::from($type)->label())->join(', ') }}
                            @else
                                {{ __('No pets') }}
                            @endif
                        </flux:text>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Laundry add-on') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['laundry_addon'] ? __('Yes — bill separately') : __('No') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Price') }}</dt>
                    <dd class="mt-1">
                        <flux:text>{{ $this->details['display_price'] ? '$'.$this->details['display_price'] : __('James will confirm the final price.') }}</flux:text>
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Assigned cleaner') }}</dt>
                    <dd class="mt-1 flex items-center gap-2">
                        @if ($this->details['cleaner_photo_url'])
                            <flux:avatar :src="$this->details['cleaner_photo_url']" size="sm" />
                            <flux:text>{{ __('Assigned') }}</flux:text>
                        @else
                            <flux:text>{{ __('Not yet assigned') }}</flux:text>
                        @endif
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Notes') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['notes'] ?: __('No notes provided.') }}</flux:text></dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-secondary bg-surface p-5">
            <div class="flex items-center justify-between">
                <flux:heading class="text-primary">{{ __('Location') }}</flux:heading>
                <flux:link :href="$this->details['google_maps_url']" target="_blank" class="text-sm">
                    {{ __('Open in Google Maps') }}
                </flux:link>
            </div>

            <div class="overflow-hidden rounded-xl border border-secondary">
                <iframe
                    src="{{ $this->mapEmbedUrl }}"
                    class="h-64 w-full"
                    style="border: 0;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <flux:heading class="text-primary">{{ __('Your photos') }}</flux:heading>

        @if (count($this->details['photo_urls']) > 0)
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($this->details['photo_urls'] as $photoUrl)
                    <a href="{{ $photoUrl }}" target="_blank" class="block overflow-hidden rounded-xl border border-secondary">
                        <img src="{{ $photoUrl }}" class="h-40 w-full object-cover" />
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
                <flux:text>{{ __('No photos were uploaded with this request.') }}</flux:text>
            </div>
        @endif
    </div>
</div>
