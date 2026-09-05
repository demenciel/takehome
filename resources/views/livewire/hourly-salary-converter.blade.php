<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="convert" class="space-y-5" novalidate>
        @if ($mode === 'hourly_to_salary')
            <div>
                <label for="hourlyWage" class="mb-2 block text-lg font-semibold">Hourly wage</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                    <input id="hourlyWage" type="text" inputmode="decimal" wire:model="hourlyWage" class="input-field pl-8 text-xl" placeholder="38.46" autocomplete="off">
                </div>
                @error('hourlyWage') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
        @else
            <div>
                <label for="annualSalary" class="mb-2 block text-lg font-semibold">Annual salary</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                    <input id="annualSalary" type="text" inputmode="decimal" wire:model="annualSalary" class="input-field pl-8 text-xl" placeholder="80,000" autocomplete="off">
                </div>
                @error('annualSalary') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label for="hoursPerWeek" class="mb-2 block text-sm font-semibold">Hours per week</label>
            <input id="hoursPerWeek" type="text" inputmode="decimal" wire:model="hoursPerWeek" class="input-field" placeholder="40">
            <p class="mt-2 text-sm text-ink-soft">Weeks per year are fixed at {{ config('tax.weeks_per_year') }} on this converter.</p>
            @error('hoursPerWeek') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary">Convert</button>
    </form>

    @if ($result)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Conversion result</h2>
            <dl class="mt-4 space-y-3">
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-soft">Hourly</dt>
                    <dd class="font-semibold">{{ $result['hourly'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-soft">Annual</dt>
                    <dd class="font-semibold">{{ $result['annual'] }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-sm text-ink-soft">Assumption: {{ $result['assumption'] }}. This is a gross conversion, not take-home pay.</p>
        </section>
    @endif
</div>
