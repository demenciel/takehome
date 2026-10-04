import benefits from '../../data/benefits.json';
import { Money, formatPercent, moneyFrom } from '../money';
import { parseFrequency, type PayFrequency } from '../frequency';
import { resolveProvince, type Province } from '../province';
import { calculatePayroll } from '../tax/payroll';
import { bcdiv } from '../bcmath';
import {
  benefitsLastReviewedLabel,
  benefitsYear,
  estimateBenefits,
  splitLeave,
  weeklyBenefit,
  type EiProgram,
  type LeaveType,
} from './benefits';

const QPIP_URL = 'https://www.rqap.gouv.qc.ca/en';

export function projectParentalLeave(input: Record<string, unknown>) {
  const province = typeof input.province === 'string' ? resolveProvince(input.province) : null;

  if (!province) {
    throw new Error('A province or territory is required.');
  }

  const salary = input.salary instanceof Money ? input.salary : Money.fromDollars(String(input.salary ?? 0));
  const frequency = parseFrequency(String(input.frequency ?? 'biweekly')) ?? 'biweekly';

  if (province.usesQpp) {
    return quebecResponse(province, salary, frequency);
  }

  const leaveType = String(input.leave_type ?? 'maternity_standard') as LeaveType;
  const plannedWeeks = Number(input.planned_weeks ?? 0);
  const partnerWeeks = Number(input.partner_weeks ?? 0);
  const split = splitLeave(leaveType, plannedWeeks, partnerWeeks);
  const employment = employmentIncome(province, salary, frequency);
  const maternity =
    split.maternity_weeks > 0 ? estimateBenefits(salary, 'maternity', split.maternity_weeks) : null;
  const parental = estimateBenefits(salary, split.parental_program, split.parental_weeks);
  const eiTotal = (maternity?.total ?? Money.zero()).add(parental.total);
  const leaveWeeks = split.maternity_weeks + split.parental_weeks;
  const averageWeeklyEi = leaveWeeks > 0 ? eiTotal.divideBy(leaveWeeks) : Money.zero();
  const topUp = calculateTopUp(
    employment.gross_weekly,
    averageWeeklyEi,
    Boolean(input.top_up_enabled),
    String(input.top_up_percent ?? '0'),
    Number(input.top_up_weeks ?? 0),
    String(input.top_up_mode ?? 'to_percent'),
  );
  const leaveWeekly = averageWeeklyEi.add(leaveWeeks > 0 ? topUp.total.divideBy(leaveWeeks) : Money.zero());
  const leaveMonthly = leaveWeekly.multiply('52').divideBy(12);
  const reductionMonthly = employment.net_monthly.subtract(leaveMonthly);
  const comparison = standardVersusExtended(salary, leaveType, plannedWeeks, partnerWeeks);
  const household = householdProjection(province, employment, leaveMonthly, leaveWeeks, input);

  return {
    supported: true,
    quebec: false,
    province,
    leave_type: leaveType,
    program_label: leaveType.includes('extended') ? 'extended' : 'standard',
    who: String(input.who ?? 'birth_parent'),
    employment,
    split,
    maternity,
    parental,
    weekly_ei: averageWeeklyEi,
    monthly_ei: averageWeeklyEi.multiply('52').divideBy(12),
    ei_total: eiTotal,
    leave_weeks: leaveWeeks,
    top_up: topUp,
    leave_weekly: leaveWeekly,
    leave_monthly: leaveMonthly,
    leave_total: leaveWeekly.multiply(String(leaveWeeks)),
    reduction_monthly: reductionMonthly,
    reduction_percent: formatPercent(reductionMonthly.cents, employment.net_monthly.cents),
    income_difference: employment.net_weekly
      .multiply(String(leaveWeeks))
      .subtract(leaveWeekly.multiply(String(leaveWeeks))),
    comparison,
    household,
    warnings: [...new Set([...split.warnings, ...(maternity?.warnings ?? []), ...parental.warnings, benefits.disclaimer])],
    year: benefitsYear(),
    reviewed: benefitsLastReviewedLabel(),
    sources: benefits.sources,
  };
}

function employmentIncome(province: Province, salary: Money, frequency: PayFrequency) {
  const payroll = calculatePayroll({
    annual_salary: salary.dollars(),
    province: province.code,
    frequency,
  });

  return {
    gross_annual: salary,
    gross_weekly: salary.divideBy(52),
    gross_biweekly: salary.divideBy(26),
    gross_monthly: salary.divideBy(12),
    net_annual: payroll.metrics.net_annual,
    net_weekly: payroll.metrics.net_annual.divideBy(52),
    net_monthly: payroll.metrics.net_annual.divideBy(12),
    net_period: payroll.metrics.net_period,
    frequency,
  };
}

function calculateTopUp(
  grossWeekly: Money,
  eiWeekly: Money,
  enabled: boolean,
  percent: string,
  weeks: number,
  mode: string,
) {
  if (!enabled || weeks < 1 || Number.isNaN(Number(percent)) || Number(percent) <= 0) {
    return {
      enabled: false,
      mode,
      percent,
      weeks: 0,
      weekly: Money.zero(),
      total: Money.zero(),
      combined_weekly: eiWeekly,
    };
  }

  const rate = bcdiv(String(percent), '100', 8);
  let weekly = mode === 'add_percent' ? grossWeekly.multiply(rate) : grossWeekly.multiply(rate).subtract(eiWeekly);

  if (weekly.isNegative()) {
    weekly = Money.zero();
  }

  return {
    enabled: true,
    mode,
    percent,
    weeks,
    weekly,
    total: weekly.multiply(String(weeks)),
    combined_weekly: eiWeekly.add(weekly),
  };
}

function standardVersusExtended(salary: Money, leaveType: LeaveType, plannedWeeks: number, partnerWeeks: number) {
  const includesMaternity = leaveType === 'maternity_standard' || leaveType === 'maternity_extended';
  let standard = pathTotals(salary, 'maternity_standard', includesMaternity ? Math.max(plannedWeeks, 50) : Math.max(plannedWeeks, 35), partnerWeeks);
  let extended = pathTotals(salary, 'maternity_extended', includesMaternity ? Math.max(plannedWeeks, 76) : Math.max(plannedWeeks, 61), partnerWeeks);

  if (!includesMaternity && (leaveType === 'standard' || leaveType === 'extended')) {
    standard = pathTotals(salary, 'standard', Math.max(plannedWeeks, 35), partnerWeeks);
    extended = pathTotals(salary, 'extended', Math.max(plannedWeeks, 61), partnerWeeks);
  }

  return { standard, extended };
}

function pathTotals(salary: Money, leaveType: LeaveType, weeks: number, partnerWeeks: number) {
  const split = splitLeave(leaveType, weeks, partnerWeeks);
  const maternity =
    split.maternity_weeks > 0 ? estimateBenefits(salary, 'maternity', split.maternity_weeks).total : Money.zero();
  const parental = estimateBenefits(salary, split.parental_program, split.parental_weeks).total;
  const total = maternity.add(parental);
  const used = split.maternity_weeks + split.parental_weeks;
  const weekly = used > 0 ? total.divideBy(used) : Money.zero();

  return {
    weekly,
    monthly: weekly.multiply('52').divideBy(12),
    weeks: used,
    total,
    label: leaveType.includes('extended') ? 'Extended' : 'Standard',
  };
}

function householdProjection(
  province: Province,
  employment: ReturnType<typeof employmentIncome>,
  leaveMonthly: Money,
  leaveWeeks: number,
  input: Record<string, unknown>,
) {
  const partnerSalary = optionalMoney(input.partner_income);
  const expenses = optionalMoney(input.monthly_expenses);
  const savingsTarget = optionalMoney(input.monthly_savings);
  const partnerWeeks = Number(input.partner_weeks ?? 0);

  if (partnerSalary === null && expenses === null) {
    return { included: false as const };
  }

  const partnerWorking = partnerWorkingMonthly(province, partnerSalary);
  let partnerDuring = partnerWorking;

  if (partnerSalary && partnerSalary.isPositive() && partnerWeeks > 0) {
    const program: EiProgram = String(input.leave_type ?? '').includes('extended')
      ? 'extended_parental'
      : 'standard_parental';
    partnerDuring = weeklyBenefit(partnerSalary, program).multiply('52').divideBy(12);
  }

  const before = employment.net_monthly.add(partnerWorking);
  const during = leaveMonthly.add(partnerDuring);
  const expenseAmount = expenses ?? Money.zero();
  const shortfall = expenseAmount.subtract(during);
  const months = leaveWeeks > 0 ? Math.max(1, Math.round((leaveWeeks * 12) / 52)) : 0;
  const totalShortfall = shortfall.isPositive() ? shortfall.multiply(String(months)) : Money.zero();

  return {
    included: true as const,
    before,
    during,
    expenses: expenseAmount,
    savings_target: savingsTarget ?? Money.zero(),
    monthly_shortfall: shortfall,
    total_shortfall: totalShortfall,
    buffer: totalShortfall,
    months,
  };
}

function partnerWorkingMonthly(province: Province, partnerSalary: Money | null): Money {
  if (!partnerSalary || partnerSalary.isZero()) {
    return Money.zero();
  }

  return calculatePayroll({
    annual_salary: partnerSalary.dollars(),
    province: province.code,
    frequency: 'monthly',
  }).metrics.net_annual.divideBy(12);
}

function optionalMoney(value: unknown): Money | null {
  if (value instanceof Money) {
    return value;
  }

  if (value === null || value === undefined || value === '' || Number.isNaN(Number(value))) {
    return null;
  }

  return moneyFrom(value as string | number);
}

function quebecResponse(province: Province, salary: Money, frequency: PayFrequency) {
  return {
    supported: false,
    quebec: true,
    province,
    employment: employmentIncome(province, salary, frequency),
    qpip_url: QPIP_URL,
    warnings: [
      'Québec maternity, paternity, parental, and adoption benefits are paid through the Québec Parental Insurance Plan (QPIP), not federal EI. This calculator does not estimate QPIP. Use the official RQAP estimator for Québec leave income.',
    ],
    year: benefitsYear(),
    reviewed: benefitsLastReviewedLabel(),
    sources: benefits.sources,
  };
}
