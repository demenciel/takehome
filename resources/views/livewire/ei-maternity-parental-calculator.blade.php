<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <x-calculator.province-select wire:model.live="province" :provinces="$provinces" />
        @error('province') <p class="text-sm text-deduct" role="alert">{{ $message }}</p> @enderror

        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold text-ink-soft">How do you want to enter earnings?</legend>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $inputMode === 'annual' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="inputMode" value="annual" class="sr-only">
                    Annual salary
                </label>
                <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $inputMode === 'weekly' ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                    <input type="radio" wire:model.live="inputMode" value="weekly" class="sr-only">
                    Average weekly insurable
                </label>
            </div>
        </fieldset>

        <div>
            <label for="ei-amount" class="mb-2 block text-sm font-semibold">{{ $inputMode === 'weekly' ? 'Average insurable weekly earnings' : 'Annual salary' }}</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                <input id="ei-amount" type="text" inputmode="decimal" wire:model="amount" class="input-field pl-8 text-xl" autocomplete="off">
            </div>
            @error('amount') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="ei-program" class="mb-2 block text-sm font-semibold">Benefit type</label>
            <select id="ei-program" wire:model.live="program" class="input-field">
                <option value="maternity">Maternity</option>
                <option value="standard_parental">Standard parental</option>
                <option value="extended_parental">Extended parental</option>
            </select>
        </div>

        @if ($program !== 'maternity')
            <label class="flex items-start gap-3 text-sm leading-6">
                <input type="checkbox" wire:model.live="sharing" class="mt-1 h-5 w-5 shrink-0">
                <span>Sharing parental weeks with another parent</span>
            </label>
        @endif

        @if ($sharing && $program !== 'maternity')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="parent-a" class="mb-2 block text-sm font-semibold">Weeks parent A</label>
                    <input id="parent-a" type="text" inputmode="numeric" wire:model="parentA" class="input-field">
                </div>
                <div>
                    <label for="parent-b" class="mb-2 block text-sm font-semibold">Weeks parent B</label>
                    <input id="parent-b" type="text" inputmode="numeric" wire:model="parentB" class="input-field">
                </div>
            </div>
        @else
            <div>
                <label for="ei-weeks" class="mb-2 block text-sm font-semibold">Number of weeks</label>
                <input id="ei-weeks" type="text" inputmode="numeric" wire:model="weeks" class="input-field">
            </div>
        @endif

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate EI benefits</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            @if ($resultPayload['quebec'] ?? false)
                <h2 class="font-serif text-2xl">Québec benefits are paid through QPIP</h2>
                <p class="mt-4 text-ink-soft">Federal EI maternity and parental benefits do not apply to most Québec births and adoptions. Use the official Québec Parental Insurance Plan.</p>
                <p class="mt-4"><a href="{{ $resultPayload['qpip_url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">QPIP / RQAP</a></p>
                <p class="mt-6">
                    <a href="{{ route('tools.parental') }}" wire:click="markParentalClick" class="font-semibold text-accent-dark underline">See household leave planning for other provinces</a>
                </p>
                <p class="mt-3">
                    <a href="{{ route('tools.baby') }}" wire:click="markBabyClick" class="font-semibold text-accent-dark underline">Estimate baby startup and first-year costs</a>
                </p>
            @else
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Estimated weekly benefit</h2>
                <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['weekly_benefit'] }}</p>
                <p class="mt-2 text-lg text-ink-soft">{{ $resultPayload['rate_percent'] }} of insurable weekly earnings, up to {{ $resultPayload['max_weekly'] }} in {{ $year }}.</p>

                <dl class="mt-8 space-y-3">
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Benefit rate</dt><dd class="font-semibold">{{ $resultPayload['rate_percent'] }}</dd></div>
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Applicable maximum</dt><dd class="font-semibold">{{ $resultPayload['max_weekly'] }}</dd></div>
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Selected weeks</dt><dd class="font-semibold">{{ $resultPayload['weeks'] }} / {{ $resultPayload['max_weeks'] }} usual maximum</dd></div>
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Approximate monthly equivalent</dt><dd class="font-semibold">{{ $resultPayload['monthly_equivalent'] }}</dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink-soft">Estimated total benefit</dt><dd class="font-semibold">{{ $resultPayload['total'] }}</dd></div>
                </dl>

                @foreach ($resultPayload['warnings'] as $warning)
                    <p class="mt-4 text-sm text-ink-soft">{{ $warning }}</p>
                @endforeach

                <p class="mt-8">
                    <a href="{{ route('tools.parental') }}" wire:click="markParentalClick" class="font-semibold text-accent-dark underline">Want to see what this means for your actual household budget?</a>
                </p>
                <p class="mt-3">
                    <a href="{{ route('tools.baby') }}" wire:click="markBabyClick" class="font-semibold text-accent-dark underline">Estimate baby startup and first-year costs</a>
                </p>

                <x-planner-cta
                    headline="Turn your EI estimate into a month-by-month leave plan"
                    copy="The calculator estimates the weekly benefit. The Canadian Parental Leave Budget Planner helps you lay out income, expenses and savings across the leave."
                    button="View the Canadian Parental Leave Budget Planner"
                    :url="$resultPayload['planner_url']"
                />
            @endif

            <x-calculator.disclaimer text="Estimates only. Actual EI benefits depend on your insurable earnings, eligibility, Service Canada calculations and individual circumstances. Government benefit amounts and eligibility can change." />
        </section>
    @endif
</div>
