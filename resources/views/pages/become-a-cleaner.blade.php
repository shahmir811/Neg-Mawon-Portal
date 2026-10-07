<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Become a Cleaning Professional'])
    </head>
    <body class="bg-background text-text">
        <header class="sticky top-0 z-50 border-b border-secondary bg-background/80 backdrop-blur-xl">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary">
                        <flux:icon.sparkles class="size-5 text-gold" />
                    </span>
                    <span class="text-xl font-semibold tracking-tight text-primary">NGM Cleaning</span>
                </a>

                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="hidden text-sm text-text transition-colors hover:text-primary sm:inline">Home</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full bg-primary px-5 py-2.5 text-sm text-background transition-all hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden text-sm text-text transition-colors hover:text-primary sm:inline">Log in</a>
                        <a href="{{ route('register', ['role' => 'cleaner']) }}" class="rounded-full bg-primary px-5 py-2.5 text-sm text-background transition-all hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
                            Apply Now
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <section class="relative overflow-hidden bg-background">
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-br from-background via-secondary/60 to-background"></div>
                <div class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-primary/10 blur-3xl"></div>
                <div class="absolute -right-32 bottom-10 h-96 w-96 rounded-full bg-gold/15 blur-3xl"></div>
            </div>

            <div class="mx-auto max-w-4xl px-6 py-20 text-center sm:px-8 md:py-28">
                <span class="mb-6 inline-block rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">Join Our Network</span>

                <h1 class="mx-auto max-w-3xl text-5xl font-medium leading-[1.05] tracking-tight text-primary md:text-6xl">
                    Clean on your schedule.<br>
                    <em class="font-normal not-italic italic text-gold">Get paid for your work.</em>
                </h1>

                <p class="mx-auto mt-8 max-w-2xl text-lg leading-relaxed text-text/75 md:text-xl">
                    NGM Cleaning is building a trusted network of cleaning professionals across Pennsylvania, New Jersey, and Delaware. Set your own availability, work jobs near you, and get paid for every clean.
                </p>

                <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <a href="{{ route('register', ['role' => 'cleaner']) }}" class="group inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 font-semibold text-background shadow-xl shadow-primary/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-primary/30">
                        Apply Now
                        <flux:icon.arrow-right class="size-5 transition-transform group-hover:translate-x-1" />
                    </a>
                    <a href="#requirements" class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-white/70 px-8 py-4 font-semibold text-primary backdrop-blur-xl transition-all duration-300 hover:border-gold hover:bg-white">
                        See Requirements
                    </a>
                </div>
            </div>
        </section>

        <section class="bg-background py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="mb-16 text-center">
                    <span class="mb-5 inline-block rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">Why Join NGM</span>
                    <h2 class="mb-5 text-4xl font-medium tracking-tight text-primary md:text-5xl">Work that fits your life.</h2>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ([
                        ['icon' => 'calendar-check', 'title' => 'Flexible Schedule', 'body' => 'Tell us the days and hours you\'re available. Work as little or as much as fits your life.'],
                        ['icon' => 'wallet', 'title' => 'Competitive Pay', 'body' => 'Get paid for the jobs you complete, with clear expectations set before you ever accept one.'],
                        ['icon' => 'map-pin', 'title' => 'Jobs Near You', 'body' => 'We match you with cleaning jobs in your area, so you spend less time commuting and more time earning.'],
                    ] as $index => $benefit)
                        <div class="group relative rounded-3xl border border-secondary bg-surface p-8 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl">
                            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary transition-transform duration-500 group-hover:scale-110">
                                <flux:icon :name="$benefit['icon']" class="size-7 text-gold" />
                            </div>
                            <h3 class="mb-3 text-2xl font-medium text-primary">{{ $benefit['title'] }}</h3>
                            <p class="text-base leading-relaxed text-text/70">{{ $benefit['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="requirements" class="bg-secondary py-20 md:py-28">
            <div class="mx-auto max-w-4xl px-6 sm:px-8">
                <div class="mb-14 text-center">
                    <span class="mb-5 inline-block rounded-full bg-background px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">Requirements</span>
                    <h2 class="mb-5 text-4xl font-medium tracking-tight text-primary md:text-5xl">What it takes to join.</h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-text/75">Every applicant is reviewed before joining the network. Here's what we look for.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ([
                        '18 years or older',
                        'Legally authorized to work in the U.S.',
                        'Reliable transportation to job sites',
                        'A smartphone to receive job details',
                        'Prior cleaning or housekeeping experience preferred',
                        'Able to pass a background check',
                    ] as $requirement)
                        <div class="flex items-start gap-3 rounded-2xl bg-background/70 p-5">
                            <flux:icon.check-circle class="mt-0.5 size-5 shrink-0 text-primary" />
                            <p class="text-base text-text/85">{{ $requirement }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden py-20 md:py-28" style="background: linear-gradient(135deg, var(--color-primary) 0%, #0a3329 50%, var(--color-primary) 100%);">
            <div class="absolute left-1/4 top-0 h-96 w-96 rounded-full bg-gold opacity-30 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 h-[500px] w-[500px] rounded-full bg-secondary opacity-20 blur-3xl"></div>

            <div class="relative mx-auto max-w-3xl px-6 text-center sm:px-8">
                <h2 class="mb-4 text-4xl font-normal leading-tight text-background md:text-5xl">
                    Ready to start <em class="not-italic text-gold">earning?</em>
                </h2>
                <p class="mx-auto mb-10 max-w-xl text-lg leading-relaxed text-secondary/85">
                    The application takes a few minutes. Once submitted, our team will review it and follow up with next steps.
                </p>
                <a href="{{ route('register', ['role' => 'cleaner']) }}" class="group inline-flex items-center gap-2 rounded-full px-8 py-4 font-semibold text-primary shadow-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl" style="background: linear-gradient(135deg, var(--color-gold), #b8892e);">
                    Start Your Application
                    <flux:icon.arrow-right class="size-5 transition-transform group-hover:translate-x-1" />
                </a>
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
