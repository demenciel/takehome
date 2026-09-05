<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">{{ $rules->year }} Canadian payroll tax rates</h1>
        <x-tax-freshness class="mt-4" />
        <p class="mt-6 text-lg text-ink-soft">These are the amounts this site uses for estimates. They come from the versioned tax files, not from a live government API. Official withholding can still differ. See the <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">methodology</a>.</p>

        <h2 class="mt-10 font-serif text-3xl">Federal income tax</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Taxable income from</th>
                        <th scope="col">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules->federal['brackets'] as $bracket)
                        <tr>
                            <td>${{ number_format((float) $bracket['threshold']) }}</td>
                            <td>{{ number_format(((float) $bracket['rate']) * 100, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-ink-soft">Basic personal amount (most filers): ${{ number_format((float) $rules->federal['basic_personal_amount_max']) }}. Canada Employment Amount: ${{ number_format((float) $rules->federal['canada_employment_amount']) }}.</p>

        <h2 class="mt-10 font-serif text-3xl">CPP</h2>
        <p class="mt-4 text-ink-soft">YMPE ${{ number_format((float) $rules->cpp['ympe']) }}, YAMPE ${{ number_format((float) $rules->cpp['yampe']) }}, employee rate {{ number_format(((float) $rules->cpp['employee_rate']) * 100, 2) }}%, maximum ${{ number_format((float) $rules->cpp['maximum_employee'], 2) }}. CPP2 maximum ${{ number_format((float) $rules->cpp['maximum_second_additional'], 2) }}.</p>

        <h2 class="mt-10 font-serif text-3xl">QPP</h2>
        <p class="mt-4 text-ink-soft">MPE ${{ number_format((float) $rules->qpp['ympe']) }}, employee rate {{ number_format(((float) $rules->qpp['employee_rate']) * 100, 2) }}%, maximum ${{ number_format((float) $rules->qpp['maximum_employee'], 2) }}.</p>

        <h2 class="mt-10 font-serif text-3xl">EI and QPIP</h2>
        <p class="mt-4 text-ink-soft">Maximum insurable earnings ${{ number_format((float) $rules->ei['maximum_insurable_earnings']) }}. Employee rate {{ number_format(((float) $rules->ei['employee_rate']) * 100, 2) }}% (maximum ${{ number_format((float) $rules->ei['maximum_employee'], 2) }}). Quebec EI rate {{ number_format(((float) $rules->ei['quebec_employee_rate']) * 100, 2) }}%.</p>
        <p class="mt-3 text-ink-soft">QPIP employee rate {{ number_format(((float) $rules->qpip['employee_rate']) * 100, 2) }}%, maximum ${{ number_format((float) $rules->qpip['maximum_employee'], 2) }}.</p>

        <h2 class="mt-10 font-serif text-3xl">Provincial and territorial basics</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Jurisdiction</th>
                        <th scope="col">Lowest rate</th>
                        <th scope="col">Basic personal amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($provinces as $province)
                        @php $data = $rules->province($province); @endphp
                        <tr>
                            <th scope="row">
                                <a href="{{ route('paycheck.province', $province->slug()) }}" class="text-accent-dark underline">{{ $province->name() }}</a>
                            </th>
                            <td>{{ number_format(((float) $data['lowest_rate']) * 100, 2) }}%</td>
                            <td>${{ number_format((float) ($data['basic_personal_amount'] ?? $rules->federal['basic_personal_amount_max'])) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-sm text-ink-soft">Yukon’s personal amount follows the federal BPAF formula. Manitoba phases out BPAMB at high incomes. Exact brackets are in the tax data files.</p>
    </article>
</x-layouts.app>
