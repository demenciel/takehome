import { Money } from './money';

export const HOURS_PER_WEEK = 40;
export const WEEKS_PER_YEAR = 52;

export function hoursPerYear(hoursPerWeek = HOURS_PER_WEEK, weeks = WEEKS_PER_YEAR): number {
  return hoursPerWeek * weeks;
}

export function annualFromHourly(hourly: Money, hoursPerWeek = HOURS_PER_WEEK): Money {
  return Money.fromRateProduct(hourly.cents, String(hoursPerWeek)).multiply(String(WEEKS_PER_YEAR));
}

export function hourlyFromAnnual(annual: Money, hoursPerWeek = HOURS_PER_WEEK): Money {
  const hours = hoursPerWeek * WEEKS_PER_YEAR;

  if (hours <= 0) {
    return Money.zero();
  }

  return annual.divideBy(Math.round(hours));
}

export function assumptionLabel(hoursPerWeek = HOURS_PER_WEEK): string {
  return `${hoursPerWeek} hours/week × ${WEEKS_PER_YEAR} weeks (${hoursPerYear(hoursPerWeek)} hours/year)`;
}
