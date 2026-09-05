<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">Terms of use</h1>
        <p class="mt-3 text-sm text-ink-soft">Last updated: {{ \Illuminate\Support\Carbon::parse($updated)->format('F j, Y') }}</p>
        <p class="mt-6 text-lg text-ink-soft">These terms apply when you use {{ config('app.name') }}. If you do not agree, do not use the site.</p>

        <h2 class="mt-10 font-serif text-3xl">The service</h2>
        <p class="mt-4 text-ink-soft">{{ config('app.name') }} provides estimated Canadian take-home pay and related calculators. It is an independent utility, not a government service, payroll product, tax-filing service, or professional advisor.</p>

        <h2 class="mt-10 font-serif text-3xl">Estimates only</h2>
        <p class="mt-4 text-ink-soft">Results are estimates based on published payroll rules and the information you enter. They may differ from an employer paycheque or a filed return. They are not tax, legal, or financial advice. See the <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">methodology</a> page.</p>

        <h2 class="mt-10 font-serif text-3xl">Acceptable use</h2>
        <p class="mt-4 text-ink-soft">Do not abuse the site: no scraping that degrades the service, no attempts to break security, and no use that is unlawful. The calculator is for personal, informational use.</p>

        <h2 class="mt-10 font-serif text-3xl">Accounts</h2>
        <p class="mt-4 text-ink-soft">The public calculators do not require an account. Any admin login is only for operating the site. You are responsible for keeping those credentials confidential.</p>

        <h2 class="mt-10 font-serif text-3xl">Privacy</h2>
        <p class="mt-4 text-ink-soft">Use of the site is also subject to the <a href="{{ route('privacy') }}" class="font-semibold text-accent-dark underline">privacy policy</a>, including cookies, Google Analytics, and Google AdSense when those are active.</p>

        <h2 class="mt-10 font-serif text-3xl">Intellectual property</h2>
        <p class="mt-4 text-ink-soft">Site design, copy, and software are owned by {{ config('site.legal_name') }} or its licensors. Tax parameters themselves come from public government sources cited on the methodology page. You may link to public pages. You may not copy the site wholesale or present it as an official CRA or Revenu Québec tool.</p>

        <h2 class="mt-10 font-serif text-3xl">No warranty</h2>
        <p class="mt-4 text-ink-soft">The site is provided “as is.” We do not warrant that estimates are complete, current for your situation, or uninterrupted. Tax rules change. You remain responsible for your own tax and payroll decisions.</p>

        <h2 class="mt-10 font-serif text-3xl">Limitation of liability</h2>
        <p class="mt-4 text-ink-soft">To the extent permitted by law, {{ config('site.legal_name') }} is not liable for loss arising from use of, or reliance on, the estimates or the site, including decisions about a job offer or tax filing.</p>

        <h2 class="mt-10 font-serif text-3xl">Changes</h2>
        <p class="mt-4 text-ink-soft">We may update these terms. The date at the top will change. Continued use after an update means you accept the revised terms.</p>

        <h2 class="mt-10 font-serif text-3xl">Contact</h2>
        <p class="mt-4 text-ink-soft"><a href="{{ route('contact') }}" class="font-semibold text-accent-dark underline">Contact us</a> or email <a href="mailto:{{ config('site.contact_email') }}" class="font-semibold text-accent-dark underline">{{ config('site.contact_email') }}</a>.</p>
    </article>
</x-layouts.app>
