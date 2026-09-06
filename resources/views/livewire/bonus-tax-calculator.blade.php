<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <x-calculator.province-select wire:model.live="province" :provinces="$provinces" />
        @error('province')
            <p class="text-sm text-deduct" role="alert">{{ $message }}</p>
        @enderror

        <div>
            <label for="bonus-salary" class="mb-2 block text-sm font-semibold">Annual salary</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                <input id="bonus-salary" type="text" inputmode="decimal" wire:model="salary" class="input-field pl-8 text-xl" placeholder="80,000" autocomplete="off">
            </div>
            @error('salary')
                <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="bonus-amount" class="mb-2 block text-sm font-semibold">Bonus amount</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                <input id="bonus-amount" type="text" inputmode="decimal" wire:model="bonus" class="input-field pl-8 text-xl" placeholder="10,000" autocomplete="off">
            </div>
            @error('bonus')
                <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="button" class="text-sm font-semibold text-accent underline" wire:click="$toggle('showAdvanced')" aria-expanded="{{ $showAdvanced ? 'true' : 'false' }}">
            Optional RRSP deduction
        </button>

        @if ($showAdvanced)
            <div>
                <label for="bonus-rrsp" class="mb-2 block text-sm font-semibold">Annual RRSP contribution</label>
                <input id="bonus-rrsp" type="text" inputmode="decimal" wire:model="rrsp" class="input-field" placeholder="0">
                <p class="mt-2 text-sm text-ink-soft">Applied to both the salary-only and salary-plus-bonus estimates.</p>
            </div>
        @endif

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate bonus tax</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Estimated net bonus</h2>
            <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['net_bonus'] }}</p>
            <p class="mt-2 text-lg text-ink-soft">You keep {{ $resultPayload['keep_percent'] }}% of the bonus after estimated tax and payroll contributions.</p>

            <dl class="mt-8 space-y-3">
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Gross bonus</dt>
                    <dd class="font-semibold">{{ $resultPayload['gross_bonus'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Estimated tax increase</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['tax_increase'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">{{ $resultPayload['pension_label'] }} impact</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['pension_impact'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">{{ $resultPayload['insurance_label'] }} impact</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['insurance_impact'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-2">
                    <dt class="text-ink-soft">Estimated net bonus</dt>
                    <dd class="font-semibold">{{ $resultPayload['net_bonus'] }}</dd>
                </div>
            </dl>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <x-calculator.result-stat label="Income before bonus" :value="$resultPayload['income_before']" />
                <x-calculator.result-stat label="Income after bonus" :value="$resultPayload['income_after']" />
                <x-calculator.result-stat label="Incremental deduction rate" :value="$resultPayload['deduction_rate'].'%'" emphasis />
            </div>

            <p class="mt-6 text-sm leading-6 text-ink-soft">
                Net bonus is take-home on salary plus bonus minus take-home on salary alone, using the same payroll engine as the paycheck calculator. CPP/QPP and EI/QPIP stop once annual ceilings are reached. Your employer may withhold a different amount on the bonus cheque than this annual estimate.
            </p>

            <x-calculator.disclaimer />
        </section>
    @endif
</div>
