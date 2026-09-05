<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">Privacy</h1>
        <p class="mt-6 text-lg text-ink-soft">You can use the calculator without creating an account or giving an email address.</p>
        <h2 class="mt-10 font-serif text-3xl">What we do not collect</h2>
        <p class="mt-4 text-ink-soft">We do not ask for a SIN, employer name, bank details, or tax documents. Ordinary calculations are not saved as a personal record of your salary.</p>
        <h2 class="mt-10 font-serif text-3xl">Analytics</h2>
        <p class="mt-4 text-ink-soft">If analytics are enabled, we may record that a calculation happened, along with province, pay frequency, and a salary range bucket. We do not send the exact salary to analytics.</p>
    </article>
</x-layouts.app>
