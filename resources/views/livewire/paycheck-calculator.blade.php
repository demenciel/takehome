<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold text-ink-soft">{{ __('calculator.input_mode') }}</legend>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $inputMode === 'annual' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="inputMode" value="annual" class="sr-only">
                    {{ __('calculator.mode_annual') }}
                </label>
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $inputMode === 'hourly' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="inputMode" value="hourly" class="sr-only">
                    {{ __('calculator.mode_hourly') }}
                </label>
            </div>
        </fieldset>

        @if ($inputMode === 'hourly')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="hourlyWage" class="mb-2 block text-sm font-semibold">{{ __('calculator.hourly_label') }}</label>
                    <input id="hourlyWage" type="text" inputmode="decimal" wire:model="hourlyWage" class="input-field" placeholder="25.00" autocomplete="off">
                    @error('hourlyWage') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="hoursPerWeek" class="mb-2 block text-sm font-semibold">{{ __('calculator.hours_label') }}</label>
                    <input id="hoursPerWeek" type="text" inputmode="decimal" wire:model="hoursPerWeek" class="input-field" placeholder="40">
                    @error('hoursPerWeek') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        @else
            <div>
                <label for="salary" class="mb-2 block text-lg font-semibold">{{ __('calculator.how_much') }}</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                    <input id="salary" type="text" inputmode="decimal" wire:model="salary" class="input-field pl-8 text-xl" placeholder="75,000" autocomplete="off" aria-describedby="salary-hint">
                </div>
                <p id="salary-hint" class="mt-2 text-sm text-ink-soft">{{ __('calculator.salary_label') }}</p>
                @error('salary') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label for="province" class="mb-2 block text-sm font-semibold">{{ __('calculator.province_label') }}</label>
            <select id="province" wire:model.live="province" class="input-field">
                <option value="">{{ __('calculator.province_placeholder') }}</option>
                @foreach ($provinces as $option)
                    <option value="{{ $option->value }}">{{ $option->name() }}</option>
                @endforeach
            </select>
            @error('province') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="frequency" class="mb-2 block text-sm font-semibold">{{ __('calculator.frequency_label') }}</label>
            <select id="frequency" wire:model.live="frequency" class="input-field">
                @foreach ($frequencies as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>

        <p class="text-sm text-ink-soft">{{ __('calculator.using_basic_claim') }} · {{ __('calculator.tax_year', ['year' => $taxYear]) }}</p>

        <button type="button" class="text-sm font-semibold text-accent underline" wire:click="$toggle('showAdvanced')" aria-expanded="{{ $showAdvanced ? 'true' : 'false' }}">
            {{ __('calculator.advanced') }}
        </button>

        @if ($showAdvanced)
            <div class="grid gap-4 rounded-xl bg-paper p-4 sm:grid-cols-2">
                <div>
                    <label for="rrsp" class="mb-2 block text-sm font-semibold">{{ __('calculator.rrsp_label') }}</label>
                    <input id="rrsp" type="text" inputmode="decimal" wire:model="rrsp" class="input-field" placeholder="0">
                </div>
                <div>
                    <label for="pension" class="mb-2 block text-sm font-semibold">{{ __('calculator.pension_label') }}</label>
                    <input id="pension" type="text" inputmode="decimal" wire:model="pension" class="input-field" placeholder="0">
                </div>
                <div>
                    <label for="unionDues" class="mb-2 block text-sm font-semibold">{{ __('calculator.union_label') }}</label>
                    <input id="unionDues" type="text" inputmode="decimal" wire:model="unionDues" class="input-field" placeholder="0">
                </div>
                <div>
                    <label for="otherDeductions" class="mb-2 block text-sm font-semibold">{{ __('calculator.other_label') }}</label>
                    <input id="otherDeductions" type="text" inputmode="decimal" wire:model="otherDeductions" class="input-field" placeholder="0">
                </div>
                <div class="sm:col-span-2">
                    <label for="additionalTax" class="mb-2 block text-sm font-semibold">{{ __('calculator.additional_tax_label') }}</label>
                    <input id="additionalTax" type="text" inputmode="decimal" wire:model="additionalTax" class="input-field" placeholder="0">
                </div>
            </div>
        @endif

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>{{ __('calculator.submit') }}</span>
            <span wire:loading>{{ __('calculator.submit') }}…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">{{ $resultPayload['summary'] }}</h2>
            <p class="mt-3 font-serif text-5xl tracking-tight text-ink">{{ $resultPayload['headline'] }}</p>
            <p class="mt-2 text-lg text-ink-soft">{{ $resultPayload['period'] }}</p>

            <dl class="mt-8 space-y-3">
                @foreach ($resultPayload['period_breakdown'] as $line)
                    <div class="flex items-baseline justify-between gap-4 border-b border-line/70 py-2">
                        <dt class="text-ink-soft">{{ $line['label'] }}</dt>
                        <dd class="font-semibold {{ $line['kind'] === 'deduction' ? 'text-deduct' : 'text-ink' }}">
                            {{ $line['kind'] === 'deduction' ? '-' : '' }}{{ $line['amount'] }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            <x-ad-slot name="result-inline" />

            <h3 class="mt-10 font-serif text-2xl">{{ __('calculator.annual_breakdown') }}</h3>
            <dl class="mt-4 space-y-3">
                @foreach ($resultPayload['annual_breakdown'] as $line)
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-soft">{{ $line['label'] }}</dt>
                        <dd class="font-semibold">{{ $line['amount'] }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-paper p-4">
                    <p class="text-sm text-ink-soft">{{ __('calculator.effective_tax_rate') }}</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $resultPayload['effective_tax_rate'] }}%</p>
                </div>
                <div class="rounded-xl bg-paper p-4">
                    <p class="text-sm text-ink-soft">{{ __('calculator.average_deductions') }}</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $resultPayload['average_deductions'] }}</p>
                </div>
            </div>

            <div
                class="mt-6 flex flex-col gap-3 sm:flex-row"
                x-data="{ copied: false, shareText: @js($resultPayload['share_text']), copyText: @js($resultPayload['copy_text']) }"
            >
                <button
                    type="button"
                    class="btn-secondary"
                    @click="
                        navigator.clipboard.writeText(copyText);
                        copied = true;
                        $wire.markShared();
                        setTimeout(() => copied = false, 2000)
                    "
                >
                    <span x-text="copied ? '{{ __('common.copied') }}' : '{{ __('common.copy') }}'"></span>
                </button>
                <button
                    type="button"
                    class="btn-secondary"
                    @click="
                        if (navigator.share) {
                            navigator.share({ text: shareText });
                        } else {
                            navigator.clipboard.writeText(shareText);
                        }
                        $wire.markShared();
                    "
                >
                    {{ __('common.share') }}
                </button>
            </div>

            <p class="mt-6 text-sm leading-6 text-ink-soft">{{ __('calculator.disclaimer') }}</p>
        </section>
    @endif
</div>
