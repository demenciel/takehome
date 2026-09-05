<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">Privacy policy</h1>
        <p class="mt-3 text-sm text-ink-soft">Last updated: {{ \Illuminate\Support\Carbon::parse($updated)->format('F j, Y') }}</p>
        <p class="mt-6 text-lg text-ink-soft">{{ config('app.name') }} is an independent Canadian paycheck calculator. You can use it without creating an account. This page describes what we actually collect and what third parties may collect when you visit.</p>

        <h2 class="mt-10 font-serif text-3xl">What we do not collect</h2>
        <p class="mt-4 text-ink-soft">We do not ask for a Social Insurance Number, employer name, bank details, tax slips, or login for the calculator. We do not store the salary you type as a personal financial record. We do not sell salary data.</p>

        <h2 class="mt-10 font-serif text-3xl">Calculator inputs</h2>
        <p class="mt-4 text-ink-soft">Salary, province, pay frequency, and optional deductions are used in the browser and sent to our server only to compute the estimate. They are not written to a user profile. First-party analytics, if enabled, may store a <em>salary range bucket</em> (for example “70–80k”), never the exact amount.</p>

        <h2 class="mt-10 font-serif text-3xl">First-party cookies and similar storage</h2>
        <p class="mt-4 text-ink-soft">The site uses a first-party session cookie so Laravel can keep the calculator working (CSRF protection and Livewire). That cookie is not used to identify you across other websites. The admin login, if you use it, also uses a session cookie.</p>

        <h2 class="mt-10 font-serif text-3xl">First-party analytics</h2>
        @if ($usesLocalAnalytics)
            <p class="mt-4 text-ink-soft">We store limited first-party events on our own database: that a page was viewed, that a calculation started or finished, province, pay frequency, a salary range bucket, and the page path. These events do not include your name, email, or exact salary.</p>
        @else
            <p class="mt-4 text-ink-soft">First-party event storage is currently off.</p>
        @endif

        <h2 class="mt-10 font-serif text-3xl">Google Analytics</h2>
        @if ($usesGoogleAnalytics)
            <p class="mt-4 text-ink-soft">We use Google Analytics 4 to understand how people find and use the site (pages viewed, approximate location, device and browser, referral source). Google sets cookies and similar technologies for this. We configure the calculator so that the salary you enter is not sent to Google Analytics as an event parameter.</p>
        @else
            <p class="mt-4 text-ink-soft">Google Analytics is not loaded unless a measurement ID is configured. When it is enabled, Google sets cookies and collects standard usage data. We do not send the salary you enter to Google Analytics.</p>
        @endif
        <p class="mt-4 text-ink-soft">Google’s own policy: <a href="https://policies.google.com/privacy" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">policies.google.com/privacy</a>. You can also use Google’s ads and analytics controls, or a browser that blocks third-party cookies.</p>

        <h2 class="mt-10 font-serif text-3xl">Advertising (Google AdSense)</h2>
        @if ($adsEnabled && $adsProvider === 'adsense')
            <p class="mt-4 text-ink-soft">This site currently displays advertisements through Google AdSense. AdSense uses cookies and advertising/measurement technologies to show ads, measure them, and in some cases personalize them. Google and its partners may collect device identifiers and browsing data for that purpose.</p>
        @else
            <p class="mt-4 text-ink-soft">Advertisements are not currently shown. If we enable ads, they will be served by Google AdSense. AdSense uses cookies and advertising/measurement technologies to show, measure, and sometimes personalize ads. That can include device identifiers and browsing data collected by Google and its partners.</p>
        @endif
        <p class="mt-4 text-ink-soft">When ads run, Google’s advertising policies apply: <a href="https://policies.google.com/technologies/ads" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">policies.google.com/technologies/ads</a>. You can opt out of personalized ads at <a href="https://adssettings.google.com" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">adssettings.google.com</a>.</p>

        <h2 class="mt-10 font-serif text-3xl">Contact messages</h2>
        <p class="mt-4 text-ink-soft">If you use the <a href="{{ route('contact') }}" class="font-semibold text-accent-dark underline">contact form</a>, we receive the name, email, and message you send so we can reply. Do not include a SIN or bank details. We do not use the contact form as a mailing list.</p>

        <h2 class="mt-10 font-serif text-3xl">Hosting</h2>
        <p class="mt-4 text-ink-soft">The site is hosted on infrastructure that processes standard server logs (IP address, user agent, requested URL, time) to operate and secure the service.</p>

        <h2 class="mt-10 font-serif text-3xl">Your choices</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            <li>Use the calculator without an account.</li>
            <li>Block or delete cookies in your browser. The calculator may still work; some features that need a session might not.</li>
            <li>Use Google’s ad settings and Analytics opt-out tools.</li>
            <li>Email us to ask what first-party data we hold about a contact message you sent.</li>
        </ul>

        <h2 class="mt-10 font-serif text-3xl">Contact</h2>
        <p class="mt-4 text-ink-soft">Questions about this policy: <a href="{{ route('contact') }}" class="font-semibold text-accent-dark underline">contact page</a> or <a href="mailto:{{ config('site.contact_email') }}" class="font-semibold text-accent-dark underline">{{ config('site.contact_email') }}</a>.</p>
    </article>
</x-layouts.app>
