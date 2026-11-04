@php
    $homeHref = request()->routeIs('home') ? '#hero' : route('home');
    $servicesHref = request()->routeIs('home') ? '#services' : route('home').'#services';
    $testimonialsHref = request()->routeIs('home') ? '#testimonials' : route('home').'#testimonials';
    $contactHref = request()->routeIs('home') ? '#contact' : route('home').'#contact';
@endphp

<header class="sticky top-0 z-50 border-b border-secondary bg-background/80 backdrop-blur-xl">
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 sm:px-8">
        <a href="{{ $homeHref }}" class="flex items-center gap-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary">
                <flux:icon.sparkles class="size-5 text-gold" />
            </span>
            <span class="text-xl font-semibold tracking-tight text-primary">NGM Cleaning</span>
        </a>

        <div class="hidden items-center gap-8 md:flex">
            <a href="{{ $servicesHref }}" class="text-sm text-text transition-colors hover:text-primary">Services</a>
            <a href="{{ $testimonialsHref }}" class="text-sm text-text transition-colors hover:text-primary">Reviews</a>
            <a href="{{ route('become-a-cleaner') }}" class="text-sm text-text transition-colors hover:text-primary">Become a Cleaning Partner</a>

            @auth
                <a href="{{ route('dashboard') }}" class="rounded-full bg-primary px-5 py-2.5 text-sm text-background transition-all hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-text transition-colors hover:text-primary">Log in</a>
                <a href="{{ $contactHref }}" class="rounded-full border border-primary/30 px-5 py-2.5 text-sm text-primary transition-all hover:-translate-y-0.5 hover:border-primary hover:bg-secondary hover:shadow-lg hover:shadow-primary/10">
                    Have a Question?
                </a>
                <a href="{{ route('quotes.residential.create') }}" class="rounded-full bg-primary px-5 py-2.5 text-sm text-background transition-all hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
                    Free Quote
                </a>
            @endauth
        </div>

        <div x-data="{ open: false }" class="md:hidden">
            <button @click="open = !open" class="text-primary" aria-label="Toggle menu">
                <flux:icon.bars-3 class="size-6" />
            </button>

            <div x-show="open" x-cloak class="absolute inset-x-0 top-full border-t border-secondary bg-background">
                <div class="flex flex-col gap-4 px-6 py-4">
                    <a @click="open = false" href="{{ $servicesHref }}" class="text-sm text-text">Services</a>
                    <a @click="open = false" href="{{ $testimonialsHref }}" class="text-sm text-text">Reviews</a>
                    <a @click="open = false" href="{{ route('become-a-cleaner') }}" class="text-sm text-text">Become a Cleaning Partner</a>

                    @auth
                        <a @click="open = false" href="{{ route('dashboard') }}" class="rounded-full bg-primary px-5 py-2.5 text-center text-sm text-background">
                            Dashboard
                        </a>
                    @else
                        <a @click="open = false" href="{{ route('login') }}" class="text-sm text-text">Log in</a>
                        <a @click="open = false" href="{{ $contactHref }}" class="rounded-full border border-primary/30 px-5 py-2.5 text-center text-sm text-primary">
                            Have a Question?
                        </a>
                        <a @click="open = false" href="{{ route('quotes.residential.create') }}" class="rounded-full bg-primary px-5 py-2.5 text-center text-sm text-background">
                            Free Quote
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
