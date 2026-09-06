@props(['text' => null])

<p {{ $attributes->class('mt-6 text-sm leading-6 text-ink-soft') }}>
    {{ $text ?? 'Results are estimates for a full-year employee claiming the basic personal amount. They are not a pay stub, tax advice, or an official CRA or Revenu Québec calculation. Employer withholding on a single cheque can differ from this annual estimate.' }}
</p>
