<?php

use App\Enums\JobStatus;
use App\Models\CleaningJob;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Job details')] class extends Component
{
    public CleaningJob $job;

    public function mount(CleaningJob $job): void
    {
        $job->load(['customer.customerProfile', 'cleaner', 'photos']);

        $this->job = $job;
    }

    /**
     * Same broad admin payload as the dashboard list (AGENTS.md Section 2 —
     * Admin has full visibility) — just rendered on its own page instead of
     * packed into a table row.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function details(): array
    {
        return [
            ...$this->job->toPortalArray(),
            'customer_name' => $this->job->customer->name,
            'customer_email' => $this->job->customer->email,
            'customer_phone' => $this->job->customer->customerProfile?->phone,
            'cleaner_name' => $this->job->cleaner?->name,
        ];
    }

    /**
     * Free, keyless map embed (same `output=embed` trick used for the
     * "View on map" link — see CleaningJob::googleMapsUrl) so the details
     * page can show an inline map without a billed Google Cloud API key.
     */
    #[Computed]
    public function mapEmbedUrl(): string
    {
        return 'https://www.google.com/maps?q='.urlencode($this->job->address).'&output=embed';
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <flux:link :href="route('admin.dashboard')" class="inline-flex w-fit items-center gap-1 text-sm">
            &larr; {{ __('Back to dashboard') }}
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
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Property size') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['property_size'] ?: __('Not specified') }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Status') }}</dt>
                    <dd class="mt-1"><flux:text>{{ ucfirst($this->details['status']->value) }}</flux:text></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Customer') }}</dt>
                    <dd class="mt-1">
                        <flux:text>{{ $this->details['customer_name'] }}</flux:text>
                        <flux:text class="text-text/70">{{ $this->details['customer_email'] }}</flux:text>
                        @if ($this->details['customer_phone'])
                            <flux:text class="text-text/70">{{ $this->details['customer_phone'] }}</flux:text>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-text/60">{{ __('Assigned cleaner') }}</dt>
                    <dd class="mt-1"><flux:text>{{ $this->details['cleaner_name'] ?? __('Not yet assigned') }}</flux:text></dd>
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
        <flux:heading class="text-primary">{{ __('Photos from customer') }}</flux:heading>

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
