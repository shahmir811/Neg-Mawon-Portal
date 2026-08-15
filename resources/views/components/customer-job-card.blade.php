@props(['job'])

@php
    $statusColor = match ($job['status']) {
        \App\Enums\JobStatus::Requested => 'zinc',
        \App\Enums\JobStatus::Assigned => 'blue',
        \App\Enums\JobStatus::Completed => 'green',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 rounded-2xl border border-secondary bg-surface p-5 shadow-[0_1px_3px_rgba(11,59,46,0.06)] sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="flex items-start gap-4">
        @if ($job['status'] === \App\Enums\JobStatus::Requested)
            <flux:avatar icon="clock" color="zinc" tooltip="{{ __('Waiting to be assigned') }}" />
        @else
            <flux:avatar :src="$job['cleaner_photo_url']" icon="user" />
        @endif

        <div class="flex flex-col gap-1.5">
            <flux:heading class="text-primary">{{ $job['address'] }}</flux:heading>

            <flux:link :href="$job['google_maps_url']" target="_blank" class="inline-flex w-fit items-center gap-1 text-sm">
                <flux:icon.map-pin class="size-3.5" />
                {{ __('View on map') }}
            </flux:link>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                <span class="inline-flex items-center gap-1.5 text-sm text-text/70">
                    <flux:icon.calendar-days class="size-4 text-text/40" />
                    {{ $job['requested_at']->format('M j, Y \a\t g:i A') }}
                </span>
                <span class="inline-flex items-center gap-1.5 text-sm text-text/70">
                    <flux:icon.home class="size-4 text-text/40" />
                    {{ $job['property_size'] }}
                </span>
            </div>

            @if ($job['service_type'] || $job['cleaning_type'] || $job['has_pets'] || $job['laundry_addon'])
                <div class="flex flex-wrap items-center gap-1.5">
                    @if ($job['service_type'])
                        <flux:badge size="sm" color="zinc">{{ $job['service_type']->label() }}</flux:badge>
                    @endif
                    @if ($job['cleaning_type'])
                        <flux:badge size="sm" color="zinc">{{ $job['cleaning_type']->label() }}</flux:badge>
                    @endif
                    @if ($job['has_pets'])
                        <flux:badge size="sm" color="zinc">{{ __(':count pet(s)', ['count' => $job['pet_count'] ?? '?']) }}</flux:badge>
                    @endif
                    @if ($job['laundry_addon'])
                        <flux:badge size="sm" color="amber">{{ __('Laundry add-on') }}</flux:badge>
                    @endif
                </div>
            @endif

            @if ($job['notes'])
                <flux:text class="italic text-text/70">&ldquo;{{ $job['notes'] }}&rdquo;</flux:text>
            @endif

            @if (count($job['photo_urls']) > 0)
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach ($job['photo_urls'] as $photoUrl)
                        <img src="{{ $photoUrl }}" class="h-14 w-14 rounded-lg object-cover" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 flex-col items-end gap-2">
        <flux:badge :color="$statusColor" class="w-fit">
            {{ ucfirst($job['status']->value) }}
        </flux:badge>

        @if ($job['status'] === \App\Enums\JobStatus::Requested)
            <flux:button size="sm" variant="ghost" :href="route('customer.jobs.edit', $job['id'])" wire:navigate>
                {{ __('Edit') }}
            </flux:button>
        @else
            <flux:button size="sm" variant="ghost" :href="route('customer.jobs.show', $job['id'])" wire:navigate>
                {{ __('View') }}
            </flux:button>
        @endif
    </div>
</div>
