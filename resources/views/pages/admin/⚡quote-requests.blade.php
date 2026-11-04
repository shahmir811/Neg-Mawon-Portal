<?php

use App\Enums\QuoteType;
use App\Models\QuoteRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Quote Requests')] class extends Component
{
    public ?string $typeFilter = null;

    public ?int $viewingId = null;

    public bool $showModal = false;

    public function show(int $id): void
    {
        $this->viewingId = $id;
        $this->showModal = true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function quoteRequests(): array
    {
        return QuoteRequest::query()
            ->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->latest()
            ->get()
            ->map(fn (QuoteRequest $quoteRequest) => [
                'id' => $quoteRequest->id,
                'type' => $quoteRequest->type,
                'name' => $quoteRequest->name,
                'email' => $quoteRequest->email,
                'phone' => $quoteRequest->phone,
                'zip_code' => $quoteRequest->zip_code,
                'service_type' => $quoteRequest->service_type,
                'business_type' => $quoteRequest->business_type,
                'property_details' => $quoteRequest->property_details,
                'frequency' => $quoteRequest->frequency,
                'preferred_date' => $quoteRequest->preferred_date,
                'description' => $quoteRequest->description,
                'created_at' => $quoteRequest->created_at,
            ])
            ->all();
    }

    #[Computed]
    public function viewing(): ?array
    {
        if (! $this->viewingId) {
            return null;
        }

        return collect($this->quoteRequests)->firstWhere('id', $this->viewingId);
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Admin') }}
        </span>

        <flux:heading size="xl" class="text-primary">{{ __('Quote Requests') }}</flux:heading>

        <flux:subheading>
            {{ __('Every residential and commercial quote request submitted through the website.') }}
        </flux:subheading>
    </div>

    <div class="flex gap-2">
        <flux:button size="sm" :variant="$typeFilter === null ? 'primary' : 'ghost'" wire:click="$set('typeFilter', null)">
            {{ __('All') }}
        </flux:button>
        <flux:button size="sm" :variant="$typeFilter === QuoteType::Residential->value ? 'primary' : 'ghost'" wire:click="$set('typeFilter', '{{ QuoteType::Residential->value }}')">
            {{ __('Residential') }}
        </flux:button>
        <flux:button size="sm" :variant="$typeFilter === QuoteType::Commercial->value ? 'primary' : 'ghost'" wire:click="$set('typeFilter', '{{ QuoteType::Commercial->value }}')">
            {{ __('Commercial') }}
        </flux:button>
    </div>

    @if (count($this->quoteRequests) > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Contact') }}</flux:table.column>
                <flux:table.column>{{ __('Summary') }}</flux:table.column>
                <flux:table.column>{{ __('Submitted') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->quoteRequests as $quoteRequest)
                    <flux:table.row wire:key="quote-request-{{ $quoteRequest['id'] }}">
                        <flux:table.cell>
                            <flux:badge :color="$quoteRequest['type'] === QuoteType::Residential ? 'blue' : 'amber'">
                                {{ $quoteRequest['type']->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $quoteRequest['name'] }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $quoteRequest['email'] }}
                            <flux:text class="text-text/70">{{ $quoteRequest['phone'] }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $quoteRequest['type'] === QuoteType::Residential ? $quoteRequest['service_type']?->label() : $quoteRequest['business_type']?->label() }}
                            <flux:text class="text-text/70">{{ $quoteRequest['frequency']->label() }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>{{ $quoteRequest['created_at']->format('M j, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" wire:click="show({{ $quoteRequest['id'] }})">
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
            <flux:text>{{ __('No quote requests submitted yet.') }}</flux:text>
        </div>
    @endif

    <flux:modal wire:model.self="showModal" class="w-full max-w-xl">
        @if ($this->viewing)
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <flux:heading size="lg" class="text-primary">
                        {{ $this->viewing['name'] }}
                    </flux:heading>
                    <flux:badge :color="$this->viewing['type'] === QuoteType::Residential ? 'blue' : 'amber'">
                        {{ $this->viewing['type']->label() }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <flux:text class="text-text/60">{{ __('Email') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewing['email'] }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-text/60">{{ __('Phone') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewing['phone'] }}</flux:text>
                    </div>

                    @if ($this->viewing['type'] === QuoteType::Residential)
                        <div>
                            <flux:text class="text-text/60">{{ __('Zip code') }}</flux:text>
                            <flux:text class="text-primary">{{ $this->viewing['zip_code'] }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-text/60">{{ __('Service type') }}</flux:text>
                            <flux:text class="text-primary">{{ $this->viewing['service_type']?->label() }}</flux:text>
                        </div>
                        @if ($this->viewing['preferred_date'])
                            <div>
                                <flux:text class="text-text/60">{{ __('Preferred date') }}</flux:text>
                                <flux:text class="text-primary">{{ $this->viewing['preferred_date']->format('M j, Y') }}</flux:text>
                            </div>
                        @endif
                    @else
                        <div>
                            <flux:text class="text-text/60">{{ __('Business type') }}</flux:text>
                            <flux:text class="text-primary">{{ $this->viewing['business_type']?->label() }}</flux:text>
                        </div>
                    @endif

                    <div>
                        <flux:text class="text-text/60">{{ __('Frequency') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewing['frequency']->label() }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:text class="text-text/60">{{ __('Property details') }}</flux:text>
                    <flux:text class="text-primary">{{ $this->viewing['property_details'] }}</flux:text>
                </div>

                <div>
                    <flux:text class="text-text/60">{{ __('Description') }}</flux:text>
                    <flux:text class="whitespace-pre-line text-primary">{{ $this->viewing['description'] }}</flux:text>
                </div>

                <flux:text class="text-text/60">
                    {{ __('Submitted :date', ['date' => $this->viewing['created_at']->format('M j, Y \a\t g:i A')]) }}
                </flux:text>
            </div>
        @endif
    </flux:modal>
</div>
