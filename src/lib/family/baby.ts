import babyBudget from '../../data/baby-budget.json';
import { Money, moneyFrom } from '../money';
import { formatDateLabel } from '../tax/rules';

export function babyStartupDefaults() {
  return babyBudget.startup.map((item) => ({
    ...item,
    actual: '',
    purchased: false,
    gift: false,
    used: false,
    used_cost: '',
    skip: false,
  }));
}

export function babyRecurringDefaults() {
  return babyBudget.recurring;
}

export function babyCcbReference() {
  return babyBudget.ccb;
}

export function babyLastReviewedLabel() {
  return formatDateLabel(babyBudget.last_reviewed);
}

function flag(value: unknown): boolean {
  return value === true || value === 1 || value === '1' || value === 'true';
}

export function summarizeBaby(
  startup: Array<Record<string, unknown>>,
  recurring: Array<Record<string, unknown>>,
  childcare: Record<string, unknown> = {},
  readiness: Record<string, unknown> = {},
) {
  let planned = Money.zero();
  let spent = Money.zero();
  let giftSavings = Money.zero();
  let usedSavings = Money.zero();
  let remaining = Money.zero();

  for (const item of startup) {
    if (flag(item.skip)) {
      continue;
    }

    const linePlanned = moneyFrom(item.planned as string | number | undefined);
    const actual = moneyFrom(item.actual as string | number | undefined);
    const usedCost = moneyFrom(item.used_cost as string | number | undefined);
    const purchased = flag(item.purchased);
    const gift = flag(item.gift);
    const used = flag(item.used);

    planned = planned.add(linePlanned);

    if (gift) {
      giftSavings = giftSavings.add(linePlanned);
      continue;
    }

    if (used && usedCost.isPositive() && linePlanned.greaterThan(usedCost)) {
      usedSavings = usedSavings.add(linePlanned.subtract(usedCost));
    }

    if (purchased) {
      spent = spent.add(actual.isPositive() ? actual : used && usedCost.isPositive() ? usedCost : linePlanned);
      continue;
    }

    remaining = remaining.add(used && usedCost.isPositive() ? usedCost : linePlanned);
  }

  let monthlyRecurring = Money.zero();

  for (const item of recurring) {
    monthlyRecurring = monthlyRecurring.add(moneyFrom(item.monthly as string | number | undefined));
  }

  const recurringFirstYear = monthlyRecurring.multiply('12');
  const childcareNeeded = flag(childcare.needed);
  const startMonth = Math.max(1, Math.min(12, Number(childcare.start_month ?? 1)));
  const childcareMonths = childcareNeeded ? 13 - startMonth : 0;
  const childcareGrossMonthly = moneyFrom(childcare.monthly as string | number | undefined);
  const subsidy = moneyFrom(childcare.subsidy as string | number | undefined);
  let childcareNetMonthly = childcareGrossMonthly.subtract(subsidy);

  if (childcareNetMonthly.isNegative()) {
    childcareNetMonthly = Money.zero();
  }

  const childcareFirstYear = childcareNeeded ? childcareNetMonthly.multiply(String(childcareMonths)) : Money.zero();
  const ccbMonthly = moneyFrom(readiness.ccb_monthly as string | number | undefined);
  const currentSavings = moneyFrom(readiness.current_savings as string | number | undefined);
  const monthlySaved = moneyFrom(readiness.monthly_saved as string | number | undefined);
  const monthsUntil = Math.max(0, Number(readiness.months_until ?? 0));
  const leaveReduction = moneyFrom(readiness.leave_reduction as string | number | undefined);
  const projectedSavings = currentSavings.add(monthlySaved.multiply(String(monthsUntil)));
  const firstYearCost = remaining.add(recurringFirstYear).add(childcareFirstYear);
  const available = projectedSavings.add(ccbMonthly.multiply('12'));
  const gap = firstYearCost
    .add(leaveReduction.multiply(String(Math.max(0, Number(readiness.leave_months ?? 0)))))
    .subtract(available);

  return {
    planned_startup: planned,
    already_spent: spent,
    gift_savings: giftSavings,
    used_savings: usedSavings,
    remaining_purchases: remaining,
    monthly_recurring: monthlyRecurring,
    recurring_first_year: recurringFirstYear,
    childcare_needed: childcareNeeded,
    childcare_months: childcareMonths,
    childcare_monthly: childcareNetMonthly,
    childcare_first_year: childcareFirstYear,
    ccb_monthly: ccbMonthly,
    current_savings: currentSavings,
    projected_savings: projectedSavings,
    first_year_cost: firstYearCost,
    funding_gap: gap,
    amount_left_to_prepare: gap.isPositive() ? gap : Money.zero(),
    surplus: gap.isNegative() ? gap.abs() : Money.zero(),
  };
}
