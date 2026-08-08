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
                @if ($job['service_type'])
                    <span class="inline-flex items-center gap-1.5 text-sm text-text/70">
                        <flux:icon.sparkles class="size-4 text-text/40" />
                        {{ $job['service_type']->label() }}
                    </span>
                @endif
            </div>

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

    <flux:badge :color="$statusColor" class="w-fit shrink-0">
        {{ ucfirst($job['status']->value) }}
    </flux:badge>
</div>
