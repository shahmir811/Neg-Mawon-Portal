@php
    $submitLabel ??= __('Submit request');
@endphp

<form wire:submit="submit" class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2" x-data="addressAutocomplete" x-init="attach($refs.addressField)">
        <div x-ref="addressField">
            <flux:input
                wire:model.live.debounce.500ms="address"
                :label="__('Service address')"
                placeholder="7135 Rising Sun Ave, Philadelphia, PA 19111"
                autocomplete="off"
                required
            />
        </div>

        @if ($this->addressMapEmbedUrl)
            <div class="mt-3 overflow-hidden rounded-xl border border-secondary" wire:key="address-map-{{ md5($address) }}">
                <iframe
                    src="{{ $this->addressMapEmbedUrl }}"
                    class="h-56 w-full"
                    style="border: 0;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        @endif
    </div>

    <flux:input
        wire:model="requested_at"
        type="datetime-local"
        :label="__('Preferred date & time')"
        required
    />

    <flux:select wire:model="property_type" :label="__('Home or business type')" placeholder="Select one">
        @foreach (\App\Enums\PropertyType::cases() as $type)
            <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select wire:model="service_type" :label="__('Service needed')" placeholder="Select one">
        @foreach (\App\Enums\ServiceType::cases() as $type)
            <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select wire:model="frequency" :label="__('How often')" placeholder="Select one">
        @foreach (\App\Enums\JobFrequency::cases() as $option)
            <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <div>
        <flux:select wire:model="property_size" :label="__('Property size')" placeholder="Select one">
            <flux:select.option value="Under 1,000 sq ft">{{ __('Under 1,000 sq ft') }}</flux:select.option>
            <flux:select.option value="1,000-3,000 sq ft">{{ __('1,000-3,000 sq ft') }}</flux:select.option>
            <flux:select.option value="3,000-5,000 sq ft">{{ __('3,000-5,000 sq ft') }}</flux:select.option>
            <flux:select.option value="5,000+ sq ft">{{ __('5,000+ sq ft') }}</flux:select.option>
            <flux:select.option value="Not sure / prefer to discuss">{{ __('Not sure / prefer to discuss') }}</flux:select.option>
        </flux:select>
    </div>

    <div>
        <flux:select
            wire:model="cleaning_type"
            :label="__('Type of cleaning')"
            placeholder="Select one"
        >
            @foreach ($this->allowedCleaningTypes as $type)
                <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        @if (! $this->eligibleForSoftCleaning)
            <flux:text size="sm" class="mt-1 text-text/60">
                {{ __('Soft cleaning unlocks for repeat clients serviced within the last 30 days.') }}
            </flux:text>
        @endif
    </div>

    <div class="sm:col-span-2 flex flex-col gap-4 rounded-xl border border-secondary bg-background p-4">
        <flux:switch wire:model.live="has_pets" :label="__('Do you have pets?')" />

        @if ($has_pets)
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:checkbox.group wire:model.live="pet_types" :label="__('What kind of pets?')">
                    @foreach (\App\Enums\PetType::cases() as $type)
                        <flux:checkbox :label="$type->label()" value="{{ $type->value }}" />
                    @endforeach
                </flux:checkbox.group>

                <flux:input
                    type="number"
                    wire:model="pet_count"
                    :label="__('How many pets?')"
                    min="1"
                    max="20"
                />

                @if (in_array(\App\Enums\PetType::Other->value, $pet_types, true))
                    <div class="sm:col-span-2">
                        <flux:input
                            wire:model="pet_type_other"
                            :label="__('Please specify')"
                            :placeholder="__('e.g. rabbit, bird')"
                        />
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="sm:col-span-2">
        <flux:checkbox
            wire:model="laundry_addon"
            :label="__('Add laundry service (washing & folding) — billed separately by James')"
        />
    </div>

    <div class="sm:col-span-2">
        <flux:textarea
            wire:model="notes"
            :label="__('Notes (optional)')"
            :placeholder="__('Anything your cleaner should know — parking, access instructions...')"
            rows="3"
        />
    </div>

    <div class="sm:col-span-2">
        <flux:input
            type="file"
            wire:model="photos"
            :label="__('Property photos (optional)')"
            :description="__('Up to 6 photos, 5MB each — helps your cleaner know what to expect.')"
            accept="image/*"
            multiple
        />

        @if (count($photos) > 0)
            <div class="mt-3 flex flex-wrap gap-3">
                @foreach ($photos as $photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 rounded-xl object-cover" />
                @endforeach
            </div>
        @endif
    </div>

    <div class="sm:col-span-2">
        <flux:button type="submit" variant="primary">{{ $submitLabel }}</flux:button>
    </div>
</form>

@if (config('services.google.maps_key'))
    <script>
        function handleGoogleMapsLoaded() {
            window.dispatchEvent(new CustomEvent('google-maps-ready'));
        }

        if (window.google?.maps?.places) {
            handleGoogleMapsLoaded();
        } else if (!document.getElementById('google-maps-script')) {
            const script = document.createElement('script');
            script.id = 'google-maps-script';
            script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=handleGoogleMapsLoaded&loading=async';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }
    </script>
@endif
