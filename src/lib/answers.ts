import { hourlyFromAnnual, assumptionLabel, hoursPerYear } from './hourly';
import { Money } from './money';
import type { Province } from './province';
import { taxFreshness } from './tax/rules';
import type { PayrollResult } from './tax/types';
import { absoluteUrl } from './catalog';
import type { PayFrequency } from './frequency';

export interface AnswerSummary {
  question: string;
  answer: string;
  facts: Array<{ label: string; value: string }>;
  jurisdiction: string | null;
  year: number;
  updated: string;
  methodology_url: string;
  sources: Array<{ title: string; url: string }>;
}

function freshness() {
  return taxFreshness();
}

function taxSources(province?: Province) {
  const preferred = province?.usesQpp ? ['revenu', 'quebec', 'cra', 't4127'] : ['cra', 't4127', 'canada revenue'];
  const ranked = [...freshness().sources].sort((a, b) => {
    const score = (source: { title: string; url: string }) => {
      const haystack = `${source.title} ${source.url}`.toLowerCase();
      const index = preferred.findIndex((needle) => haystack.includes(needle));
      return index === -1 ? 99 : index;
    };
    return score(a) - score(b);
  });

  return ranked.slice(0, 2).map((source) => ({ title: source.title, url: source.url }));
}

export function makeAnswer(
  question: string,
  answer: string,
  facts: Array<{ label: string; value: string }> = [],
  jurisdiction: string | null = null,
  sources: Array<{ title: string; url: string }> = [],
  updated?: string,
): AnswerSummary {
  return {
    question,
    answer,
    facts,
    jurisdiction,
    year: freshness().year,
    updated: updated ?? freshness().lastUpdatedLabel,
    methodology_url: absoluteUrl('/methodology'),
    sources,
  };
}

export function canadaAnswer(): AnswerSummary {
  return makeAnswer(
    'How is take-home pay calculated in Canada?',
    'Canadian take-home pay is gross salary minus federal income tax, provincial or territorial tax, CPP or QPP, and EI. Quebec also deducts QPIP and uses Revenu Québec provincial tax. Choose a province — this calculator does not assume one.',
    [
      { label: 'Payroll year', value: String(freshness().year) },
      { label: 'Federal formula', value: 'CRA T4127 Option 1' },
      { label: 'Claim code used', value: '1 (basic personal amount)' },
    ],
    'Canada',
    taxSources(),
  );
}

export function provinceAnswer(
  province: Province,
  example?: { salary: number; net_annual: Money; effective_tax_rate: string },
): AnswerSummary {
  const year = freshness().year;
  const extras = province.usesQpp ? 'QPP, EI, and QPIP' : 'CPP, and EI';

  if (example) {
    const gross = `$${example.salary.toLocaleString('en-CA')}`;
    return makeAnswer(
      `How is take-home pay calculated in ${province.name}?`,
      `In ${province.name}, a ${gross} salary has an estimated take-home of ${example.net_annual.format()} per year using ${year} payroll rules. Take-home is salary minus federal tax, ${province.adjective} tax, ${extras}.`,
      [
        { label: 'Example gross salary', value: gross },
        { label: 'Estimated annual take-home', value: example.net_annual.format() },
        { label: 'Effective tax rate', value: `${example.effective_tax_rate}%` },
        { label: 'Payroll year', value: String(year) },
      ],
      province.name,
      taxSources(province),
    );
  }

  return makeAnswer(
    `How is take-home pay calculated in ${province.name}?`,
    `${province.name} take-home pay is estimated from federal tax, ${province.adjective} tax, ${extras}. Enter a salary to see the ${year} estimate for a full-year employee claiming the basic personal amount.`,
    [
      { label: 'Payroll year', value: String(year) },
      { label: 'Claim code used', value: '1 (basic personal amount)' },
    ],
    province.name,
    taxSources(province),
  );
}

export function salaryAnswer(province: Province, salary: number, result: PayrollResult): AnswerSummary {
  const net = result.metrics.net_annual;
  const monthly = net.divideBy(12);
  const biweekly = net.divideBy(26);
  const weekly = net.divideBy(52);
  const hourly = hourlyFromAnnual(Money.fromDollars(salary));
  const gross = `$${salary.toLocaleString('en-CA')}`;
  const incomeTax = result.metrics.federal_tax.add(result.metrics.provincial_tax);

  return makeAnswer(
    `How much is an ${gross} salary after tax in ${province.name}?`,
    `An ${gross} annual salary in ${province.name} results in an estimated take-home pay of ${net.format()} per year, or about ${monthly.format()} per month, based on ${freshness().year} payroll rules.`,
    [
      { label: 'Annual gross', value: Money.fromDollars(salary).format() },
      { label: 'Annual net', value: net.format() },
      { label: 'Monthly net', value: monthly.format() },
      { label: 'Biweekly net', value: biweekly.format() },
      { label: 'Weekly net', value: weekly.format() },
      { label: 'Income tax', value: incomeTax.format() },
      { label: 'Federal tax', value: result.metrics.federal_tax.format() },
      { label: `${province.name} tax`, value: result.metrics.provincial_tax.format() },
      {
        label: province.usesQpp ? 'QPP / QPP2' : 'CPP / CPP2',
        value: result.metrics.cpp.add(result.metrics.cpp2).format(),
      },
      {
        label: province.usesQpp ? 'EI + QPIP' : 'EI',
        value: result.metrics.ei.add(result.metrics.qpip).format(),
      },
      { label: 'Hourly equivalent (gross)', value: hourly.format() },
    ],
    province.name,
    taxSources(province),
  );
}

export function salaryExplanation(province: Province, salary: number, result: PayrollResult): string {
  const formatted = `$${salary.toLocaleString('en-CA')}`;
  const pension = province.usesQpp ? 'QPP' : 'CPP';
  const second = result.metrics.cpp2.isPositive()
    ? ` plus ${result.metrics.cpp2.format()} in ${province.usesQpp ? 'QPP2' : 'CPP2'}`
    : '';
  const qpip =
    province.usesQpp && result.metrics.qpip.isPositive() ? ` QPIP is ${result.metrics.qpip.format()}.` : '';

  return `On a ${formatted} salary in ${province.name}, this estimate puts annual take-home at ${result.metrics.net_annual.format()} after about ${result.metrics.federal_tax.format()} federal tax and ${result.metrics.provincial_tax.format()} ${province.adjective} tax. ${pension} is ${result.metrics.cpp.format()}${second}. EI is ${result.metrics.ei.format()}.${qpip} The effective income-tax rate on this page is about ${result.metrics.effective_tax_rate}% of gross salary — that rate does not include ${pension} or EI.`;
}

export function salaryFaqs(province: Province, salary: number, result: PayrollResult) {
  const explanation = salaryExplanation(province, salary, result);
  const gross = `$${salary.toLocaleString('en-CA')}`;
  const monthly = result.metrics.net_annual.divideBy(12);
  const biweekly = result.metrics.net_annual.divideBy(26);
  const hourly = hourlyFromAnnual(Money.fromDollars(salary));
  const pension = province.usesQpp ? 'QPP' : 'CPP';
  const second = result.metrics.cpp2.isPositive()
    ? ` plus ${result.metrics.cpp2.format()} ${province.usesQpp ? 'QPP2' : 'CPP2'}`
    : '';
  const qpip = result.metrics.qpip.isPositive() ? ` QPIP is ${result.metrics.qpip.format()}.` : '';
  const tax = result.metrics.federal_tax.add(result.metrics.provincial_tax);

  return [
    { question: `How much is ${gross} after tax in ${province.name}?`, answer: explanation },
    {
      question: `What is ${gross} per month after tax in ${province.name}?`,
      answer: `Estimated monthly take-home is ${monthly.format()} on a ${gross} salary in ${province.name}.`,
    },
    {
      question: `What is ${gross} biweekly after tax in ${province.name}?`,
      answer: `Estimated biweekly take-home is ${biweekly.format()}, which is the annual net divided by 26.`,
    },
    {
      question: 'How much income tax is paid?',
      answer: `Estimated income tax is ${tax.format()} (${result.metrics.federal_tax.format()} federal and ${result.metrics.provincial_tax.format()} ${province.adjective}).`,
    },
    {
      question: `How much ${pension} and EI are deducted?`,
      answer: `${pension} is ${result.metrics.cpp.format()}${second}. EI is ${result.metrics.ei.format()}.${qpip}`,
    },
    {
      question: `What is ${gross} per hour?`,
      answer: `Using ${assumptionLabel()}, ${gross} is about ${hourly.format()}/hour before tax. That is a conversion, not a contracted wage.`,
    },
  ];
}

export function conversionAnswer(mode: 'hourly_to_salary' | 'salary_to_hourly'): AnswerSummary {
  if (mode === 'salary_to_hourly') {
    const hourly = hourlyFromAnnual(Money.fromDollars(80000));
    return makeAnswer(
      'What is an $80,000 salary per hour?',
      `Using ${assumptionLabel()}, $80,000 is about ${hourly.format()}/hour before tax. That is a conversion, not a contracted wage or take-home pay.`,
      [
        { label: 'Example salary', value: '$80,000' },
        { label: 'Hourly equivalent (gross)', value: hourly.format() },
        { label: 'Hours assumption', value: `${hoursPerYear().toLocaleString('en-CA')} hours/year` },
      ],
      'Canada',
    );
  }

  return makeAnswer(
    'How do I convert hourly pay to an annual salary?',
    `Multiply hourly wage × hours per week × weeks per year. The default on this site is ${assumptionLabel()}. That is a gross conversion — tax still depends on province and the annual total.`,
    [
      { label: 'Default hours/week', value: '40' },
      { label: 'Default weeks/year', value: '52' },
      { label: 'Default hours/year', value: hoursPerYear().toLocaleString('en-CA') },
    ],
    'Canada',
  );
}

export function overtimeAnswer(
  province: Province | null,
  example: ReturnType<typeof import('./overtime').estimateOvertime>,
): AnswerSummary {
  const place = province?.name ?? 'Canada';
  const rules = example.rules;
  const thresholdText = rules.weekly_threshold
    ? `most employees qualify for overtime after the standard weekly threshold of ${rules.weekly_threshold} hours`
    : 'overtime thresholds vary by province';
  const answer = province
    ? `In ${place}, ${thresholdText}. At $30/hour with 8 overtime hours, gross overtime is ${example.overtime_pay.format()} and estimated after-tax overtime is ${example.after_tax_overtime.format()} based on ${freshness().year} payroll rules. Occupations and contracts can differ.`
    : `Overtime rules differ by province. As an Ontario example at $30/hour with 8 overtime hours, gross overtime is ${example.overtime_pay.format()} and estimated after-tax overtime is ${example.after_tax_overtime.format()}. After-tax overtime is take-home with overtime minus take-home without it.`;

  return makeAnswer(
    `How much overtime pay do I keep after tax in ${place}?`,
    answer,
    [
      { label: 'Example hourly wage', value: '$30.00' },
      { label: 'Example overtime hours', value: '8' },
      { label: 'Overtime rate', value: `${example.overtime_rate.format()}/hour` },
      { label: 'Gross overtime', value: example.overtime_pay.format() },
      { label: 'Estimated after-tax overtime', value: example.after_tax_overtime.format() },
      { label: 'Standard weekly threshold', value: rules.weekly_threshold ? `${rules.weekly_threshold} hours` : 'Varies by province' },
      { label: 'Standard multiplier', value: `${rules.multiplier}×` },
      { label: 'Daily overtime', value: rules.daily_threshold ? `${rules.daily_threshold} hours` : 'No general daily threshold' },
    ],
    place,
    rules.source ? [{ title: rules.source.title, url: rules.source.url }] : [],
    example.retrieved_date,
  );
}

export function bonusAnswer(province: Province | null, example: ReturnType<typeof import('./tax/comparison').comparePayroll>): AnswerSummary {
  const place = province?.name ?? 'Canada';
  return makeAnswer(
    `How much of a $10,000 bonus do I keep after tax in ${place}?`,
    `On an $80,000 salary, a $10,000 bonus in ${place} has an estimated net of ${example.net_annual_delta.format()} (${example.keep_percent}% kept) after about ${example.tax_delta.format()} in extra income tax plus any remaining CPP/QPP or EI/QPIP. Employer withholding on the bonus cheque can differ from this annual estimate.`,
    [
      { label: 'Gross bonus', value: '$10,000.00' },
      { label: 'Estimated additional tax', value: example.tax_delta.format() },
      { label: 'CPP/QPP impact', value: example.pension_delta.format() },
      { label: 'EI/QPIP impact', value: example.insurance_delta.format() },
      { label: 'Estimated net bonus', value: example.net_annual_delta.format() },
      { label: 'Share kept', value: `${example.keep_percent}%` },
    ],
    place,
    taxSources(province ?? undefined),
  );
}

export function raiseAnswer(province: Province | null, example: ReturnType<typeof import('./tax/comparison').comparePayroll>): AnswerSummary {
  const place = province?.name ?? 'Canada';
  const net = example.net_annual_delta;
  return makeAnswer(
    `How much of a $10,000 raise do I actually keep in ${place}?`,
    `Moving from $75,000 to $85,000 in ${place} is a $10,000 raise. Estimated extra take-home is ${net.format()} a year, or ${net.divideBy(12).format()} a month (${example.keep_percent}% of the raise), using ${freshness().year} payroll rules.`,
    [
      { label: 'Gross annual raise', value: example.gross_delta.format() },
      { label: 'Net annual increase', value: net.format() },
      { label: 'Monthly increase', value: net.divideBy(12).format() },
      { label: 'Biweekly increase', value: net.divideBy(26).format() },
      { label: 'Share of raise kept', value: `${example.keep_percent}%` },
    ],
    place,
    taxSources(province ?? undefined),
  );
}

export function militaryAnswer(example: ReturnType<typeof import('./military').calculateMilitarySalary>): AnswerSummary {
  const monthlyBase = example.pay.rate.format();
  const takeHome = example.payroll.metrics.net_annual.divideBy(12).format();
  const official = example.sources.sources.filter((source) => source.title.toLowerCase().includes('regular force'));

  return makeAnswer(
    'How much does a Regular Force Corporal earn and take home?',
    `A Regular Force ${example.pay.rank_short} at ${example.pay.increment_label} earns ${monthlyBase}/month in base pay under the current published DND table. Estimated take-home in Ontario is approximately ${takeHome}/month before excluded allowances and benefits.`,
    [
      { label: 'Component', value: example.component_label },
      { label: 'Rank', value: example.pay.rank_name },
      { label: 'Pay increment', value: example.pay.increment_label },
      { label: 'Official monthly base pay', value: monthlyBase },
      { label: 'Pay-table effective date', value: example.effective_date },
      { label: 'Annualized base pay', value: example.base_annual.format() },
      { label: 'Estimated annual take-home', value: example.payroll.metrics.net_annual.format() },
      { label: 'Pension included', value: example.pension.included ? 'Yes (Regular Force estimate)' : 'No' },
      { label: 'Allowances included', value: 'No, unless entered' },
    ],
    'Ontario example',
    official.length ? official : example.sources.sources.slice(0, 2),
    example.retrieved_date,
  );
}

export function parentalAnswer(example: {
  weekly_ei: { format(): string };
  monthly_ei: { format(): string };
  ei_total: { format(): string };
  leave_weeks: number;
  year: number;
  parental: { max_weekly: { format(): string } };
}): AnswerSummary {
  return makeAnswer(
    'How much will I make on parental leave in Canada?',
    `On an $80,000 Ontario salary, estimated federal EI for maternity plus standard parental leave is ${example.weekly_ei.format()} a week in ${example.year} — the published weekly maximum — or about ${example.monthly_ei.format()} a month before tax. Over ${example.leave_weeks} counted weeks that is about ${example.ei_total.format()}. Eligibility still depends on insurable hours and a Service Canada claim. Québec uses QPIP, not federal EI.`,
    [
      { label: 'Example salary', value: '$80,000' },
      { label: 'Example province', value: 'Ontario' },
      { label: 'Leave path', value: 'Maternity + standard parental' },
      { label: 'Estimated weekly EI', value: example.weekly_ei.format() },
      { label: 'Approximate monthly EI', value: example.monthly_ei.format() },
      { label: 'Counted weeks', value: String(example.leave_weeks) },
      { label: 'Estimated total EI', value: example.ei_total.format() },
      { label: `${example.year} weekly maximum (standard)`, value: example.parental.max_weekly.format() },
    ],
    'Canada (federal EI; Ontario example)',
  );
}

export function eiBenefitsAnswer(example: {
  weekly_benefit: { format(): string };
  total: { format(): string };
  max_weekly: { format(): string };
  rate_percent: string;
  max_weeks: number;
  weeks: number;
}): AnswerSummary {
  const year = freshness().year;
  return makeAnswer(
    `What is the maximum EI maternity benefit in ${year}?`,
    `The ${year} maximum weekly EI maternity benefit is ${example.max_weekly.format()}, which is ${example.rate_percent}% of insurable weekly earnings up to the yearly maximum. Maternity benefits last up to ${example.max_weeks} weeks. On an $80,000 example that is ${example.weekly_benefit.format()} a week, or ${example.total.format()} over ${example.weeks} weeks before tax.`,
    [
      { label: `${year} maternity rate`, value: `${example.rate_percent}%` },
      { label: `${year} weekly maximum`, value: example.max_weekly.format() },
      { label: 'Maternity weeks', value: `Up to ${example.max_weeks}` },
      { label: 'Example weekly benefit', value: example.weekly_benefit.format() },
      { label: 'Example total (15 weeks)', value: example.total.format() },
    ],
    'Canada (federal EI)',
  );
}

export function babyAnswer(example: {
  remaining_purchases: { format(): string };
  monthly_recurring: { format(): string };
  first_year_cost: { format(): string };
  planned_startup: { format(): string };
  recurring_first_year: { format(): string };
}): AnswerSummary {
  return makeAnswer(
    'How much does a baby cost in Canada?',
    `There is no single Canadian baby-cost figure. Using this page’s editable planning defaults, estimated remaining startup purchases are ${example.remaining_purchases.format()} and monthly baby expenses are ${example.monthly_recurring.format()}, or about ${example.first_year_cost.format()} over the first year before childcare. Edit every line — gifts, used items, and skipped purchases change the total.`,
    [
      { label: 'Planned startup (defaults)', value: example.planned_startup.format() },
      { label: 'Remaining purchases', value: example.remaining_purchases.format() },
      { label: 'Monthly baby expenses', value: example.monthly_recurring.format() },
      { label: 'First-year recurring total', value: example.recurring_first_year.format() },
      { label: 'First-year cost before childcare', value: example.first_year_cost.format() },
      { label: 'Childcare assumed', value: 'No, unless entered' },
    ],
    'Canada',
  );
}

export function hubAnswer(question: string, answer: string): AnswerSummary {
  return makeAnswer(
    question,
    answer,
    [
      { label: 'Payroll year', value: String(freshness().year) },
      { label: 'Federal formula', value: 'CRA T4127 Option 1' },
      { label: 'Claim code used', value: '1 (basic personal amount)' },
    ],
    'Canada',
    taxSources(),
  );
}

export { taxSources };
