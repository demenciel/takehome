import { Money } from '../money';
import { bcdiv } from '../bcmath';
import type { Province } from '../province';
import { applyBrackets } from './bracketMath';
import type { Bracket, FederalTaxResult } from './types';

export function federalBasicPersonalAmount(netIncome: Money, federal: Record<string, unknown>): Money {
  const max = Money.fromDollars(String(federal.basic_personal_amount_max));
  const min = Money.fromDollars(String(federal.basic_personal_amount_min));
  const start = Money.fromDollars(String(federal.bpa_phase_out_start));
  const end = Money.fromDollars(String(federal.bpa_phase_out_end));

  if (netIncome.lessThanOrEqual(start)) {
    return max;
  }

  if (netIncome.greaterThanOrEqual(end)) {
    return min;
  }

  const additional = Money.fromDollars(String(federal.bpa_additional_amount));
  const span = end.subtract(start);
  const over = netIncome.subtract(start);
  const ratio = bcdiv(String(additional.cents), String(span.cents), 12);
  const reduction = Money.fromRateProduct(over.cents, ratio);
  const amount = max.subtract(reduction);

  return amount.lessThan(min) ? min : amount;
}

export function annualFederalTax(
  taxableIncome: Money,
  employmentIncome: Money,
  basePension: Money,
  employmentInsurance: Money,
  qpip: Money,
  province: Province,
  federal: Record<string, unknown>,
): FederalTaxResult {
  const lowest = String(federal.lowest_rate);
  const bpa = federalBasicPersonalAmount(employmentIncome, federal);
  const k1 = bpa.multiply(lowest);
  const k2 = basePension.add(employmentInsurance).add(qpip).multiply(lowest);
  const cea = Money.fromDollars(String(federal.canada_employment_amount)).min(employmentIncome);
  const k4 = cea.multiply(lowest);
  const bracket = applyBrackets(taxableIncome, federal.brackets as Bracket[]);
  let t3 = bracket.tax.subtract(k1).subtract(k2).subtract(k4);

  if (t3.isNegative()) {
    t3 = Money.zero();
  }

  let t1 = t3;

  if (province.usesQpp) {
    t1 = t3.subtract(t3.multiply(String(federal.quebec_abatement)));

    if (t1.isNegative()) {
      t1 = Money.zero();
    }
  }

  return {
    t1,
    t3,
    k1,
    k2,
    k4,
    bpa,
    bracket_rate: bracket.rate,
  };
}
