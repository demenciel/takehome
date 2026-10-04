import { Money } from '../money';
import { bcdiv } from '../bcmath';
import type { Province } from '../province';
import { applyBrackets } from './bracketMath';
import { federalBasicPersonalAmount } from './federal';
import type { Bracket, ProvincialTaxResult } from './types';

export function provincialBasicPersonalAmount(
  netIncome: Money,
  rules: Record<string, unknown>,
  federal: Record<string, unknown>,
): Money {
  const formula = String(rules.bpa_formula ?? 'fixed');

  if (formula === 'yukon') {
    return federalBasicPersonalAmount(netIncome, federal);
  }

  if (formula === 'manitoba') {
    const max = Money.fromDollars(String(rules.basic_personal_amount));
    const start = Money.fromDollars(String(rules.bpa_phase_out_start));
    const end = Money.fromDollars(String(rules.bpa_phase_out_end));

    if (netIncome.lessThanOrEqual(start)) {
      return max;
    }

    if (netIncome.greaterThanOrEqual(end)) {
      return Money.zero();
    }

    const over = netIncome.subtract(start);
    const span = end.subtract(start);
    const ratio = bcdiv(String(max.cents), String(span.cents), 12);
    const amount = max.subtract(Money.fromRateProduct(over.cents, ratio));

    return amount.isNegative() ? Money.zero() : amount;
  }

  return Money.fromDollars(String(rules.basic_personal_amount));
}

export function annualProvincialTax(
  taxableIncome: Money,
  employmentIncome: Money,
  basePension: Money,
  employmentInsurance: Money,
  qpip: Money,
  province: Province,
  provinceRules: Record<string, unknown>,
  federal: Record<string, unknown>,
): ProvincialTaxResult {
  if (province.usesQpp) {
    return quebecTax(taxableIncome, basePension, employmentInsurance, qpip, provinceRules);
  }

  const lowest = String(provinceRules.lowest_rate);
  const bpa = provincialBasicPersonalAmount(employmentIncome, provinceRules, federal);
  const k1p = bpa.multiply(lowest);
  const k2p = basePension.add(employmentInsurance).multiply(lowest);
  const bracket = applyBrackets(taxableIncome, provinceRules.brackets as Bracket[]);
  let t4 = bracket.tax.subtract(k1p).subtract(k2p);

  if (province.code === 'YT') {
    const cea = Money.fromDollars(String(provinceRules.canada_employment_amount ?? federal.canada_employment_amount));
    t4 = t4.subtract(employmentIncome.min(cea).multiply(lowest));
  }

  if (province.code === 'AB') {
    const threshold = Money.fromDollars(String(provinceRules.alberta_tax_credit_threshold));
    const excess = k1p.add(k2p).subtract(threshold);
    const k5p = excess.isNegative()
      ? Money.zero()
      : excess.multiply(String(provinceRules.alberta_tax_credit_rate));
    t4 = t4.subtract(k5p);
  }

  if (t4.isNegative()) {
    t4 = Money.zero();
  }

  const surtax = ontarioSurtax(t4, provinceRules);
  const health = ontarioHealthPremium(taxableIncome, provinceRules);
  const reduction = taxReduction(taxableIncome, t4, surtax, province, provinceRules);
  let t2 = t4.add(surtax).add(health).subtract(reduction);

  if (t2.isNegative()) {
    t2 = Money.zero();
  }

  return { t2, t4, k1p, k2p, surtax, health_premium: health, reduction, bpa };
}

function quebecTax(
  taxableIncome: Money,
  basePension: Money,
  employmentInsurance: Money,
  qpip: Money,
  rules: Record<string, unknown>,
): ProvincialTaxResult {
  const lowest = String(rules.lowest_rate);
  const bpa = Money.fromDollars(String(rules.basic_personal_amount));
  const k1p = bpa.multiply(lowest);
  const k2p = basePension.add(employmentInsurance).add(qpip).multiply(lowest);
  const bracket = applyBrackets(taxableIncome, rules.brackets as Bracket[]);
  let t4 = bracket.tax.subtract(k1p).subtract(k2p);

  if (t4.isNegative()) {
    t4 = Money.zero();
  }

  return {
    t2: t4,
    t4,
    k1p,
    k2p,
    surtax: Money.zero(),
    health_premium: Money.zero(),
    reduction: Money.zero(),
    bpa,
  };
}

function ontarioSurtax(t4: Money, rules: Record<string, unknown>): Money {
  if (!rules.surtax) {
    return Money.zero();
  }

  let total = Money.zero();

  for (const band of rules.surtax as Array<{ threshold: string; rate: string }>) {
    const threshold = Money.fromDollars(band.threshold);

    if (t4.greaterThan(threshold)) {
      total = total.add(t4.subtract(threshold).multiply(band.rate));
    }
  }

  return total;
}

function ontarioHealthPremium(taxableIncome: Money, rules: Record<string, unknown>): Money {
  if (!rules.health_premium) {
    return Money.zero();
  }

  for (const band of rules.health_premium as Array<Record<string, string | null>>) {
    const upTo = band.up_to === null ? null : Money.fromDollars(String(band.up_to));

    if (upTo && taxableIncome.greaterThan(upTo)) {
      continue;
    }

    const cap = Money.fromDollars(String(band.cap));

    if (!band.rate) {
      return cap;
    }

    const from = Money.fromDollars(String(band.from));
    const base = Money.fromDollars(String(band.base));
    const calculated = base.add(taxableIncome.subtract(from).multiply(String(band.rate)));

    return calculated.min(cap);
  }

  return Money.zero();
}

function taxReduction(
  taxableIncome: Money,
  t4: Money,
  surtax: Money,
  province: Province,
  rules: Record<string, unknown>,
): Money {
  if (!rules.tax_reduction) {
    return Money.zero();
  }

  const reduction = rules.tax_reduction as Record<string, string>;

  if (province.code === 'ON') {
    const basic = Money.fromDollars(reduction.basic);
    const combined = t4.add(surtax);
    const amount = basic.multiply('2').subtract(combined);

    if (amount.isNegative()) {
      return Money.zero();
    }

    return amount.min(combined);
  }

  if (province.code === 'BC') {
    const basic = Money.fromDollars(reduction.basic);
    const fullUntil = Money.fromDollars(reduction.full_threshold);
    const phaseOutEnd = Money.fromDollars(reduction.phase_out_end);

    if (taxableIncome.lessThanOrEqual(fullUntil)) {
      return t4.min(basic);
    }

    if (taxableIncome.greaterThan(phaseOutEnd)) {
      return Money.zero();
    }

    const phased = basic.subtract(taxableIncome.subtract(fullUntil).multiply(reduction.phase_out_rate));

    if (phased.isNegative()) {
      return Money.zero();
    }

    return t4.min(phased);
  }

  return Money.zero();
}
