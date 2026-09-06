<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <x-calculator.province-select wire:model.live="province" :provinces="$provinces" />
        @error('province')
            <p class="text-sm text-deduct" role="alert">{{ $message }}</p>
        @enderror

        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold text-ink-soft">How do you want to enter the raise?</legend>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $inputMode === 'amount' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="inputMode" value="amount" class="sr-only">
                    New salary
                </label>
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $inputMode === 'percent' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="inputMode" value="percent" class="sr-only">
                    Raise %
                </label>
            </div>
        </fieldset>

        <div>
            <label for="current-salary" class="mb-2 block text-sm font-semibold">Current annual salary</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                <input id="current-salary" type="text" inputmode="decimal" wire:model.blur="currentSalary" class="input-field pl-8 text-xl" placeholder="75,000" autocomplete="off">
            </div>
            @error('currentSalary')
                <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
            @enderror
        </div>

        @if ($inputMode === 'percent')
            <div>
                <label for="raise-percent" class="mb-2 block text-sm font-semibold">Raise percentage</label>
                <div class="relative">
                    <input id="raise-percent" type="text" inputmode="decimal" wire:model.live="raisePercent" class="input-field pr-10 text-xl" placeholder="10" autocomplete="off">
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">%</span>
                </div>
                @error('raisePercent')
                    <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
                @enderror
                @if ($newSalary !== '')
                    <p class="mt-2 text-sm text-ink-soft">New salary: ${{ number_format((float) $newSalary, 2) }}</p>
                @endif
            </div>
        @else
            <div>
                <label for="new-salary" class="mb-2 block text-sm font-semibold">New annual salary</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                    <input id="new-salary" type="text" inputmode="decimal" wire:model="newSalary" class="input-field pl-8 text-xl" placeholder="85,000" autocomplete="off">
                </div>
                @error('newSalary')
                    <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate my raise</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">How much of the raise you keep</h2>
            <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['net_raise'] }}</p>
            <p class="mt-2 text-lg text-ink-soft">{{ $resultPayload['keep_percent'] }}% of the {{ $resultPayload['gross_raise'] }} gross raise remains after estimated payroll deductions.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <x-calculator.result-stat label="Gross annual raise" :value="$resultPayload['gross_raise']" />
                <x-calculator.result-stat label="Net annual raise" :value="$resultPayload['net_raise']" emphasis />
                <x-calculator.result-stat label="Extra monthly take-home" :value="$resultPayload['monthly']" />
                <x-calculator.result-stat label="Extra biweekly take-home" :value="$resultPayload['biweekly']" />
                <x-calculator.result-stat label="Extra weekly take-home" :value="$resultPayload['weekly']" />
                <x-calculator.result-stat label="Percentage of raise kept" :value="$resultPayload['keep_percent'].'%'" />
            </div>

            <h3 class="mt-10 font-serif text-2xl">Before and after</h3>
            <x-calculator.comparison-table :rows="$resultPayload['rows']" />

            <x-calculator.disclaimer />
        </section>
    @endif
</div>
