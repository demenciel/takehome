import overtimeData from '../data/employment/overtime.json';
import { Money } from './money';
import { frequencyPeriods, type PayFrequency } from './frequency';
import type { Province } from './province';
import { comparePayroll } from './tax/comparison';

export function overtimeRules(province: Province) {
  const rules = overtimeData.jurisdictions[province.code as keyof typeof overtimeData.jurisdictions];

  if (!rules) {
    throw new Error(`No overtime rules for ${province.code}.`);
  }

  return rules;
}

function hoursPay(rate: Money, hours: number): Money {
  if (hours <= 0) {
    return Money.zero();
  }

  return rate.multiply(hours.toFixed(4));
}

function overtimeRate(hourlyWage: Money, rules: ReturnType<typeof overtimeRules>, useContractPremium: boolean): Money {
  const premium = hourlyWage.multiply(String(rules.multiplier));

  if (useContractPremium || (rules.rate_basis ?? 'regular') !== 'greater_of_regular_or_min_ot') {
    return premium;
  }

  const statutoryFloor = Money.fromDollars(String(rules.minimum_wage ?? '0')).multiply(String(rules.multiplier));

  return hourlyWage.greaterThan(statutoryFloor) ? hourlyWage : statutoryFloor;
}

export function overtimeGross(
  province: Province,
  hourlyWage: Money,
  regularHours: number,
  overtimeHours: number,
  doubleHours = 0,
  useContractPremium = false,
) {
  if (hourlyWage.isNegative() || regularHours < 0 || overtimeHours < 0 || doubleHours < 0) {
    throw new Error('Hours and wage cannot be negative.');
  }

  const rules = overtimeRules(province);
  const otRate = overtimeRate(hourlyWage, rules, useContractPremium);
  const doubleRate = rules.double_multiplier ? hourlyWage.multiply(String(rules.double_multiplier)) : null;
  const regularPay = hoursPay(hourlyWage, regularHours);
  const overtimePay = hoursPay(otRate, overtimeHours);
  const doublePay = doubleRate ? hoursPay(doubleRate, doubleHours) : Money.zero();

  return {
    rules,
    overtime_rate: otRate,
    double_rate: doubleRate,
    regular_pay: regularPay,
    overtime_pay: overtimePay,
    double_pay: doublePay,
    total_gross: regularPay.add(overtimePay).add(doublePay),
    regular_hours: regularHours,
    overtime_hours: overtimeHours,
    double_hours: doubleHours,
  };
}

export function splitWeeklyHours(province: Province, totalHours: number) {
  if (totalHours < 0) {
    throw new Error('Hours cannot be negative.');
  }

  const threshold = Number(overtimeRules(province).weekly_threshold);
  const regular = Math.min(totalHours, threshold);
  const overtime = Math.max(0, totalHours - threshold);

  return { regular_hours: regular, overtime_hours: overtime };
}

export function estimateOvertime(
  province: Province,
  hourlyWage: Money,
  regularHours: number,
  overtimeHours: number,
  frequency: PayFrequency,
  doubleHours = 0,
  useContractPremium = false,
) {
  const gross = overtimeGross(province, hourlyWage, regularHours, overtimeHours, doubleHours, useContractPremium);
  const periods = frequencyPeriods(frequency);
  const annualWithout = gross.regular_pay.multiply(String(periods));
  const annualWith = gross.total_gross.multiply(String(periods));
  const comparison = comparePayroll(province, annualWithout, annualWith, frequency);
  const periodNetWith = comparison.modified.metrics.net_annual.divideBy(periods);
  const periodNetWithout = comparison.baseline.metrics.net_annual.divideBy(periods);

  return {
    rules: gross.rules,
    retrieved_date: overtimeData.retrieved_date,
    disclaimer: overtimeData.disclaimer,
    frequency,
    periods,
    hourly_wage: hourlyWage,
    overtime_rate: gross.overtime_rate,
    double_rate: gross.double_rate,
    regular_hours: regularHours,
    overtime_hours: overtimeHours,
    double_hours: doubleHours,
    regular_pay: gross.regular_pay,
    overtime_pay: gross.overtime_pay.add(gross.double_pay),
    double_pay: gross.double_pay,
    total_gross: gross.total_gross,
    period_deductions: gross.total_gross.subtract(periodNetWith),
    period_net: periodNetWith,
    after_tax_overtime: periodNetWith.subtract(periodNetWithout),
    annual_without: annualWithout,
    annual_with: annualWith,
    comparison,
  };
}
