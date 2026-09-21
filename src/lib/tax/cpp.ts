import { Money } from '../money';
import { bcdiv } from '../bcmath';
import type { PensionResult } from './types';

export function annualPension(pensionableEarnings: Money, rules: Record<string, unknown>): PensionResult {
  const exemption = Money.fromDollars(String(rules.basic_exemption));
  const ympe = Money.fromDollars(String(rules.ympe));
  const yampe = Money.fromDollars(String(rules.yampe));
  const rate = String(rules.employee_rate);
  const baseRate = String(rules.base_rate);
  const maximum = Money.fromDollars(String(rules.maximum_employee));
  const maximumBase = Money.fromDollars(String(rules.maximum_base_employee));
  const maximumSecond = Money.fromDollars(String(rules.maximum_second_additional));
  const secondRate = String(rules.second_additional_rate);

  const firstBand = pensionableEarnings.min(ympe).subtract(exemption);
  const contribution = firstBand.isNegative()
    ? Money.zero()
    : firstBand.multiply(rate).min(maximum);

  const secondBand = pensionableEarnings.min(yampe).subtract(ympe);
  const second = secondBand.isNegative()
    ? Money.zero()
    : secondBand.multiply(secondRate).min(maximumSecond);

  const base = contribution.multiply(bcdiv(baseRate, rate, 10)).min(maximumBase);
  const additional = contribution.subtract(base);

  return {
    contribution,
    second_additional: second,
    base,
    additional,
  };
}
