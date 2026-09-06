<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <x-calculator.province-select wire:model.live="province" :provinces="$provinces" />
        @error('province')
            <p class="text-sm text-deduct" role="alert">{{ $message }}</p>
        @enderror

        @if ($rules)
            <p class="rounded-xl bg-paper p-4 text-sm leading-6 text-ink-soft">
                Standard rule here: overtime after
                <strong class="text-ink">{{ $rules['weekly_threshold'] }} hours/week</strong>
                @if ($rules['daily_threshold'])
                    or <strong class="text-ink">{{ $rules['daily_threshold'] }} hours/day</strong>
                @endif
                at {{ $rules['multiplier'] }}×
                @if ($rules['daily_double_threshold'])
                    ({{ $rules['double_multiplier'] }}× after {{ $rules['daily_double_threshold'] }} hours in a day)
                @endif
                .
            </p>
        @endif

        <div>
            <label for="hourlyWage" class="mb-2 block text-sm font-semibold">Hourly wage</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                <input id="hourlyWage" type="text" inputmode="decimal" wire:model="hourlyWage" class="input-field pl-8 text-xl" placeholder="30.00" autocomplete="off">
            </div>
            @error('hourlyWage')
                <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="regularHours" class="mb-2 block text-sm font-semibold">Regular hours</label>
                <input id="regularHours" type="text" inputmode="decimal" wire:model="regularHours" class="input-field" placeholder="40" autocomplete="off">
                @error('regularHours')
                    <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="overtimeHours" class="mb-2 block text-sm font-semibold">Overtime hours</label>
                <input id="overtimeHours" type="text" inputmode="decimal" wire:model="overtimeHours" class="input-field" placeholder="8" autocomplete="off">
                @error('overtimeHours')
                    <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($showsDoubleTime)
            <div>
                <label for="doubleHours" class="mb-2 block text-sm font-semibold">Double-time hours (optional)</label>
                <input id="doubleHours" type="text" inputmode="decimal" wire:model="doubleHours" class="input-field" placeholder="0" autocomplete="off">
                <p class="mt-2 text-sm text-ink-soft">British Columbia pays 2× after 12 hours in a day. Enter only those extra hours.</p>
                @error('doubleHours')
                    <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label for="ot-frequency" class="mb-2 block text-sm font-semibold">Pay frequency</label>
            <select id="ot-frequency" wire:model.live="frequency" class="input-field">
                @foreach ($frequencies as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-sm text-ink-soft">Hours are for one pay period. Tax is estimated by repeating that period for a full year.</p>
        </div>

        @if ($showsContractPremium)
            <label class="flex items-start gap-3 text-sm leading-6">
                <input type="checkbox" wire:model="useContractPremium" class="mt-1 h-5 w-5 shrink-0">
                <span>Pay overtime at 1.5× my regular wage. New Brunswick’s statutory floor is 1.5× minimum wage; many contracts pay a higher premium.</span>
            </label>
        @endif

        <button type="button" class="text-sm font-semibold text-accent underline" wire:click="applyWeeklyThreshold">
            Split these hours using the weekly overtime threshold
        </button>

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate overtime pay</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">This {{ strtolower($resultPayload['frequency_label']) }} period</h2>
            <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['after_tax_overtime'] }}</p>
            <p class="mt-2 text-lg text-ink-soft">Estimated after-tax value of your overtime — about {{ $resultPayload['keep_percent'] }}% of the overtime premium.</p>

            <dl class="mt-8 space-y-3">
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Regular pay</dt>
                    <dd class="font-semibold">{{ $resultPayload['regular_pay'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Overtime pay</dt>
                    <dd class="font-semibold">{{ $resultPayload['overtime_pay'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Total gross pay</dt>
                    <dd class="font-semibold">{{ $resultPayload['total_gross'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Estimated deductions</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['period_deductions'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-2">
                    <dt class="text-ink-soft">Estimated take-home</dt>
                    <dd class="font-semibold">{{ $resultPayload['period_net'] }}</dd>
                </div>
            </dl>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <x-calculator.result-stat label="Overtime rate" :value="$resultPayload['overtime_rate'].'/hour'" />
                <x-calculator.result-stat label="Overtime hours" :value="$resultPayload['overtime_hours']" />
                <x-calculator.result-stat label="Gross overtime earned" :value="$resultPayload['overtime_pay']" />
            </div>

            <p class="mt-6 text-sm leading-6 text-ink-soft">
                After-tax overtime is the difference between two full-year payroll estimates:
                {{ $resultPayload['annual_with'] }} with this overtime repeating every period
                ({{ $resultPayload['periods'] }} periods) versus {{ $resultPayload['annual_without'] }} without it.
                It is not overtime pay multiplied by an assumed tax rate.
            </p>

            <x-calculator.disclaimer />
        </section>
    @endif
</div>
