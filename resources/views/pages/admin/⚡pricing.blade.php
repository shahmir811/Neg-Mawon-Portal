<?php

use App\Enums\JobFrequency;
use App\Models\PricingSetting;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pricing rules')] class extends Component
{
    public float $base_flat_fee = 0;

    public float $per_bedroom_rate = 0;

    public float $per_bathroom_rate = 0;

    public float $pet_fee_per_pet = 0;

    public float $laundry_fee = 0;

    public float $deep_cleaning_surcharge = 0;

    /** @var array<string, float> */
    public array $base_rates_by_size = [];

    /** @var array<string, float> */
    public array $frequency_discounts = [];

    public function mount(): void
    {
        $settings = PricingSetting::current();

        $this->base_flat_fee = (float) $settings->base_flat_fee;
        $this->per_bedroom_rate = (float) $settings->per_bedroom_rate;
        $this->per_bathroom_rate = (float) $settings->per_bathroom_rate;
        $this->pet_fee_per_pet = (float) $settings->pet_fee_per_pet;
        $this->laundry_fee = (float) $settings->laundry_fee;
        $this->deep_cleaning_surcharge = (float) $settings->deep_cleaning_surcharge;
        $this->base_rates_by_size = $settings->base_rates_by_size ?? [];
        $this->frequency_discounts = $settings->frequency_discounts ?? [];
    }

    public function save(): void
    {
        $this->validate([
            'base_flat_fee' => ['required', 'numeric', 'min:0'],
            'per_bedroom_rate' => ['required', 'numeric', 'min:0'],
            'per_bathroom_rate' => ['required', 'numeric', 'min:0'],
            'pet_fee_per_pet' => ['required', 'numeric', 'min:0'],
            'laundry_fee' => ['required', 'numeric', 'min:0'],
            'deep_cleaning_surcharge' => ['required', 'numeric', 'min:0'],
            'base_rates_by_size.*' => ['required', 'numeric', 'min:0'],
            'frequency_discounts.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        PricingSetting::current()->update([
            'base_flat_fee' => $this->base_flat_fee,
            'per_bedroom_rate' => $this->per_bedroom_rate,
            'per_bathroom_rate' => $this->per_bathroom_rate,
            'pet_fee_per_pet' => $this->pet_fee_per_pet,
            'laundry_fee' => $this->laundry_fee,
            'deep_cleaning_surcharge' => $this->deep_cleaning_surcharge,
            'base_rates_by_size' => $this->base_rates_by_size,
            'frequency_discounts' => $this->frequency_discounts,
        ]);

        Flux::toast(variant: 'success', text: __('Pricing rules saved.'));
    }

    /** @return array<int, JobFrequency> */
    public function frequenciesWithDiscount(): array
    {
        return array_values(array_filter(JobFrequency::cases(), fn (JobFrequency $f) => $f !== JobFrequency::OneTime));
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Admin') }}
        </span>

        <flux:heading size="xl" class="text-primary">{{ __('Pricing rules') }}</flux:heading>

        <flux:subheading>
            {{ __('These numbers drive the estimate shown to customers on the job request form. Edit them any time — no code change or document needed.') }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="flex flex-col gap-8">
        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:heading class="mb-1 text-primary">{{ __('Residential base rates') }}</flux:heading>
            <flux:text class="mb-4 text-text/60">
                {{ __('Used when the customer provides bedroom/bathroom counts on a residential job.') }}
            </flux:text>
            <div class="grid gap-4 sm:grid-cols-3">
                <flux:input type="number" step="0.01" min="0" wire:model="base_flat_fee" :label="__('Base flat fee ($)')" />
                <flux:input type="number" step="0.01" min="0" wire:model="per_bedroom_rate" :label="__('Per bedroom ($)')" />
                <flux:input type="number" step="0.01" min="0" wire:model="per_bathroom_rate" :label="__('Per bathroom ($)')" />
            </div>
        </div>

        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:heading class="mb-1 text-primary">{{ __('Rates by property size') }}</flux:heading>
            <flux:text class="mb-4 text-text/60">
                {{ __('Fallback used for commercial/other property types, or residential jobs without bedroom/bathroom counts.') }}
            </flux:text>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($base_rates_by_size as $size => $rate)
                    <flux:input type="number" step="0.01" min="0" wire:model="base_rates_by_size.{{ $size }}" :label="$size" />
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:heading class="mb-4 text-primary">{{ __('Add-on fees') }}</flux:heading>
            <div class="grid gap-4 sm:grid-cols-3">
                <flux:input type="number" step="0.01" min="0" wire:model="pet_fee_per_pet" :label="__('Pet fee, per pet ($)')" />
                <flux:input type="number" step="0.01" min="0" wire:model="laundry_fee" :label="__('Laundry add-on ($)')" />
                <flux:input type="number" step="0.01" min="0" wire:model="deep_cleaning_surcharge" :label="__('Deep cleaning surcharge ($)')" />
            </div>
        </div>

        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:heading class="mb-1 text-primary">{{ __('Recurring service discounts') }}</flux:heading>
            <flux:text class="mb-4 text-text/60">
                {{ __('Percentage off the base estimate. One-time jobs never get a discount.') }}
            </flux:text>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($this->frequenciesWithDiscount() as $frequency)
                    <flux:input type="number" step="1" min="0" max="100" wire:model="frequency_discounts.{{ $frequency->value }}" :label="$frequency->label().' (%)'" />
                @endforeach
            </div>
        </div>

        <div>
            <flux:button type="submit" variant="primary">{{ __('Save pricing rules') }}</flux:button>
        </div>
    </form>
</div>
