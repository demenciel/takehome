<div class="rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-8">
    <form wire:submit="calculate" class="space-y-8" novalidate>
        <section>
            <h2 class="font-serif text-2xl">Baby preparation / startup costs</h2>
            <p class="mt-2 text-sm text-ink-soft">Edit any amount. Defaults are planning estimates, not a claim that a baby costs a fixed total.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="data-table min-w-[40rem]">
                    <thead>
                        <tr>
                            <th scope="col">Item</th>
                            <th scope="col">Expected</th>
                            <th scope="col">Bought</th>
                            <th scope="col">Actual</th>
                            <th scope="col">Gift</th>
                            <th scope="col">Used</th>
                            <th scope="col">Used cost</th>
                            <th scope="col">Skip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastCategory = null; @endphp
                        @foreach ($startup as $index => $item)
                            @if ($item['category'] !== $lastCategory)
                                <tr>
                                    <th colspan="8" scope="colgroup" class="bg-paper text-left">{{ $item['category'] }}</th>
                                </tr>
                                @php $lastCategory = $item['category']; @endphp
                            @endif
                            <tr>
                                <th scope="row">{{ $item['label'] }}</th>
                                <td><input type="text" inputmode="decimal" wire:model="startup.{{ $index }}.planned" class="input-field" aria-label="{{ $item['label'] }} expected cost"></td>
                                <td><input type="checkbox" wire:model="startup.{{ $index }}.purchased" class="h-5 w-5" aria-label="{{ $item['label'] }} already purchased"></td>
                                <td><input type="text" inputmode="decimal" wire:model="startup.{{ $index }}.actual" class="input-field" aria-label="{{ $item['label'] }} actual cost"></td>
                                <td><input type="checkbox" wire:model="startup.{{ $index }}.gift" class="h-5 w-5" aria-label="{{ $item['label'] }} gift"></td>
                                <td><input type="checkbox" wire:model="startup.{{ $index }}.used" class="h-5 w-5" aria-label="{{ $item['label'] }} bought used"></td>
                                <td><input type="text" inputmode="decimal" wire:model="startup.{{ $index }}.used_cost" class="input-field" aria-label="{{ $item['label'] }} used cost"></td>
                                <td><input type="checkbox" wire:model="startup.{{ $index }}.skip" class="h-5 w-5" aria-label="Skip {{ $item['label'] }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="font-serif text-2xl">Monthly recurring baby costs</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($recurring as $index => $item)
                    <div>
                        <label class="mb-2 block text-sm font-semibold" for="recurring-{{ $item['id'] }}">{{ $item['label'] }}</label>
                        <input id="recurring-{{ $item['id'] }}" type="text" inputmode="decimal" wire:model="recurring.{{ $index }}.monthly" class="input-field">
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="font-serif text-2xl">Childcare</h2>
            <label class="mt-4 flex items-start gap-3 text-sm leading-6">
                <input type="checkbox" wire:model.live="childcareNeeded" class="mt-1 h-5 w-5 shrink-0">
                <span>Childcare will be needed in the first year</span>
            </label>
            @if ($childcareNeeded)
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="care-start" class="mb-2 block text-sm font-semibold">Expected start month (1–12)</label>
                        <input id="care-start" type="text" inputmode="numeric" wire:model="childcareStartMonth" class="input-field">
                    </div>
                    <div>
                        <label for="care-cost" class="mb-2 block text-sm font-semibold">Monthly childcare cost</label>
                        <input id="care-cost" type="text" inputmode="decimal" wire:model="childcareMonthly" class="input-field">
                    </div>
                    <div>
                        <label for="care-subsidy" class="mb-2 block text-sm font-semibold">Monthly subsidy / benefit</label>
                        <input id="care-subsidy" type="text" inputmode="decimal" wire:model="childcareSubsidy" class="input-field">
                    </div>
                </div>
                <p class="mt-2 text-sm text-ink-soft">Do not assume a Canada-wide $10/day rate. Enter the amount you actually expect.</p>
            @endif
        </section>

        <section>
            <h2 class="font-serif text-2xl">Government benefits (optional)</h2>
            <div class="mt-4">
                <label for="ccb-monthly" class="mb-2 block text-sm font-semibold">Estimated monthly Canada Child Benefit</label>
                <input id="ccb-monthly" type="text" inputmode="decimal" wire:model="ccbMonthly" class="input-field">
                <p class="mt-2 text-sm text-ink-soft">
                    {{ $ccb['note'] }} For {{ $ccb['period_label'] }}, the published maximum for a child under 6 is ${{ number_format((float) $ccb['under_6_annual_maximum']) }} a year (${{ $ccb['under_6_monthly_maximum'] }}/month). Most families receive less.
                    <a href="{{ config('external-links.government.ccb_calculator') }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">CRA Child and Family Benefits Calculator</a>
                </p>
            </div>
        </section>

        <section>
            <h2 class="font-serif text-2xl">Family readiness</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="current-savings" class="mb-2 block text-sm font-semibold">Savings already set aside</label>
                    <input id="current-savings" type="text" inputmode="decimal" wire:model="currentSavings" class="input-field">
                </div>
                <div>
                    <label for="monthly-saved" class="mb-2 block text-sm font-semibold">Amount being saved each month</label>
                    <input id="monthly-saved" type="text" inputmode="decimal" wire:model="monthlySaved" class="input-field">
                </div>
                <div>
                    <label for="months-until" class="mb-2 block text-sm font-semibold">Months until arrival</label>
                    <input id="months-until" type="text" inputmode="numeric" wire:model="monthsUntil" class="input-field">
                </div>
                <div>
                    <label for="leave-reduction" class="mb-2 block text-sm font-semibold">Optional monthly leave income reduction</label>
                    <input id="leave-reduction" type="text" inputmode="decimal" wire:model="leaveReduction" class="input-field">
                    <p class="mt-2 text-sm text-ink-soft">
                        <a href="{{ route('tools.parental') }}" wire:click="markParentalClick" class="font-semibold text-accent-dark underline">Estimate this with the parental leave calculator</a>
                    </p>
                </div>
                <div>
                    <label for="leave-months" class="mb-2 block text-sm font-semibold">Months of leave income reduction</label>
                    <input id="leave-months" type="text" inputmode="numeric" wire:model="leaveMonths" class="input-field">
                </div>
            </div>
        </section>

        <button type="submit" class="btn-primary">
            <span wire:loading.remove>Estimate my baby budget</span>
            <span wire:loading>Estimating…</span>
        </button>
    </form>

    @if ($errorMessage)
        <p class="mt-6 rounded-xl bg-deduct/10 p-4 text-deduct" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($resultPayload)
        <section class="mt-8 border-t border-line pt-8" aria-live="polite">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Estimated amount left to prepare</h2>
            <p class="mt-3 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{{ $resultPayload['amount_left'] }}</p>
            <p class="mt-2 text-lg text-ink-soft">First-year baby cost estimate: {{ $resultPayload['first_year_cost'] }}.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <x-calculator.result-stat label="Baby setup budget" :value="$resultPayload['planned_startup']" />
                <x-calculator.result-stat label="Already spent" :value="$resultPayload['already_spent']" />
                <x-calculator.result-stat label="Remaining purchases" :value="$resultPayload['remaining_purchases']" emphasis />
                <x-calculator.result-stat label="Gift savings" :value="$resultPayload['gift_savings']" />
                <x-calculator.result-stat label="Second-hand savings" :value="$resultPayload['used_savings']" />
                <x-calculator.result-stat label="Estimated monthly baby expenses" :value="$resultPayload['monthly_recurring']" />
                <x-calculator.result-stat label="First-year recurring total" :value="$resultPayload['recurring_first_year']" />
                <x-calculator.result-stat label="Estimated childcare cost" :value="$resultPayload['childcare_first_year']" />
                <x-calculator.result-stat label="Savings currently available" :value="$resultPayload['current_savings']" />
                <x-calculator.result-stat label="Projected savings by arrival" :value="$resultPayload['projected_savings']" />
            </div>

            <p class="mt-8">
                <a href="{{ route('tools.parental') }}" wire:click="markParentalClick" class="font-semibold text-accent-dark underline">Expecting your income to change during leave?</a>
            </p>
            <p class="mt-3">
                <a href="{{ route('tools.ei_benefits') }}" wire:click="markEiClick" class="font-semibold text-accent-dark underline">Not sure how much EI you could receive?</a>
            </p>
            <p class="mt-3">
                <a href="{{ route('paycheck.canada') }}" class="font-semibold text-accent-dark underline">Want to see your normal take-home pay before comparing leave?</a>
            </p>

            <x-planner-cta
                headline="Want the full baby budget and purchase tracker?"
                copy="The Canadian New Baby Budget Planner helps you track purchases, gifts, used items, recurring costs, childcare and savings in one place."
                button="View the New Baby Budget Planner"
                :url="$resultPayload['planner_url']"
            />

            <x-calculator.disclaimer :text="$disclaimer.' Government benefit amounts and eligibility can change. Verify your entitlement with the Government of Canada.'" />
        </section>
    @endif
</div>
