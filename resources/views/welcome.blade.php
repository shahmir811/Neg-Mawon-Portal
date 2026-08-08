<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Trusted House Cleaning in Northeast Philadelphia'])

        <style>
            @media (prefers-reduced-motion: no-preference) {
                html {
                    scroll-behavior: smooth;
                }
            }

            .animate-on-scroll {
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }

            .animate-on-scroll.visible {
                opacity: 1;
                transform: translateY(0);
            }

            @media (prefers-reduced-motion: reduce) {
                .animate-on-scroll {
                    opacity: 1;
                    transform: none;
                    transition: none;
                }
            }
        </style>
    </head>
    <body class="bg-background text-text">
        <header class="sticky top-0 z-50 border-b border-secondary bg-background/80 backdrop-blur-xl">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 sm:px-8">
                <a href="#hero" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary">
                        <flux:icon.sparkles class="size-5 text-gold" />
                    </span>
                    <span class="text-xl font-semibold tracking-tight text-primary">Nèg Mawon</span>
                </a>

                <div class="hidden items-center gap-8 md:flex">
                    <a href="#services" class="text-sm text-text transition-colors hover:text-primary">Services</a>
                    <a href="#about" class="text-sm text-text transition-colors hover:text-primary">About</a>
                    <a href="#process" class="text-sm text-text transition-colors hover:text-primary">Process</a>
                    <a href="#testimonials" class="text-sm text-text transition-colors hover:text-primary">Reviews</a>
                    <a href="#faq" class="text-sm text-text transition-colors hover:text-primary">FAQ</a>
                    <a href="tel:+12676901707" class="flex items-center gap-1.5 text-sm font-semibold text-primary">
                        <flux:icon.phone class="size-4" />
                        (267) 690-1707
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full bg-primary px-5 py-2.5 text-sm text-background transition-all hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-text transition-colors hover:text-primary">Log in</a>
                        <a href="#contact" class="rounded-full bg-primary px-5 py-2.5 text-sm text-background transition-all hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
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
                            <a @click="open = false" href="#services" class="text-sm text-text">Services</a>
                            <a @click="open = false" href="#about" class="text-sm text-text">About</a>
                            <a @click="open = false" href="#process" class="text-sm text-text">Process</a>
                            <a @click="open = false" href="#testimonials" class="text-sm text-text">Reviews</a>
                            <a @click="open = false" href="#faq" class="text-sm text-text">FAQ</a>
                            <a @click="open = false" href="tel:+12676901707" class="text-sm font-semibold text-primary">(267) 690-1707</a>

                            @auth
                                <a @click="open = false" href="{{ route('dashboard') }}" class="rounded-full bg-primary px-5 py-2.5 text-center text-sm text-background">
                                    Dashboard
                                </a>
                            @else
                                <a @click="open = false" href="{{ route('login') }}" class="text-sm text-text">Log in</a>
                                <a @click="open = false" href="#contact" class="rounded-full bg-primary px-5 py-2.5 text-center text-sm text-background">
                                    Free Quote
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <section id="hero" class="relative overflow-hidden bg-background">
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-br from-background via-secondary/60 to-background"></div>
                <div class="absolute -left-32 top-20 h-96 w-96 animate-pulse rounded-full bg-primary/10 blur-3xl" style="animation-duration: 6s;"></div>
                <div class="absolute -right-32 bottom-10 h-96 w-96 animate-pulse rounded-full bg-gold/15 blur-3xl" style="animation-duration: 8s;"></div>
            </div>

            <div class="mx-auto max-w-6xl px-6 py-20 sm:px-8 md:py-28 lg:py-32">
                <div class="flex flex-col items-center text-center">

                    <div class="mb-8 inline-flex animate-pulse items-center gap-2 rounded-full border border-secondary bg-white/60 px-4 py-2 shadow-sm backdrop-blur-xl" style="animation-duration: 3s;">
                        <div class="flex items-center gap-0.5 text-gold">
                            @for ($i = 0; $i < 5; $i++)
                                <flux:icon.star variant="solid" class="size-4" />
                            @endfor
                        </div>
                        <span class="text-sm font-semibold text-primary">5.0 on Google Reviews</span>
                    </div>

                    <p class="mb-6 text-xs uppercase tracking-[0.25em] text-primary/70 md:text-sm">
                        Northeast Philadelphia &middot; Family-Owned &middot; Haitian-American
                    </p>

                    <h1 class="max-w-4xl text-5xl font-medium leading-[1.05] tracking-tight text-primary md:text-6xl lg:text-7xl">
                        A Spotless Home,<br>
                        <em class="font-normal not-italic italic text-gold">Cleaned With Pride.</em>
                    </h1>

                    <p class="mt-8 max-w-2xl text-lg leading-relaxed text-text/75 md:text-xl">
                        Nèg Mawon Cleaning Services is Northeast Philly's trusted 5-star house cleaning team – offering standard cleans, deep cleans, house clearance, and professional organizing for busy Philadelphia families.
                    </p>

                    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row">
                        <a href="tel:+12676901707" class="group inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 font-semibold text-background shadow-xl shadow-primary/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-primary/30">
                            <flux:icon.phone class="size-5" />
                            Call (267) 690-1707
                        </a>
                        <a href="#contact" class="group inline-flex items-center gap-2 rounded-full border border-primary/20 bg-white/70 px-8 py-4 font-semibold text-primary backdrop-blur-xl transition-all duration-300 hover:border-gold hover:bg-white">
                            Get a Free Quote
                            <flux:icon.arrow-right class="size-5 transition-transform group-hover:translate-x-1" />
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-text/60">
                        <span class="flex items-center gap-1.5"><flux:icon.shield-check class="size-4 text-primary" /> Fully Insured</span>
                        <span class="flex items-center gap-1.5"><flux:icon.calendar-check class="size-4 text-primary" /> Mon–Sat 9AM–5PM</span>
                        <span class="flex items-center gap-1.5"><flux:icon.map-pin class="size-4 text-primary" /> Serving NE Philadelphia</span>
                    </div>

                    <div class="relative mt-16 w-full max-w-5xl">
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-primary/10 via-gold/20 to-primary/10 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-3xl border border-white/60 shadow-2xl">
                            <img
                                src="https://zgnpmogdjnnhpwewavnr.supabase.co/storage/v1/object/public/project-images/6aeb0c72-051a-4b62-aa01-435fcb5871fe/da9c7a5f-9714-4e81-9dbd-846fac18696d.png"
                                alt="Bright, sunlit, spotlessly cleaned Philadelphia row home living room with polished hardwood floors and pristine furniture after professional cleaning by Nèg Mawon Cleaning Services"
                                class="h-auto w-full object-cover"
                                loading="eager"
                            >
                            <div class="absolute bottom-6 left-6 right-6 md:left-8 md:right-auto md:max-w-xs">
                                <div class="rounded-2xl border border-white/60 bg-white/80 p-4 shadow-xl backdrop-blur-xl">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary">
                                            <flux:icon.sparkles class="size-5 text-gold" />
                                        </div>
                                        <div class="text-left">
                                            <p class="text-xs uppercase tracking-wider text-primary/70">Freedom to Rest</p>
                                            <p class="text-sm font-semibold text-text">We handle the cleaning.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section id="services" class="bg-background py-20 md:py-28 lg:py-32">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="animate-on-scroll mb-16 text-center">
                    <span class="mb-5 inline-block rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">What We Offer</span>
                    <h2 class="mb-5 text-4xl font-medium tracking-tight text-primary md:text-5xl lg:text-6xl">Cleaning services built<br>around your home</h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-text/75">From weekly upkeep to full move-out clearances, our Northeast Philly team treats every home with the care it deserves.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['icon' => 'sparkles', 'title' => 'Standard Cleaning', 'body' => 'Weekly or bi-weekly upkeep – dusting, vacuuming, mopping, kitchens, and baths done right every time.'],
                        ['icon' => 'spray-can', 'title' => 'Deep Cleaning', 'body' => 'Baseboards, grout, inside appliances, vents – the works. Perfect for seasonal resets and move-in shine.'],
                        ['icon' => 'package', 'title' => 'House Clearance', 'body' => 'Estate cleanouts, post-renovation debris, garage clearances – hauled, sorted, and left spotless.'],
                        ['icon' => 'layout-grid', 'title' => 'Professional Organizing', 'body' => 'Closets, pantries, playrooms – transformed into calm, functional spaces you\'ll actually maintain.'],
                    ] as $index => $service)
                        <div class="animate-on-scroll group relative rounded-3xl border border-secondary bg-surface p-8 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl" style="transition-delay: {{ $index * 100 }}ms;">
                            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary transition-transform duration-500 group-hover:scale-110">
                                <flux:icon :name="$service['icon']" class="size-7 text-gold" />
                            </div>
                            <h3 class="mb-3 text-2xl font-medium text-primary">{{ $service['title'] }}</h3>
                            <p class="mb-5 text-base leading-relaxed text-text/70">{{ $service['body'] }}</p>
                            <div class="h-0.5 w-0 bg-gold transition-all duration-500 group-hover:w-16"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="about" class="bg-secondary py-20 md:py-28 lg:py-32">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="grid items-center gap-16 lg:grid-cols-2 lg:gap-20">
                    <div class="animate-on-scroll order-2 lg:order-1">
                        <div class="relative">
                            <div class="absolute -left-6 -top-6 -z-10 h-32 w-32 rounded-3xl bg-gold/40"></div>
                            <img
                                src="https://zgnpmogdjnnhpwewavnr.supabase.co/storage/v1/object/public/project-images/6aeb0c72-051a-4b62-aa01-435fcb5871fe/34b5ced2-6473-45a0-b24b-612534982133.png"
                                alt="Friendly Haitian-American cleaning team from Nèg Mawon smiling in a bright Philadelphia home"
                                class="relative h-auto w-full rounded-3xl shadow-2xl"
                            >
                            <div class="absolute -bottom-6 -right-6 -z-10 h-32 w-32 rounded-3xl bg-primary"></div>
                        </div>
                    </div>
                    <div class="animate-on-scroll order-1 lg:order-2">
                        <span class="mb-5 inline-block rounded-full bg-background px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">Our Story</span>
                        <h2 class="mb-6 text-4xl font-medium leading-[1.05] tracking-tight text-primary md:text-5xl lg:text-6xl">A family name rooted in freedom.</h2>
                        <p class="mb-5 text-lg leading-relaxed text-text/85"><em class="font-semibold not-italic text-primary">"Nèg Mawon"</em> is a proud Haitian symbol of freedom – the maroon who broke chains and lived on his own terms. That spirit is stitched into everything we do.</p>
                        <p class="mb-8 text-lg leading-relaxed text-text/75">We're a Haitian-American, family-run business proudly serving Northeast Philadelphia. Every home we clean, we treat like our grandmother's parlor – with warmth, respect, and honest work. No shortcuts. No surprises. Just spotless.</p>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary">
                                    <flux:icon.shield-check class="size-5 text-gold" />
                                </div>
                                <div>
                                    <h4 class="mb-1 font-semibold text-primary">Fully Insured</h4>
                                    <p class="text-sm text-text/70">Licensed LLC &amp; bonded</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary">
                                    <flux:icon.heart-handshake class="size-5 text-gold" />
                                </div>
                                <div>
                                    <h4 class="mb-1 font-semibold text-primary">Family-Run</h4>
                                    <p class="text-sm text-text/70">Local, trusted, personal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="process" class="bg-background py-20 md:py-28 lg:py-32">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="animate-on-scroll mb-20 text-center">
                    <span class="mb-5 inline-block rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">How It Works</span>
                    <h2 class="mb-5 text-4xl font-medium tracking-tight text-primary md:text-5xl lg:text-6xl">Booking a clean is simple.</h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-text/75">Four easy steps from first call to fresh-smelling home.</p>
                </div>

                <div class="relative">
                    <div class="absolute left-[12.5%] right-[12.5%] top-10 hidden h-0.5 bg-gold/30 lg:block"></div>

                    <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                        @foreach ([
                            ['icon' => 'phone-call', 'title' => 'Request a Quote', 'body' => 'Call or text us the size of your home and what you need – we\'ll respond same day with a flat price.'],
                            ['icon' => 'calendar-check', 'title' => 'Schedule Your Visit', 'body' => 'Pick a day Monday–Saturday that fits your schedule. One-time or recurring, we work around you.'],
                            ['icon' => 'sparkles', 'title' => 'Professional Clean', 'body' => 'Our uniformed team arrives on time with supplies and gets to work – top to bottom, room by room.'],
                            ['icon' => 'home', 'title' => 'Enjoy Your Home', 'body' => 'Walk in, breathe deep, and relax. If anything\'s off, we come back and make it right – guaranteed.'],
                        ] as $index => $step)
                            <div class="animate-on-scroll text-center" style="transition-delay: {{ $index * 100 }}ms;">
                                <div class="relative mb-6 inline-flex items-center justify-center">
                                    <div class="relative z-10 flex h-20 w-20 items-center justify-center rounded-full bg-primary shadow-lg">
                                        <flux:icon :name="$step['icon']" class="size-8 text-gold" />
                                    </div>
                                    <span class="absolute -right-2 -top-2 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-gold text-sm font-bold text-primary">{{ $index + 1 }}</span>
                                </div>
                                <h3 class="mb-3 text-xl font-medium text-primary">{{ $step['title'] }}</h3>
                                <p class="text-base leading-relaxed text-text/70">{{ $step['body'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="bg-secondary py-20 md:py-28 lg:py-32">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="animate-on-scroll mb-16 text-center">
                    <span class="mb-5 inline-block rounded-full bg-background px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">Google Reviews</span>
                    <h2 class="mb-5 text-4xl font-medium tracking-tight text-primary md:text-5xl lg:text-6xl">Loved by Northeast Philly.</h2>
                    <div class="mb-3 flex items-center justify-center gap-2">
                        <div class="flex gap-1 text-gold">
                            @for ($i = 0; $i < 5; $i++)
                                <flux:icon.star variant="solid" class="size-5" />
                            @endfor
                        </div>
                        <span class="text-lg font-semibold text-primary">5.0 on Google</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ([
                        ['initials' => 'MJ', 'quote' => 'I\'ve never had a cleaning team leave my Rhawnhurst rowhome looking THIS good. Baseboards, oven, ceiling fans – everything sparkled. Truly a 5-star experience.', 'name' => 'Maria J.', 'location' => 'Rhawnhurst, PA'],
                        ['initials' => 'DP', 'quote' => 'Booked a deep clean before my mom moved in. The Nèg Mawon team was warm, punctual, and thorough. My kitchen actually smells like lemon now. Highly recommend!', 'name' => 'Devon P.', 'location' => 'Fox Chase, PA'],
                        ['initials' => 'AS', 'quote' => 'Hired them for a full house clearance after my dad passed. They handled everything with dignity, care, and hard work. A blessing to our family. Thank you.', 'name' => 'Angela S.', 'location' => 'Bustleton, PA'],
                    ] as $index => $review)
                        <div class="animate-on-scroll rounded-3xl border border-primary/10 bg-background/70 p-8 backdrop-blur-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl" style="transition-delay: {{ $index * 100 }}ms;">
                            <flux:icon.quote class="mb-5 size-10 text-gold" />
                            <div class="mb-5 flex gap-1 text-gold">
                                @for ($i = 0; $i < 5; $i++)
                                    <flux:icon.star variant="solid" class="size-4" />
                                @endfor
                            </div>
                            <p class="mb-6 text-base leading-relaxed text-text">&ldquo;{{ $review['quote'] }}&rdquo;</p>
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary font-bold text-gold">{{ $review['initials'] }}</div>
                                <div>
                                    <p class="font-semibold text-primary">{{ $review['name'] }}</p>
                                    <p class="text-sm text-text/60">{{ $review['location'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="contact" class="relative overflow-hidden py-20 md:py-28 lg:py-32" style="background: linear-gradient(135deg, var(--color-primary) 0%, #0a3329 50%, var(--color-primary) 100%);">
            <div class="absolute left-1/4 top-0 h-96 w-96 rounded-full bg-gold opacity-30 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 h-[500px] w-[500px] rounded-full bg-secondary opacity-20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, #FAF7F2 1px, transparent 0); background-size: 40px 40px;"></div>

            <div class="relative mx-auto max-w-6xl px-6 sm:px-8">
                <div class="mb-14 text-center md:mb-20">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold/15 px-4 py-2 backdrop-blur-xl">
                        <flux:icon.sparkles class="size-4 text-gold" />
                        <span class="text-xs font-semibold uppercase tracking-widest text-gold">Get In Touch</span>
                    </div>
                    <h2 class="mb-4 text-4xl font-normal leading-tight text-background md:text-5xl lg:text-6xl">
                        Ready for a <em class="not-italic text-gold">Spotless</em> Home?
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-secondary/85">
                        Request your free quote today. Our Northeast Philly team will get back to you within 24 hours.
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-5 lg:gap-10">

                    <div class="space-y-4 lg:col-span-2">
                        <div class="rounded-2xl border border-white/15 bg-white/8 p-6 shadow-2xl backdrop-blur-2xl transition-all duration-300 hover:-translate-y-1 md:p-7">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gold/20">
                                    <flux:icon.phone class="size-5 text-gold" />
                                </div>
                                <div>
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-gold">Call Us</p>
                                    <a href="tel:+12676901707" class="text-lg font-semibold text-background transition-opacity hover:opacity-80">(267) 690-1707</a>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white/8 p-6 shadow-2xl backdrop-blur-2xl transition-all duration-300 hover:-translate-y-1 md:p-7">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gold/20">
                                    <flux:icon.map-pin class="size-5 text-gold" />
                                </div>
                                <div>
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-gold">Visit Us</p>
                                    <p class="text-base leading-relaxed text-background">7135 Rising Sun Ave<br>Philadelphia, PA 19111</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white/8 p-6 shadow-2xl backdrop-blur-2xl transition-all duration-300 hover:-translate-y-1 md:p-7">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gold/20">
                                    <flux:icon.clock class="size-5 text-gold" />
                                </div>
                                <div>
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-gold">Hours</p>
                                    <p class="text-base leading-relaxed text-background">Mon – Sat: 9 AM – 5 PM<br><span class="text-secondary/60">Closed Sunday</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gold/30 p-6 shadow-2xl md:p-7" style="background: linear-gradient(135deg, rgba(200,155,60,0.15), rgba(200,155,60,0.05));">
                            <div class="mb-2 flex items-center gap-3">
                                <div class="flex gap-0.5 text-gold">
                                    @for ($i = 0; $i < 5; $i++)
                                        <flux:icon.star variant="solid" class="size-4" />
                                    @endfor
                                </div>
                                <span class="text-sm font-bold text-background">5.0 Google Rating</span>
                            </div>
                            <p class="text-sm text-secondary/85">Trusted by hundreds of Northeast Philly families.</p>
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <div class="relative rounded-3xl border border-white/15 bg-background/8 p-8 shadow-2xl backdrop-blur-2xl md:p-10 lg:p-12">
                            <div class="absolute -right-3 -top-3 h-24 w-24 rounded-full bg-gold opacity-40 blur-2xl"></div>

                            @if (session('status') === 'contact-sent')
                                <p class="relative mb-6 rounded-xl border border-gold/30 bg-gold/15 px-4 py-3 text-sm text-gold">
                                    Thanks! Your message is on its way to James — we'll get back to you within 24 hours.
                                </p>
                            @endif

                            <form method="POST" action="{{ route('contact.store') }}" class="relative space-y-6">
                                @csrf

                                <div>
                                    <label for="name" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Full Name</label>
                                    <input
                                        type="text" id="name" name="name" required value="{{ old('name') }}"
                                        class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold"
                                        placeholder="Marie Joseph"
                                    >
                                    @error('name')
                                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Email Address</label>
                                    <input
                                        type="email" id="email" name="email" required value="{{ old('email') }}"
                                        class="w-full rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold"
                                        placeholder="you@example.com"
                                    >
                                    @error('email')
                                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="message" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-gold">Tell Us About Your Home</label>
                                    <textarea
                                        id="message" name="message" rows="5" required
                                        class="w-full resize-none rounded-xl border border-white/20 bg-white/8 px-5 py-4 text-background placeholder:text-background/40 backdrop-blur-xl transition-all focus:outline-none focus:ring-2 focus:ring-gold"
                                        placeholder="How many bedrooms/bathrooms? Type of cleaning needed? Preferred date?"
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button
                                    type="submit"
                                    class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-xl px-8 py-4 text-base font-semibold text-primary transition-all hover:-translate-y-0.5 hover:shadow-2xl"
                                    style="background: linear-gradient(135deg, var(--color-gold), #b8892e);"
                                >
                                    <span>Request My Free Quote</span>
                                    <flux:icon.arrow-right class="size-5 transition-transform group-hover:translate-x-1" />
                                </button>

                                <p class="pt-2 text-center text-xs text-secondary/60">
                                    <flux:icon.shield-check class="mr-1 inline size-3" />
                                    Licensed, insured &amp; 100% satisfaction guaranteed
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="relative overflow-hidden bg-primary text-background">
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle at 2px 2px, #FAF7F2 1px, transparent 0); background-size: 32px 32px;"></div>
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold to-transparent"></div>

            <div class="relative mx-auto max-w-6xl px-6 pb-8 pt-20 sm:px-8">
                <div class="mb-16 grid gap-10 md:grid-cols-2 lg:grid-cols-4 lg:gap-12">

                    <div class="lg:col-span-1">
                        <div class="mb-5">
                            <h3 class="mb-1 text-2xl text-background">Nèg Mawon</h3>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gold">Cleaning Services LLC</p>
                        </div>
                        <p class="mb-6 text-sm leading-relaxed text-secondary/75">
                            A proud Haitian-American, family-run cleaning company serving Northeast Philadelphia with excellence and freedom in every clean.
                        </p>
                        <div class="flex items-center gap-3">
                            <a href="#" aria-label="Instagram" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-secondary transition-all duration-300 hover:-translate-y-1 hover:text-gold">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                            </a>
                            <a href="#" aria-label="Facebook" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-secondary transition-all duration-300 hover:-translate-y-1 hover:text-gold">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                            </a>
                            <a href="#" aria-label="Google Reviews" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-secondary transition-all duration-300 hover:-translate-y-1 hover:text-gold">
                                <flux:icon.star class="size-4" />
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gold">Services</h4>
                        <ul class="space-y-3">
                            <li><a href="#services" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:-translate-y-0.5 hover:text-gold">Standard Cleaning</a></li>
                            <li><a href="#services" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:-translate-y-0.5 hover:text-gold">Deep Cleaning</a></li>
                            <li><a href="#services" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:-translate-y-0.5 hover:text-gold">House Clearance</a></li>
                            <li><a href="#services" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:-translate-y-0.5 hover:text-gold">Professional Organizing</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gold">Company</h4>
                        <ul class="space-y-3">
                            <li><a href="#about" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:text-gold">About Us</a></li>
                            <li><a href="#process" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:text-gold">Our Process</a></li>
                            <li><a href="#testimonials" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:text-gold">Reviews</a></li>
                            <li><a href="#faq" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:text-gold">FAQ</a></li>
                            <li><a href="#contact" class="inline-block text-sm text-secondary/75 transition-colors duration-300 hover:text-gold">Get a Quote</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gold">Contact</h4>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <flux:icon.phone class="mt-0.5 size-4 shrink-0 text-gold" />
                                <a href="tel:+12676901707" class="text-sm text-secondary/85 transition-colors hover:text-gold">(267) 690-1707</a>
                            </li>
                            <li class="flex items-start gap-3">
                                <flux:icon.map-pin class="mt-0.5 size-4 shrink-0 text-gold" />
                                <span class="text-sm leading-relaxed text-secondary/85">7135 Rising Sun Ave<br>Philadelphia, PA 19111</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <flux:icon.clock class="mt-0.5 size-4 shrink-0 text-gold" />
                                <span class="text-sm leading-relaxed text-secondary/85">Mon – Sat: 9 AM – 5 PM</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 md:flex-row">
                    <p class="text-xs text-secondary/50">
                        &copy; {{ now()->year }} Nèg Mawon Cleaning Services LLC. All rights reserved.
                    </p>
                    <p class="flex items-center gap-2 text-xs text-secondary/50">
                        <span>Made with</span>
                        <flux:icon.heart variant="solid" class="size-3 text-gold" />
                        <span>in Northeast Philadelphia</span>
                    </p>
                </div>
            </div>
        </footer>

        <script>
            (function () {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.querySelectorAll('.animate-on-scroll').forEach((el) => el.classList.add('visible'));
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));
            })();
        </script>

        @fluxScripts
    </body>
</html>
