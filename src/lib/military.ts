import pensionRules from '../data/military/2025/pension.json';
import regular from '../data/military/2025/regular.json';
import reserve from '../data/military/2025/reserve.json';
import sources from '../data/military/2025/sources.json';
import { Money } from './money';
import type { PayFrequency } from './frequency';
import type { Province } from './province';
import { calculatePayroll } from './tax/payroll';

const TABLES = { regular, reserve } as const;

export type MilitaryComponent = keyof typeof TABLES;

export function incrementLabel(increment: string): string {
  return increment === 'basic' ? 'Basic' : `Pay increment ${increment}`;
}

export function militaryRanks(component: MilitaryComponent) {
  return Object.entries(TABLES[component].ranks).map(([key, rank]) => ({
    key,
    short: rank.short,
    name: rank.name,
    group: rank.group,
  }));
}

export function militaryPayLevels(component: MilitaryComponent, rank: string): string[] {
  return Object.keys(rankMeta(component, rank).levels);
}

export function militaryIncrements(component: MilitaryComponent, rank: string, level: string): Record<string, string> {
  const levels = rankMeta(component, rank).levels as Record<string, Record<string, string>>;

  if (!levels[level]) {
    throw new Error(`Unknown pay level [${level}] for ${rank}.`);
  }

  return levels[level];
}

export function lookupMilitaryPay(
  component: MilitaryComponent,
  rank: string,
  increment: string,
  level?: string | null,
) {
  const resolvedLevel = level || militaryPayLevels(component, rank)[0];
  const increments = militaryIncrements(component, rank, resolvedLevel);

  if (!(increment in increments)) {
    throw new Error(`Unknown pay increment [${increment}] for ${rank}.`);
  }

  const meta = rankMeta(component, rank);

  return {
    rank,
    rank_name: meta.name,
    rank_short: meta.short,
    level: resolvedLevel,
    increment,
    increment_label: incrementLabel(increment),
    unit: TABLES[component].unit,
    rate: Money.fromDollars(increments[increment]),
    component,
  };
}

export function cafPension(component: MilitaryComponent, pensionablePay: Money, override?: Money | null) {
  if (override) {
    return {
      included: true,
      amount: override,
      source: 'override',
      note: 'Using the annual pension contribution you entered.',
    };
  }

  const rules = pensionRules[component] as Record<string, unknown>;

  if (!rules?.included) {
    return {
      included: false,
      amount: Money.zero(),
      source: 'omitted',
      note: String(rules?.note ?? 'CAF pension deductions are not included for this component.'),
    };
  }

  const ympe = Money.fromDollars(String(rules.ympe));
  const toYmpe = pensionablePay.min(ympe).multiply(String(rules.rate_to_ympe));
  const above = pensionablePay.greaterThan(ympe)
    ? pensionablePay.subtract(ympe).multiply(String(rules.rate_above_ympe))
    : Money.zero();

  return {
    included: true,
    amount: toYmpe.add(above),
    source: 'regular_force_2026',
    note: 'Estimated Regular Force pension using 9.10% up to the 2026 YMPE of $74,600 and 11.69% above it. Members with 35 years of pensionable service are not modeled.',
  };
}

export function calculateMilitarySalary(options: {
  component: MilitaryComponent;
  rank: string;
  increment: string;
  province: Province;
  frequency: PayFrequency;
  payLevel?: string | null;
  reserveDays?: number;
  taxableAllowances?: Money | null;
  pensionOverride?: Money | null;
}) {
  const reserveDays = options.reserveDays ?? 37;

  if (reserveDays < 0) {
    throw new Error('Paid reserve days cannot be negative.');
  }

  const pay = lookupMilitaryPay(options.component, options.rank, options.increment, options.payLevel);
  const allowances = options.taxableAllowances ?? Money.zero();

  if (allowances.isNegative()) {
    throw new Error('Taxable allowances cannot be negative.');
  }

  const baseAnnual = pay.unit === 'monthly' ? pay.rate.multiply('12') : pay.rate.multiply(String(reserveDays));
  const grossAnnual = baseAnnual.add(allowances);
  const pension = cafPension(options.component, baseAnnual, options.pensionOverride);
  const payroll = calculatePayroll({
    annual_salary: grossAnnual.dollars(),
    province: options.province.code,
    frequency: options.frequency,
    pension: pension.amount.dollars(),
  });

  return {
    component: options.component,
    component_label: options.component === 'regular' ? 'Regular Force' : 'Reserve Force (Class A / B)',
    pay,
    reserve_days: options.component === 'reserve' ? reserveDays : null,
    base_annual: baseAnnual,
    allowances,
    gross_annual: grossAnnual,
    gross_monthly: grossAnnual.divideBy(12),
    gross_biweekly: grossAnnual.divideBy(26),
    pension,
    payroll,
    sources,
    effective_date: sources.effective_date,
    retrieved_date: sources.retrieved_date,
    edition: sources.edition,
  };
}

function rankMeta(component: MilitaryComponent, rank: string) {
  const ranks = TABLES[component].ranks as Record<string, { name: string; short: string; group: string; levels: Record<string, Record<string, string>> }>;

  if (!ranks[rank]) {
    throw new Error(`Unknown CAF rank [${rank}].`);
  }

  return ranks[rank];
}
