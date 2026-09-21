import { Money } from '../money';
import type { Province } from '../province';

export function annualEi(insurableEarnings: Money, province: Province, rules: Record<string, unknown>): Money {
  const rate = province.usesQpp ? String(rules.quebec_employee_rate) : String(rules.employee_rate);
  const maximum = Money.fromDollars(
    String(province.usesQpp ? rules.quebec_maximum_employee : rules.maximum_employee),
  );
  const cap = Money.fromDollars(String(rules.maximum_insurable_earnings));

  return insurableEarnings.min(cap).multiply(rate).min(maximum);
}

export function annualQpip(insurableEarnings: Money, province: Province, rules: Record<string, unknown>): Money {
  if (!province.usesQpp) {
    return Money.zero();
  }

  const cap = Money.fromDollars(String(rules.maximum_insurable_earnings));
  const maximum = Money.fromDollars(String(rules.maximum_employee));

  return insurableEarnings.min(cap).multiply(String(rules.employee_rate)).min(maximum);
}
