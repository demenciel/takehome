<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">Contact</h1>
        <p class="mt-6 text-lg text-ink-soft">Questions about the calculator, tax data, privacy, or a problem with the site. Do not send a SIN, bank details, or a full tax return.</p>
        <p class="mt-4 text-ink-soft">
            Email
            <a href="mailto:{{ $contactEmail }}" class="font-semibold text-accent-dark underline">{{ $contactEmail }}</a>
            or use the form.
        </p>

        @if (session('status'))
            <p class="mt-6 rounded-xl bg-accent/10 p-4 text-accent-dark" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="mt-8 space-y-5" novalidate>
            @csrf
            <div>
                <label for="name" class="mb-2 block text-sm font-semibold">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required class="input-field">
                @error('name') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="input-field">
                @error('email') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="message" class="mb-2 block text-sm font-semibold">Message</label>
                <textarea id="message" name="message" rows="6" required class="input-field min-h-40">{{ old('message') }}</textarea>
                @error('message') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary max-w-xs">Send message</button>
        </form>

        <p class="mt-8 text-sm text-ink-soft">
            How we handle messages is described in the
            <a href="{{ route('privacy') }}" class="font-semibold text-accent-dark underline">privacy policy</a>.
        </p>
    </article>
</x-layouts.app>
