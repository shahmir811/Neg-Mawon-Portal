<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Get a Residential Quote'])
    </head>
    <body class="bg-background text-text">
        <x-site-nav />

        <section class="relative overflow-hidden py-20 md:py-28" style="background: linear-gradient(135deg, var(--color-primary) 0%, #0a3329 50%, var(--color-primary) 100%);">
            <div class="absolute left-1/4 top-0 h-96 w-96 rounded-full bg-gold opacity-30 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 h-[500px] w-[500px] rounded-full bg-secondary opacity-20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, #FAF7F2 1px, transparent 0); background-size: 40px 40px;"></div>

            <div class="relative mx-auto max-w-3xl px-6 sm:px-8">
                <div class="mb-10 text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold/15 px-4 py-2 backdrop-blur-xl">
                        <flux:icon.sparkles class="size-4 text-gold" />
                        <span class="text-xs font-semibold uppercase tracking-widest text-gold">Find a Cleaning Professional</span>
                    </div>
                    <h1 class="mb-4 text-4xl font-normal leading-tight text-background md:text-5xl">
                        Request your <em class="not-italic text-gold">free residential quote</em>
                    </h1>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-secondary/85">
                        Tell us about your home and we'll get back to you within 24 hours — one-time or recurring.
                    </p>
                </div>

                <div class="mb-8 flex justify-center gap-2">
                    <a href="{{ route('quotes.residential.create') }}" class="flex-1 max-w-[160px] cursor-pointer rounded-full border px-5 py-2.5 text-center text-sm font-semibold transition-colors border-gold bg-gold/15 text-gold">
                        Residential
                    </a>
                    <a href="{{ route('quotes.commercial.create') }}" class="flex-1 max-w-[160px] cursor-pointer rounded-full border px-5 py-2.5 text-center text-sm font-semibold transition-colors border-white/20 text-secondary/70">
                        Commercial
                    </a>
                </div>

                @if (session('status') === 'quote-sent')
                    <p class="relative mb-6 rounded-xl border border-gold/30 bg-gold/15 px-4 py-3 text-center text-sm text-gold">
                        Thanks! Your quote request is on its way to James — we'll get back to you within 24 hours.
                    </p>
                @endif

                <div class="relative rounded-3xl border border-white/15 bg-background/8 p-8 shadow-2xl backdrop-blur-2xl md:p-10 lg:p-12">
                    <div class="absolute -right-3 -top-3 h-24 w-24 rounded-full bg-gold opacity-40 blur-2xl"></div>

                    <form method="POST" action="{{ route('quotes.residential.store') }}" class="relative space-y-6">
                        @csrf

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="r_name" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Full Name</label>
                                <input type="text" id="r_name" name="name" required value="{{ old('name') }}" class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Marie Joseph">
                                @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="r_email" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Email Address</label>
                                <input type="email" id="r_email" name="email" required value="{{ old('email') }}" class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold" placeholder="you@example.com">
                                @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="r_phone" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Phone Number</label>
                                <input type="tel" id="r_phone" name="phone" required value="{{ old('phone') }}" class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold" placeholder="(267) 555-0123">
                                @error('phone') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="r_zip_code" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Zip Code</label>
                                <input type="text" id="r_zip_code" name="zip_code" required value="{{ old('zip_code') }}" class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold" placeholder="19111">
                                @error('zip_code') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="r_service_type" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Service Type</label>
                                <select id="r_service_type" name="service_type" required class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold">
                                    <option value="" class="text-ink-900">Select a service</option>
                                    @foreach (\App\Enums\ServiceType::cases() as $type)
                                        <option value="{{ $type->value }}" class="text-ink-900" @selected(old('service_type') === $type->value)>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                @error('service_type') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="r_frequency" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Frequency</label>
                                <select id="r_frequency" name="frequency" required class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold">
                                    <option value="" class="text-ink-900">Select a frequency</option>
                                    @foreach (\App\Enums\JobFrequency::cases() as $frequency)
                                        <option value="{{ $frequency->value }}" class="text-ink-900" @selected(old('frequency') === $frequency->value)>{{ $frequency->label() }}</option>
                                    @endforeach
                                </select>
                                @error('frequency') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="r_preferred_date" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Preferred Date (optional)</label>
                            <input type="date" id="r_preferred_date" name="preferred_date" value="{{ old('preferred_date') }}" class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold">
                            @error('preferred_date') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="r_property_details" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Property Details</label>
                            <input type="text" id="r_property_details" name="property_details" required value="{{ old('property_details') }}" class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold" placeholder="3 bedrooms, 2 bathrooms, ~1,400 sq ft">
                            @error('property_details') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="r_description" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Tell Us About Your Home</label>
                            <textarea id="r_description" name="description" rows="4" required class="w-full resize-none rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Anything else we should know?">{{ old('description') }}</textarea>
                            @error('description') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-xl px-8 py-4 text-base font-semibold text-primary transition-all hover:-translate-y-0.5 hover:shadow-2xl" style="background: linear-gradient(135deg, var(--color-gold), #b8892e);">
                            <span>Request My Residential Quote</span>
                            <flux:icon.arrow-right class="size-5 transition-transform group-hover:translate-x-1" />
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-sm text-secondary/60">
                    Have a general question instead? <a href="{{ route('home') }}#contact" class="font-semibold text-gold hover:underline">Contact us</a>
                </p>
            </div>
        </section>

        <footer class="relative overflow-hidden bg-primary text-background">
            <div class="relative mx-auto max-w-6xl px-6 py-10 text-center sm:px-8">
                <p class="text-xs text-secondary/50">
                    &copy; {{ now()->year }} NGM Cleaning Services LLC. All rights reserved.
                </p>
                <a href="{{ route('home') }}" class="mt-3 inline-block text-xs text-secondary/60 hover:text-gold">Back to Home</a>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
