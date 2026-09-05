<x-layouts.app>
    <section class="mx-auto max-w-md px-4 py-16">
        <h1 class="font-serif text-3xl">{{ __('common.log_in') }}</h1>
        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold">{{ __('common.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="input-field">
                @error('email') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold">{{ __('common.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input-field">
            </div>
            <button type="submit" class="btn-primary">{{ __('common.log_in') }}</button>
        </form>
    </section>
</x-layouts.app>
