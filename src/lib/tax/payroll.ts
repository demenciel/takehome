import calculatorCopy from '../../data/calculator-copy.json';
import { Money, formatPercent, moneyFrom } from '../money';
import { frequencyAdverb, frequencyLabel, frequencyPeriods, parseFrequency, type PayFrequency } from '../frequency';
import { resolveProvince, type Province } from '../province';
import { annualPension } from './cpp';
import { annualEi, annualQpip } from './ei';
import { annualFederalTax } from './federal';
import { annualProvincialTax } from './provincial';
import { CURRENT_TAX_YEAR, provinceRules, taxRulesForYear } from './rules';
import type { PayrollInputs, PayrollResult } from './types';

export function calculatePayroll(inputs: PayrollInputs): PayrollResult {
  const province = resolveProvince(inputs.province);

  if (!province) {
    throw new Error('A valid province is required.');
  }

  const frequency = parseFrequency(inputs.frequency ?? 'biweekly');

  if (!frequency) {
    throw new Error('A valid pay frequency is required.');
  }

  let annual = moneyFrom(inputs.annual_salary ?? 0);

  if (inputs.input_mode === 'hourly') {
    const hourly = moneyFrom(inputs.hourly_wage ?? 0);
    const hours = Number(inputs.hours_per_week ?? 40);

    if (hourly.isZero() || hours <= 0) {
      throw new Error('Enter a valid hourly wage and hours per week.');
    }

    annual = Money.fromRateProduct(hourly.cents, String(hours)).multiply('52');
  }

  if (annual.isNegative()) {
    throw new Error('Salary cannot be negative.');
  }

  const year = inputs.tax_year ?? CURRENT_TAX_YEAR;
  const rules = taxRulesForYear(year);
  const rrsp = moneyFrom(inputs.rrsp);
  const pensionPlan = moneyFrom(inputs.pension);
  const unionDues = moneyFrom(inputs.union_dues);
  const otherDeductions = moneyFrom(inputs.other_deductions);
  const additionalTax = moneyFrom(inputs.additional_tax);
  const pension = annualPension(annual, province.usesQpp ? rules.qpp : rules.cpp);
  const ei = annualEi(annual, province, rules.ei);
  const qpip = annualQpip(annual, province, rules.qpip);
  const f5 = pension.additional.add(pension.second_additional);
  let taxable = annual.subtract(rrsp).subtract(pensionPlan).subtract(unionDues).subtract(f5);

  if (taxable.isNegative()) {
    taxable = Money.zero();
  }

  let provincialTaxable = taxable;

  if (province.usesQpp) {
    const workerDeduction = Money.fromDollars(String(provinceRules(province, year).worker_deduction ?? '0'));
    provincialTaxable = taxable.subtract(workerDeduction);

    if (provincialTaxable.isNegative()) {
      provincialTaxable = Money.zero();
    }
  }

  const federal = annualFederalTax(taxable, annual, pension.base, ei, qpip, province, rules.federal);
  const provincial = annualProvincialTax(
    provincialTaxable,
    annual,
    pension.base,
    ei,
    qpip,
    province,
    provinceRules(province, year),
    rules.federal,
  );

  const incomeTax = federal.t1.add(provincial.t2);
  const additionalTaxAnnual = additionalTax.multiply(String(frequencyPeriods(frequency)));
  const statutory = pension.contribution.add(pension.second_additional).add(ei).add(qpip);
  const optional = rrsp.add(pensionPlan).add(unionDues).add(otherDeductions);
  const totalDeductions = incomeTax.add(statutory).add(optional).add(additionalTaxAnnual);
  let netAnnual = annual.subtract(totalDeductions);

  if (netAnnual.isNegative()) {
    netAnnual = Money.zero();
  }

  const periods = frequencyPeriods(frequency);
  const periodNet = netAnnual.divideBy(periods);
  const periodGross = annual.divideBy(periods);
  const periodTax = incomeTax.add(additionalTaxAnnual).divideBy(periods);
  const periodPension = pension.contribution.add(pension.second_additional).divideBy(periods);
  const periodEi = ei.divideBy(periods);
  const periodQpip = qpip.divideBy(periods);
  const periodOther = optional.divideBy(periods);
  const periodDeductions = periodGross.subtract(periodNet);
  const effectiveRate = formatPercent(incomeTax.cents, annual.cents);
  const pensionKey = province.usesQpp ? 'qpp' : 'cpp';
  const pensionLabel = province.usesQpp ? calculatorCopy.qpp : calculatorCopy.cpp;

  const periodBreakdown = [
    { key: 'gross', label: calculatorCopy.gross, amount: periodGross, kind: 'gross' as const },
    { key: 'income_tax', label: calculatorCopy.income_tax, amount: periodTax, kind: 'deduction' as const },
    { key: pensionKey, label: pensionLabel, amount: periodPension, kind: 'deduction' as const },
    { key: 'ei', label: calculatorCopy.ei, amount: periodEi, kind: 'deduction' as const },
  ];

  if (periodQpip.isPositive()) {
    periodBreakdown.push({ key: 'qpip', label: calculatorCopy.qpip, amount: periodQpip, kind: 'deduction' });
  }

  if (periodOther.isPositive()) {
    periodBreakdown.push({
      key: 'other',
      label: calculatorCopy.other_deductions,
      amount: periodOther,
      kind: 'deduction',
    });
  }

  const annualBreakdown = [
    { key: 'gross', label: calculatorCopy.gross_salary, amount: annual, kind: 'gross' as const },
    { key: 'income_tax', label: calculatorCopy.estimated_income_tax, amount: incomeTax, kind: 'deduction' as const },
    {
      key: pensionKey,
      label: province.usesQpp ? calculatorCopy.estimated_qpp : calculatorCopy.estimated_cpp,
      amount: pension.contribution.add(pension.second_additional),
      kind: 'deduction' as const,
    },
    { key: 'ei', label: calculatorCopy.estimated_ei, amount: ei, kind: 'deduction' as const },
  ];

  if (qpip.isPositive()) {
    annualBreakdown.push({ key: 'qpip', label: calculatorCopy.estimated_qpip, amount: qpip, kind: 'deduction' });
  }

  if (optional.isPositive()) {
    annualBreakdown.push({
      key: 'other',
      label: calculatorCopy.other_deductions,
      amount: optional,
      kind: 'deduction',
    });
  }

  annualBreakdown.push({
    key: 'net',
    label: calculatorCopy.estimated_take_home,
    amount: netAnnual,
    kind: 'net',
  });

  const warnings = [calculatorCopy.disclaimer];

  if (province.usesQpp) {
    warnings.push(calculatorCopy.quebec_estimate_note);
  }

  if (pension.second_additional.isPositive()) {
    warnings.push(calculatorCopy.cpp2_note);
  }

  return {
    summary: calculatorCopy.result_heading,
    headlineAmount: periodNet,
    headlinePeriod: frequencyAdverb(frequency),
    inputs: {
      annual_salary: annual,
      province: province.code,
      province_name: province.name,
      frequency,
      tax_year: year,
      rrsp,
      pension: pensionPlan,
      union_dues: unionDues,
      other_deductions: otherDeductions,
      additional_tax_per_period: additionalTax,
    },
    periodBreakdown,
    annualBreakdown,
    metrics: {
      effective_tax_rate: effectiveRate,
      average_deductions: periodDeductions,
      federal_tax: federal.t1,
      provincial_tax: provincial.t2,
      taxable_income: taxable,
      cpp: pension.contribution,
      cpp2: pension.second_additional,
      ei,
      qpip,
      net_annual: netAnnual,
      net_period: periodNet,
    },
    warnings,
    metadata: {
      calculator: 'paycheck',
      tax_year: year,
      edition: rules.sources.edition ?? null,
      claim_code: 1,
      federal_bpa: federal.bpa,
      provincial_bpa: provincial.bpa,
    },
    shareText: `I make ${annual.format()}/year in ${province.name}. My estimated take-home pay is ${netAnnual.format()}/year.`,
    copyText: [
      `${annual.format()} salary`,
      province.name,
      frequencyLabel(frequency),
      '',
      `Estimated take-home: ${periodNet.format()}`,
      `Gross: ${periodGross.format()}`,
      `Income tax: ${periodTax.format()}`,
      `${pensionLabel}: ${periodPension.format()}`,
      `EI: ${periodEi.format()}`,
      periodQpip.isPositive() ? `QPIP: ${periodQpip.format()}` : null,
      periodOther.isPositive() ? `Other deductions: ${periodOther.format()}` : null,
    ]
      .filter(Boolean)
      .join('\n'),
    province,
  };
}

export function netsByFrequency(annual: Money) {
  return {
    annual,
    monthly: annual.divideBy(12),
    semimonthly: annual.divideBy(24),
    biweekly: annual.divideBy(26),
    weekly: annual.divideBy(52),
  };
}
