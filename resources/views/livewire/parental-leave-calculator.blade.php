<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-5" novalidate>
        <x-calculator.province-select wire:model.live="province" :provinces="$provinces" />
        @error('province') <p class="text-sm text-deduct" role="alert">{{ $message }}</p> @enderror

        <div>
            <label for="parental-salary" class="mb-2 block text-sm font-semibold">Current annual gross salary</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-soft" aria-hidden="true">$</span>
                <input id="parental-salary" type="text" inputmode="decimal" wire:model="salary" class="input-field pl-8 text-xl" placeholder="80,000" autocomplete="off">
            </div>
            @error('salary') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="parental-frequency" class="mb-2 block text-sm font-semibold">Pay frequency</label>
            <select id="parental-frequency" wire:model="frequency" class="input-field">
                @foreach ($frequencies as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>

        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold text-ink-soft">Who is taking leave?</legend>
            <div class="grid gap-2 sm:grid-cols-3">
                @foreach (['birth_parent' => 'Birth parent', 'other_parent' => 'Other parent', 'both' => 'Both / household'] as $value => $label)
                    <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border px-3 text-sm font-semibold {{ $who === $value ? 'border-accent bg-accent/10 text-accent-dark' : 'border-line' }}">
                        <input type="radio" wire:model.live="who" value="{{ $value }}" class="sr-only">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div>
            <label for="leave-type" class="mb-2 block text-sm font-semibold">Leave type</label>
            <select id="leave-type" wire:model="leaveType" class="input-field">
                <option value="maternity_standard">Maternity + standard parental</option>
                <option value="maternity_extended">Maternity + extended parental</option>
                <option value="standard">Standard parental only</option>
                <option value="extended">Extended parental only</option>
            </select>
        </div>

        <div>
            <label for="planned-weeks" class="mb-2 block text-sm font-semibold">Planned number of weeks</label>
            <input id="planned-weeks" type="text" inputmode="numeric" wire:model="plannedWeeks" class="input-field" placeholder="50">
            <p class="mt-2 text-sm text-ink-soft">Salary does not decide eligibility. Service Canada still reviews insurable hours and your claim.</p>
            @error('plannedWeeks') <p class="mt-2 text-sm text-deduct" role="alert">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-start gap-3 text-sm leading-6">
            <input type="checkbox" wire:model.live="topUpEnabled" class="mt-1 h-5 w-5 shrink-0">
            <span>Employer top-up (optional)</span>
        </label>

        @if ($topUpEnabled)
            <div class="grid gap-4 rounded-xl bg-paper p-4 sm:grid-cols-2">
                <div>
                    <label for="top-up-percent" class="mb-2 block text-sm font-semibold">Top-up percent</label>
                    <input id="top-up-percent" type="text" inputmode="decimal" wire:model="topUpPercent" class="input-field" placeholder="80">
                </div>
                <div>
                    <label for="top-up-weeks" class="mb-2 block text-sm font-semibold">Top-up weeks</label>
                    <input id="top-up-weeks" type="text" inputmode="numeric" wire:model="topUpWeeks" class="input-field" placeholder="17">
                </div>
                <div class="sm:col-span-2">
                    <label for="top-up-mode" class="mb-2 block text-sm font-semibold">How the top-up works</label>
                    <select id="top-up-mode" wire:model="topUpMode" class="input-field">
                        <option value="to_percent">Top income up to this percent of regular salary (most common)</option>
                        <option value="add_percent">Add this percent of salary on top of EI</option>
                    </select>
                </div>
            </div>
        @endif

        <button type="button" class="text-sm font-semibold text-accent underline" wire:click="$toggle('showHousehold')">
            Household income and expenses (optional)
        </button>

        @if ($showHousehold || $who === 'both')
            <div class="grid gap-4 rounded-xl bg-paper p-4 sm:grid-cols-2">
                <div>
                    <label for="partner-income" class="mb-2 block text-sm font-semibold">Partner annual income</label>
                    <input id="partner-income" type="text" inputmode="decimal" wire:model="partnerIncome" class="input-field" placeholder="0">
                </div>
                <div>
                    <label for="partner-weeks" class="mb-2 block text-sm font-semibold">Partner parental weeks</label>
                    <input id="partner-weeks" type="text" inputmode="numeric" wire:model="partnerWeeks" class="input-field" placeholder="0">
                </div>
                <div>
                    <label for="monthly-expenses" class="mb-2 block text-sm font-semibold">Average monthly household expenses</label>
                    <input id="monthly-expenses" type="text" inputmode="decimal" wire:model="monthlyExpenses" class="input-field" placeholder="0">
                </div>
                <div>
                    <label for="monthly-savings" class="mb-2 block text-sm font-semibold">Current monthly savings target</label>
                    <input id="monthly-savings" type="text" inputmode="decimal" wire:model="monthlySavings" class="input-field" placeholder="0">
                </div>
            </div>
        @endif

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate my leave income</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            @if ($resultPayload['quebec'] ?? false)
                <h2 class="font-serif text-2xl">Québec uses QPIP, not federal EI</h2>
                <p class="mt-4 text-ink-soft">{{ $resultPayload['warnings'][0] }}</p>
                <p class="mt-4">
                    <a href="{{ $resultPayload['qpip_url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">Québec Parental Insurance Plan</a>
                </p>
                <h3 class="mt-8 text-lg font-semibold">Current take-home while working</h3>
                <p class="mt-2 text-ink-soft">Estimated monthly take-home: {{ $resultPayload['employment']['net_monthly'] }}.</p>
                <p class="mt-6">
                    <a href="{{ route('tools.ei_benefits') }}" wire:click="markEiClick" class="font-semibold text-accent-dark underline">How federal EI maternity and parental benefits work</a>
                </p>
                <p class="mt-3">
                    <button type="button" wire:click="continueToBabyBudget" class="font-semibold text-accent-dark underline">Estimate baby startup and first-year costs</button>
                </p>
            @else
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Your estimated weekly benefit</h2>
                <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['weekly_ei'] }}</p>
                <p class="mt-2 text-lg text-ink-soft">
                    About {{ $resultPayload['monthly_ei'] }} a month before tax, over {{ $resultPayload['leave_weeks'] }} counted weeks.
                    @if ($resultPayload['capped']) The 2026 weekly maximum was applied. @endif
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <x-calculator.result-stat label="Monthly income during leave" :value="$resultPayload['leave_monthly']" emphasis />
                    <x-calculator.result-stat label="Income change vs working" :value="$resultPayload['reduction_monthly'].' / '.$resultPayload['reduction_percent'].'%'" />
                </div>

                <div class="mt-8">
                    <p class="text-sm font-semibold text-ink-soft">Regular monthly take-home vs leave monthly income</p>
                    @php
                        $work = max(1, (int) preg_replace('/[^0-9]/', '', $resultPayload['working_monthly']));
                        $leave = max(1, (int) preg_replace('/[^0-9]/', '', $resultPayload['leave_monthly']));
                        $max = max($work, $leave);
                    @endphp
                    <div class="mt-3 space-y-3">
                        <div>
                            <p class="text-sm text-ink-soft">Working · {{ $resultPayload['working_monthly'] }}</p>
                            <div class="mt-1 h-3 rounded-full bg-paper">
                                <div class="h-3 rounded-full bg-accent" style="width: {{ min(100, (int) round(($work / $max) * 100)) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-ink-soft">On leave · {{ $resultPayload['leave_monthly'] }}</p>
                            <div class="mt-1 h-3 rounded-full bg-paper">
                                <div class="h-3 rounded-full bg-ink" style="width: {{ min(100, (int) round(($leave / $max) * 100)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-10 font-serif text-2xl">Current employment income</h3>
                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Gross weekly</dt><dd class="font-semibold">{{ $resultPayload['employment']['gross_weekly'] }}</dd></div>
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Gross biweekly</dt><dd class="font-semibold">{{ $resultPayload['employment']['gross_biweekly'] }}</dd></div>
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Gross monthly</dt><dd class="font-semibold">{{ $resultPayload['employment']['gross_monthly'] }}</dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink-soft">Estimated take-home ({{ $resultPayload['employment']['frequency'] }})</dt><dd class="font-semibold">{{ $resultPayload['employment']['net_period'] }}</dd></div>
                </dl>

                <h3 class="mt-10 font-serif text-2xl">Estimated EI benefit</h3>
                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Weekly EI</dt><dd class="font-semibold">{{ $resultPayload['weekly_ei'] }}</dd></div>
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Parental rate / 2026 maximum</dt><dd class="font-semibold">{{ $resultPayload['parental_rate'] }} · {{ $resultPayload['parental_max'] }}</dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink-soft">EI over the counted leave</dt><dd class="font-semibold">{{ $resultPayload['ei_total'] }}</dd></div>
                </dl>

                @if ($resultPayload['top_up_enabled'])
                    <h3 class="mt-10 font-serif text-2xl">Employer top-up</h3>
                    <dl class="mt-4 space-y-3">
                        <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Top-up per week</dt><dd class="font-semibold">{{ $resultPayload['top_up_weekly'] }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-line/70 py-2"><dt class="text-ink-soft">Top-up weeks</dt><dd class="font-semibold">{{ $resultPayload['top_up_weeks'] }}</dd></div>
                        <div class="flex justify-between gap-4 py-2"><dt class="text-ink-soft">Combined weekly EI + top-up</dt><dd class="font-semibold">{{ $resultPayload['leave_weekly'] }}</dd></div>
                    </dl>
                @endif

                <h3 class="mt-10 font-serif text-2xl">Leave comparison</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="data-table min-w-[28rem]">
                        <thead>
                            <tr>
                                <th scope="col">Path</th>
                                <th scope="col">Weekly EI</th>
                                <th scope="col">Monthly EI</th>
                                <th scope="col">Weeks</th>
                                <th scope="col">Estimated total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">Standard</th>
                                <td>{{ $resultPayload['standard_weekly'] }}</td>
                                <td>{{ $resultPayload['standard_monthly'] }}</td>
                                <td>{{ $resultPayload['standard_weeks'] }}</td>
                                <td>{{ $resultPayload['standard_total'] }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Extended</th>
                                <td>{{ $resultPayload['extended_weekly'] }}</td>
                                <td>{{ $resultPayload['extended_monthly'] }}</td>
                                <td>{{ $resultPayload['extended_weeks'] }}</td>
                                <td>{{ $resultPayload['extended_total'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-ink-soft">Standard pays more per week for a shorter period. Extended pays less per week for longer. Neither is universally better.</p>

                @if ($resultPayload['household'])
                    <h3 class="mt-10 font-serif text-2xl">Household impact</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-calculator.result-stat label="Household monthly income before leave" :value="$resultPayload['household']['before']" />
                        <x-calculator.result-stat label="Household monthly income during leave" :value="$resultPayload['household']['during']" />
                        <x-calculator.result-stat label="Household expenses" :value="$resultPayload['household']['expenses']" />
                        <x-calculator.result-stat label="Estimated household shortfall" :value="$resultPayload['household']['monthly_shortfall']" emphasis />
                    </div>
                    <p class="mt-4 text-ink-soft">Estimated amount your household may need to cover from savings over the leave: {{ $resultPayload['household']['buffer'] }}.</p>
                @endif

                <p class="mt-8">
                    <button type="button" wire:click="continueToBabyBudget" class="font-semibold text-accent-dark underline">Use this income change in the baby cost calculator</button>
                </p>
                <p class="mt-3">
                    <a href="{{ route('tools.ei_benefits') }}" wire:click="markEiClick" class="font-semibold text-accent-dark underline">See the EI maternity and parental benefit rules</a>
                </p>

                <x-planner-cta
                    headline="Planning the full budget for parental leave?"
                    copy="The calculator estimates your leave income. The Canadian Parental Leave Budget Planner helps you map your household income, expenses, leave period and savings month by month."
                    button="Plan My Parental Leave Budget"
                    :url="$resultPayload['planner_url']"
                />
            @endif

            <x-calculator.disclaimer text="Estimates only. Actual EI benefits depend on your insurable earnings, eligibility, Service Canada calculations and individual circumstances. Employer top-ups are plan-specific. This is not a Service Canada or Government of Canada service." />
        </section>
    @endif
</div>
