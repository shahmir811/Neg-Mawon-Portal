<?php

use App\Enums\JobFrequency;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Your Dashboard')] class extends Component
{
    use WithFileUploads;

    public string $address = '';

    public string $requested_at = '';

    public string $property_type = '';

    public string $service_type = '';

    public string $frequency = '';

    public string $property_size = '';

    public string $notes = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $photos = [];

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'requested_at' => ['required', 'date', 'after:now'],
            'property_type' => ['required', Rule::enum(PropertyType::class)],
            'service_type' => ['required', Rule::enum(ServiceType::class)],
            'frequency' => ['required', Rule::enum(JobFrequency::class)],
            'property_size' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $job = Auth::user()->jobsAsCustomer()->create([
            'address' => $validated['address'],
            'requested_at' => $validated['requested_at'],
            'property_type' => $validated['property_type'],
            'service_type' => $validated['service_type'],
            'frequency' => $validated['frequency'],
            'property_size' => $validated['property_size'],
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($this->photos as $photo) {
            $job->photos()->create([
                'path' => $photo->store('job-photos', 'public'),
            ]);
        }

        $this->reset(['address', 'requested_at', 'property_type', 'service_type', 'frequency', 'property_size', 'notes', 'photos']);

        Flux::toast(
            variant: 'success',
            text: __('Job request submitted — James will review it and be in touch soon.'),
        );

        $this->redirectRoute('customer.upcoming-jobs', navigate: true);
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
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Customer') }}
        </span>

        <flux:heading size="xl" class="text-primary">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </flux:heading>

        <flux:subheading>
            {{ __("Tell us about your home below and James will assign you a cleaner. You'll find your requests under Upcoming Jobs.") }}
        </flux:subheading>
    </div>

    <div class="rounded-2xl border border-secondary bg-surface p-6">
        <flux:heading size="lg" class="mb-4 text-primary">{{ __('Request a cleaning') }}</flux:heading>

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

            <flux:select wire:model="property_size" :label="__('Property size')" placeholder="Select one">
                <flux:select.option value="Under 1,000 sq ft">{{ __('Under 1,000 sq ft') }}</flux:select.option>
                <flux:select.option value="1,000-3,000 sq ft">{{ __('1,000-3,000 sq ft') }}</flux:select.option>
                <flux:select.option value="3,000-5,000 sq ft">{{ __('3,000-5,000 sq ft') }}</flux:select.option>
                <flux:select.option value="5,000+ sq ft">{{ __('5,000+ sq ft') }}</flux:select.option>
                <flux:select.option value="Not sure / prefer to discuss">{{ __('Not sure / prefer to discuss') }}</flux:select.option>
            </flux:select>

            <div class="sm:col-span-2">
                <flux:textarea
                    wire:model="notes"
                    :label="__('Notes (optional)')"
                    :placeholder="__('Anything your cleaner should know — pets, parking, access instructions...')"
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
                <flux:button type="submit" variant="primary">{{ __('Submit request') }}</flux:button>
            </div>
        </form>
    </div>

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
</div>
