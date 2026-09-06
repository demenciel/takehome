<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <x-seo.meta :seo="$seo ?? null" />
    @if (filled(config('analytics.measurement_id')))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('analytics.measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', @json(config('analytics.measurement_id')));
        </script>
    @endif
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9601080087531926"
        crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen antialiased">
    <a href="#content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2">
        {{ __('common.skip_to_content') }}
    </a>

    <header class="border-b border-line bg-card/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="font-serif text-xl tracking-tight text-ink">
                {{ config('app.name') }}
            </a>
            <nav aria-label="Primary" class="flex items-center gap-4 text-sm font-medium text-ink-soft">
                <details class="relative">
                    <summary class="cursor-pointer list-none hover:text-ink">{{ __('common.nav_calculators') }}</summary>
                    <ul class="absolute right-0 z-20 mt-2 w-64 rounded-xl border border-line bg-card p-2 shadow-sm">
                        <li><a class="flex min-h-12 items-center rounded-lg px-3 hover:bg-paper" href="{{ route('paycheck.canada') }}">Paycheque calculator</a></li>
                        <li><a class="flex min-h-12 items-center rounded-lg px-3 hover:bg-paper" href="{{ route('tools.overtime') }}">Overtime pay calculator</a></li>
                        <li><a class="flex min-h-12 items-center rounded-lg px-3 hover:bg-paper" href="{{ route('tools.bonus') }}">Bonus tax calculator</a></li>
                        <li><a class="flex min-h-12 items-center rounded-lg px-3 hover:bg-paper" href="{{ route('tools.raise') }}">Raise calculator</a></li>
                        <li><a class="flex min-h-12 items-center rounded-lg px-3 hover:bg-paper" href="{{ route('tools.military') }}">CAF salary calculator</a></li>
                        <li><a class="flex min-h-12 items-center rounded-lg px-3 hover:bg-paper" href="{{ route('tools.hourly_to_salary') }}">Hourly ↔ salary</a></li>
                    </ul>
                </details>
                <a href="{{ route('home') }}#provinces"
                    class="hidden hover:text-ink sm:inline">{{ __('common.nav_provinces') }}</a>
                <a href="{{ route('methodology') }}"
                    class="hidden hover:text-ink sm:inline">{{ __('common.nav_how_it_works') }}</a>
                <a href="{{ route('about') }}" class="hover:text-ink">{{ __('common.footer_about') }}</a>
            </nav>
        </div>
    </header>

    <main id="content">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-line bg-card">
        <div
            class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10 text-sm text-ink-soft sm:px-6 md:flex-row md:justify-between">
            <div>
                <p class="font-serif text-base text-ink">{{ config('app.name') }}</p>
                <p class="mt-2 max-w-md">{{ __('common.brand_tagline') }}</p>
                <p class="mt-3">{{ __('common.footer_disclaimer') }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <a href="{{ route('paycheck.canada') }}" class="hover:text-ink">{{ __('common.nav_calculator') }}</a>
                <a href="{{ route('tools.overtime') }}" class="hover:text-ink">Overtime pay calculator</a>
                <a href="{{ route('tools.bonus') }}" class="hover:text-ink">Bonus tax calculator</a>
                <a href="{{ route('tools.raise') }}" class="hover:text-ink">Raise calculator</a>
                <a href="{{ route('tools.military') }}" class="hover:text-ink">CAF salary calculator</a>
                <a href="{{ route('tools.hourly_to_salary') }}" class="hover:text-ink">Hourly to salary calculator</a>
                <a href="{{ route('methodology') }}" class="hover:text-ink">{{ __('common.footer_methodology') }}</a>
                <a href="{{ route('tax-rates') }}" class="hover:text-ink">{{ __('common.footer_tax_rates') }}</a>
                <a href="{{ route('about') }}" class="hover:text-ink">{{ __('common.footer_about') }}</a>
                <a href="{{ route('privacy') }}" class="hover:text-ink">{{ __('common.footer_privacy') }}</a>
                <a href="{{ route('terms') }}" class="hover:text-ink">{{ __('common.footer_terms') }}</a>
                <a href="{{ route('contact') }}" class="hover:text-ink">{{ __('common.footer_contact') }}</a>
                <a href="{{ route('sitemap') }}" class="hover:text-ink">Sitemap</a>
            </div>
        </div>
    </footer>

    @isset($seo)
        <x-seo.json-ld :seo="$seo" />
    @endisset

    @livewireScripts
</body>

</html>
