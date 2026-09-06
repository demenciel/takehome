<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold text-ink-soft">Component</legend>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $component === 'regular' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="component" value="regular" class="sr-only">
                    Regular Force
                </label>
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $component === 'reserve' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="component" value="reserve" class="sr-only">
                    Reserve Force
                </label>
            </div>
            <p class="text-sm text-ink-soft">
                @if ($component === 'regular')
                    Monthly rates for Regular Force and Reserve Class C.
                @else
                    Daily rates for Reserve Class A and Class B. Enter paid days below.
                @endif
            </p>
        </fieldset>

        <div>
            <label for="caf-rank" class="mb-2 block text-sm font-semibold">Rank</label>
            <select id="caf-rank" wire:model.live="rank" class="input-field">
                @foreach ($ranks as $option)
                    <option value="{{ $option['key'] }}">{{ $option['name'] }}</option>
                @endforeach
            </select>
        </div>

        @if ($showLevels)
            <div>
                <label for="caf-level" class="mb-2 block text-sm font-semibold">Pay level</label>
                <select id="caf-level" wire:model.live="payLevel" class="input-field">
                    @foreach ($levels as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="caf-increment" class="mb-2 block text-sm font-semibold">Pay increment</label>
            <select id="caf-increment" wire:model="increment" class="input-field">
                @foreach ($increments as $key => $rate)
                    <option value="{{ $key }}">{{ $key === 'basic' ? 'Basic' : 'Pay increment '.$key }} — ${{ $rate }}{{ $component === 'regular' ? '/month' : '/day' }}</option>
                @endforeach
            </select>
        </div>

        @if ($component === 'reserve')
            <div>
                <label for="reserve-days" class="mb-2 block text-sm font-semibold">Paid reserve days this year</label>
                <input id="reserve-days" type="text" inputmode="numeric" wire:model="reserveDays" class="input-field" placeholder="37">
                <p class="mt-2 text-sm text-ink-soft">Class A is often a few dozen days. Class B can be 100–365 paid days. Tax is estimated from daily rate × days.</p>
                @error('reserveDays')
                    <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <x-calculator.province-select wire:model.live="province" :provinces="$provinces" />
        @error('province')
            <p class="text-sm text-deduct" role="alert">{{ $message }}</p>
        @enderror

        <div>
            <label for="caf-frequency" class="mb-2 block text-sm font-semibold">Pay frequency (for take-home display)</label>
            <select id="caf-frequency" wire:model="frequency" class="input-field">
                @foreach ($frequencies as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-sm text-ink-soft">Regular Force pay is typically issued twice a month. Annual totals stay the same; only the per-period display changes.</p>
        </div>

        <button type="button" class="text-sm font-semibold text-accent underline" wire:click="$toggle('showAdvanced')" aria-expanded="{{ $showAdvanced ? 'true' : 'false' }}">
            Optional taxable income or pension override
        </button>

        @if ($showAdvanced)
            <div class="grid gap-4 rounded-xl bg-paper p-4 sm:grid-cols-2">
                <div>
                    <label for="caf-allowances" class="mb-2 block text-sm font-semibold">Annual taxable allowances</label>
                    <input id="caf-allowances" type="text" inputmode="decimal" wire:model="taxableAllowances" class="input-field" placeholder="0">
                    <p class="mt-2 text-sm text-ink-soft">Only amounts you enter are added. PLD, sea duty, and other benefits are not estimated.</p>
                </div>
                <div>
                    <label for="caf-pension" class="mb-2 block text-sm font-semibold">Annual pension contribution override</label>
                    <input id="caf-pension" type="text" inputmode="decimal" wire:model="pensionOverride" class="input-field" placeholder="Leave blank to estimate">
                </div>
            </div>
        @endif

        <p class="text-sm text-ink-soft">{{ $edition }} · Allowances and benefits are not included unless entered manually.</p>

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate CAF take-home</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Estimated take-home</h2>
            <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['net_period'] }}</p>
            <p class="mt-2 text-lg text-ink-soft">{{ $resultPayload['frequency'] }} · {{ $resultPayload['rank'] }}</p>

            <dl class="mt-8 space-y-3">
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Component</dt>
                    <dd class="font-semibold">{{ $resultPayload['component'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Rank</dt>
                    <dd class="font-semibold text-right">{{ $resultPayload['rank'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Pay increment</dt>
                    <dd class="font-semibold">{{ $resultPayload['increment'] }}@if ($resultPayload['level']) · {{ $resultPayload['level'] }}@endif</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Published {{ $resultPayload['unit'] }} rate</dt>
                    <dd class="font-semibold">{{ $resultPayload['rate'] }}</dd>
                </div>
                @if ($resultPayload['reserve_days'])
                    <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                        <dt class="text-ink-soft">Paid reserve days</dt>
                        <dd class="font-semibold">{{ $resultPayload['reserve_days'] }}</dd>
                    </div>
                @endif
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Base annual military pay</dt>
                    <dd class="font-semibold">{{ $resultPayload['base_annual'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Other entered taxable income</dt>
                    <dd class="font-semibold">{{ $resultPayload['allowances'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Gross monthly pay</dt>
                    <dd class="font-semibold">{{ $resultPayload['gross_monthly'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Gross biweekly equivalent</dt>
                    <dd class="font-semibold">{{ $resultPayload['gross_biweekly'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Estimated federal tax</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['federal_tax'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Estimated provincial tax</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['provincial_tax'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">{{ $resultPayload['pension_payroll'] }}</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['cpp'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">{{ $resultPayload['insurance_label'] }}</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['ei'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                    <dt class="text-ink-soft">Estimated CAF pension</dt>
                    <dd class="font-semibold text-deduct">-{{ $resultPayload['caf_pension'] }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-2">
                    <dt class="text-ink-soft">Estimated annual take-home</dt>
                    <dd class="font-semibold">{{ $resultPayload['net_annual'] }}</dd>
                </div>
            </dl>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <x-calculator.result-stat label="Monthly take-home" :value="$resultPayload['net_monthly']" emphasis />
                <x-calculator.result-stat label="Biweekly take-home" :value="$resultPayload['net_biweekly']" />
                <x-calculator.result-stat label="Annual take-home" :value="$resultPayload['net_annual']" />
            </div>

            <p class="mt-6 text-sm leading-6 text-ink-soft">{{ $resultPayload['caf_pension_note'] }}</p>
            <p class="mt-3 text-sm font-semibold leading-6 text-ink">Allowances and benefits are not included unless entered manually.</p>

            <x-calculator.disclaimer />
        </section>
    @endif
</div>
