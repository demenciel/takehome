import { describe, expect, it } from 'vitest';
import { allProvinces, provinceFromCode } from '../src/lib/province';
import { calculatePayroll } from '../src/lib/tax/payroll';
import { annualPension } from '../src/lib/tax/cpp';
import { annualEi, annualQpip } from '../src/lib/tax/ei';
import { annualFederalTax, federalBasicPersonalAmount } from '../src/lib/tax/federal';
import { taxRulesForYear } from '../src/lib/tax/rules';
import { Money } from '../src/lib/money';
import { comparePayroll } from '../src/lib/tax/comparison';
import { annualFromHourly, assumptionLabel, hourlyFromAnnual } from '../src/lib/hourly';

const rules = taxRulesForYear(2026);

function paycheck(overrides: Parameters<typeof calculatePayroll>[0]) {
  return calculatePayroll({
    annual_salary: '80000',
    province: 'NB',
    frequency: 'annual',
    tax_year: 2026,
    ...overrides,
  });
}

describe('2026 CPP / QPP / EI / QPIP', () => {
  it('calculates 2026 CPP below the YMPE', () => {
    const result = annualPension(Money.fromDollars('50000'), rules.cpp);
    expect(result.contribution.dollars()).toBe('2766.75');
    expect(result.second_additional.isZero()).toBe(true);
    expect(result.base.dollars()).toBe('2301.75');
  });

  it('caps 2026 CPP at the published maximum', () => {
    expect(annualPension(Money.fromDollars('74600'), rules.cpp).contribution.dollars()).toBe('4230.45');
    expect(annualPension(Money.fromDollars('120000'), rules.cpp).contribution.dollars()).toBe('4230.45');
  });

  it('calculates 2026 CPP2 between YMPE and YAMPE', () => {
    expect(annualPension(Money.fromDollars('80000'), rules.cpp).second_additional.dollars()).toBe('216.00');
    expect(annualPension(Money.fromDollars('85000'), rules.cpp).second_additional.dollars()).toBe('416.00');
    expect(annualPension(Money.fromDollars('200000'), rules.cpp).second_additional.dollars()).toBe('416.00');
  });

  it('returns zero CPP on zero and sub-exemption income', () => {
    expect(annualPension(Money.zero(), rules.cpp).contribution.isZero()).toBe(true);
    expect(annualPension(Money.fromDollars('3000'), rules.cpp).contribution.isZero()).toBe(true);
  });

  it('calculates 2026 QPP at a higher employee rate than CPP', () => {
    const result = annualPension(Money.fromDollars('50000'), rules.qpp);
    expect(result.contribution.dollars()).toBe('2929.50');
    expect(result.base.dollars()).toBe('2464.50');
  });

  it('caps 2026 QPP at the published maximum', () => {
    expect(annualPension(Money.fromDollars('74600'), rules.qpp).contribution.dollars()).toBe('4479.30');
  });

  it('calculates 2026 EI and the Quebec reduced rate', () => {
    expect(annualEi(Money.fromDollars('50000'), provinceFromCode('NB')!, rules.ei).dollars()).toBe('815.00');
    expect(annualEi(Money.fromDollars('68900'), provinceFromCode('ON')!, rules.ei).dollars()).toBe('1123.07');
    expect(annualEi(Money.fromDollars('80000'), provinceFromCode('ON')!, rules.ei).dollars()).toBe('1123.07');
    expect(annualEi(Money.fromDollars('68900'), provinceFromCode('QC')!, rules.ei).dollars()).toBe('895.70');
    expect(annualEi(Money.zero(), provinceFromCode('ON')!, rules.ei).isZero()).toBe(true);
  });

  it('calculates 2026 QPIP only for Quebec', () => {
    expect(annualQpip(Money.fromDollars('75000'), provinceFromCode('QC')!, rules.qpip).dollars()).toBe('322.50');
    expect(annualQpip(Money.fromDollars('120000'), provinceFromCode('QC')!, rules.qpip).dollars()).toBe('442.90');
    expect(annualQpip(Money.fromDollars('75000'), provinceFromCode('ON')!, rules.qpip).isZero()).toBe(true);
  });
});

describe('federal tax 2026', () => {
  it('uses the maximum federal BPA below the phase-out', () => {
    expect(federalBasicPersonalAmount(Money.fromDollars('80000'), rules.federal).dollars()).toBe('16452.00');
  });

  it('uses the minimum federal BPA at and above the top threshold', () => {
    expect(federalBasicPersonalAmount(Money.fromDollars('258482.00'), rules.federal).dollars()).toBe('14829.00');
    expect(federalBasicPersonalAmount(Money.fromDollars('400000'), rules.federal).dollars()).toBe('14829.00');
  });

  it('phases out the federal BPA between the 29% and 33% thresholds', () => {
    const amount = federalBasicPersonalAmount(Money.fromDollars('219961.00'), rules.federal);
    expect(amount.cents).toBeGreaterThan(1_482_900);
    expect(amount.cents).toBeLessThan(1_645_200);
  });

  it('applies the 14% federal bracket on $50,000 NB taxable income', () => {
    const result = annualFederalTax(
      Money.fromDollars('49535'),
      Money.fromDollars('50000'),
      Money.fromDollars('2301.75'),
      Money.fromDollars('815'),
      Money.zero(),
      provinceFromCode('NB')!,
      rules.federal,
    );
    expect(result.bracket_rate).toBe('0.1400');
    expect(result.k1.dollars()).toBe('2303.28');
    expect(result.k4.dollars()).toBe('210.14');
    expect(result.t1.dollars()).toBe('3985.13');
  });

  it('applies the Quebec abatement to basic federal tax', () => {
    const outside = annualFederalTax(
      Money.fromDollars('50000'),
      Money.fromDollars('50000'),
      Money.fromDollars('2301.75'),
      Money.fromDollars('815'),
      Money.zero(),
      provinceFromCode('ON')!,
      rules.federal,
    );
    const quebec = annualFederalTax(
      Money.fromDollars('50000'),
      Money.fromDollars('50000'),
      Money.fromDollars('2301.75'),
      Money.fromDollars('815'),
      Money.zero(),
      provinceFromCode('QC')!,
      rules.federal,
    );
    expect(quebec.t3.equals(outside.t3)).toBe(true);
    expect(quebec.t1.cents).toBe(outside.t3.subtract(outside.t3.multiply('0.165')).cents);
    expect(quebec.t1.lessThan(outside.t1)).toBe(true);
  });
});

describe('payroll golden cases', () => {
  it('calculates a known $50,000 New Brunswick case from T4127 Option 1', () => {
    const result = paycheck({ annual_salary: '50000', province: 'NB' });
    expect(result.metrics.cpp.dollars()).toBe('2766.75');
    expect(result.metrics.ei.dollars()).toBe('815.00');
    expect(result.metrics.taxable_income.dollars()).toBe('49535.00');
    expect(result.metrics.federal_tax.dollars()).toBe('3985.13');
    expect(result.metrics.provincial_tax.dollars()).toBe('3078.90');
    expect(result.metrics.net_annual.dollars()).toBe('39354.22');
  });

  it('calculates a $50,000 Ontario salary below the CPP2 threshold', () => {
    const result = paycheck({ annual_salary: '50000', province: 'ON' });
    expect(result.metrics.cpp2.isZero()).toBe(true);
    expect(result.metrics.ei.dollars()).toBe('815.00');
    expect(result.metrics.cpp.dollars()).toBe('2766.75');
    expect(result.metrics.federal_tax.isPositive()).toBe(true);
    expect(result.metrics.provincial_tax.isPositive()).toBe(true);
  });

  it('calculates a known $80,000 Ontario case including health premium', () => {
    const result = paycheck({ annual_salary: '80000', province: 'ON' });
    expect(result.metrics.cpp.dollars()).toBe('4230.45');
    expect(result.metrics.cpp2.dollars()).toBe('216.00');
    expect(result.metrics.ei.dollars()).toBe('1123.07');
    expect(result.metrics.taxable_income.dollars()).toBe('79073.00');
    expect(result.metrics.federal_tax.dollars()).toBe('9242.60');
    expect(result.metrics.provincial_tax.dollars()).toBe('4884.79');
    expect(result.metrics.net_annual.dollars()).toBe('60303.09');
  });

  it('calculates a $100,000 Ontario salary at CPP, CPP2, and EI ceilings', () => {
    const result = paycheck({ annual_salary: '100000', province: 'ON' });
    expect(result.metrics.cpp.dollars()).toBe('4230.45');
    expect(result.metrics.cpp2.dollars()).toBe('416.00');
    expect(result.metrics.ei.dollars()).toBe('1123.07');
    expect(Number(result.metrics.effective_tax_rate)).toBeGreaterThan(15);
  });

  it('uses QPP, QPIP, reduced EI, and the federal abatement in Quebec', () => {
    const result = paycheck({ annual_salary: '75000', province: 'QC' });
    expect(result.metrics.cpp.dollars()).toBe('4479.30');
    expect(result.metrics.cpp2.dollars()).toBe('16.00');
    expect(result.metrics.ei.dollars()).toBe('895.70');
    expect(result.metrics.qpip.dollars()).toBe('322.50');
    expect(result.metrics.federal_tax.cents).toBeLessThan(821044);
    expect(result.metrics.provincial_tax.isPositive()).toBe(true);
  });

  it('returns zeros for zero income', () => {
    const result = paycheck({ annual_salary: '0', province: 'ON' });
    expect(result.metrics.net_annual.isZero()).toBe(true);
    expect(result.metrics.federal_tax.isZero()).toBe(true);
    expect(result.metrics.cpp.isZero()).toBe(true);
    expect(result.metrics.ei.isZero()).toBe(true);
  });

  it('keeps annual totals identical across pay frequencies', () => {
    const annual = paycheck({ frequency: 'annual' });
    const biweekly = paycheck({ frequency: 'biweekly' });
    const weekly = paycheck({ frequency: 'weekly' });
    expect(biweekly.metrics.net_annual.equals(annual.metrics.net_annual)).toBe(true);
    expect(weekly.metrics.net_annual.equals(annual.metrics.net_annual)).toBe(true);
    expect(biweekly.metrics.net_period.cents).toBe(annual.metrics.net_annual.divideBy(26).cents);
    expect(weekly.metrics.net_period.cents).toBe(annual.metrics.net_annual.divideBy(52).cents);
  });

  it('reduces taxable income for RRSP contributions', () => {
    const base = paycheck({ rrsp: '0' });
    const rrsp = paycheck({ rrsp: '5000' });
    expect(rrsp.metrics.taxable_income.cents).toBe(base.metrics.taxable_income.cents - 500000);
    expect(rrsp.metrics.federal_tax.lessThan(base.metrics.federal_tax)).toBe(true);
  });

  it('produces a result for every province and territory at $80,000', () => {
    for (const province of allProvinces()) {
      const result = paycheck({ province: province.code });
      expect(result.metrics.net_annual.isPositive()).toBe(true);
      expect(result.metrics.federal_tax.isPositive()).toBe(true);
    }
  });

  it('applies a British Columbia tax reduction on low income', () => {
    expect(paycheck({ annual_salary: '20000', province: 'BC' }).metrics.provincial_tax.isZero()).toBe(true);
  });

  it('applies Alberta tax credit and higher BPA relative to Ontario', () => {
    const ab = paycheck({ province: 'AB' });
    const on = paycheck({ province: 'ON' });
    expect(ab.metrics.provincial_tax.lessThan(on.metrics.provincial_tax)).toBe(true);
  });

  it('handles very high income without going negative', () => {
    const result = paycheck({ annual_salary: '1000000', province: 'ON' });
    expect(result.metrics.net_annual.isPositive()).toBe(true);
    expect(result.metrics.cpp.dollars()).toBe('4230.45');
    expect(result.metrics.cpp2.dollars()).toBe('416.00');
    expect(result.metrics.ei.dollars()).toBe('1123.07');
    expect(Number(result.metrics.effective_tax_rate)).toBeGreaterThan(30);
  });

  it('converts hourly wages into an annual salary', () => {
    const result = paycheck({
      input_mode: 'hourly',
      hourly_wage: '38.46',
      hours_per_week: 40,
      province: 'NB',
      frequency: 'biweekly',
    });
    expect(result.inputs.annual_salary.cents).toBe(7999680);
  });

  it('rejects an invalid province', () => {
    expect(() => paycheck({ province: 'XX', annual_salary: '50000' })).toThrow();
  });
});

describe('payroll comparison', () => {
  it('calculates a $50,000 salary plus a $5,000 bonus as the net difference', () => {
    const on = provinceFromCode('ON')!;
    const comparison = comparePayroll(on, Money.fromDollars('50000'), Money.fromDollars('55000'));
    const low = paycheck({ annual_salary: '50000', province: 'ON' });
    const high = paycheck({ annual_salary: '55000', province: 'ON' });
    expect(comparison.gross_delta.dollars()).toBe('5000.00');
    expect(comparison.net_annual_delta.cents).toBe(high.metrics.net_annual.subtract(low.metrics.net_annual).cents);
  });

  it('does not add CPP or EI on a bonus once ceilings are already maxed', () => {
    const comparison = comparePayroll(
      provinceFromCode('ON')!,
      Money.fromDollars('200000'),
      Money.fromDollars('210000'),
    );
    expect(comparison.cpp_delta.isZero()).toBe(true);
    expect(comparison.cpp2_delta.isZero()).toBe(true);
    expect(comparison.ei_delta.isZero()).toBe(true);
    expect(comparison.tax_delta.isPositive()).toBe(true);
  });

  it('returns a zero net bonus when the bonus is zero', () => {
    const comparison = comparePayroll(provinceFromCode('ON')!, Money.fromDollars('80000'), Money.fromDollars('80000'));
    expect(comparison.gross_delta.isZero()).toBe(true);
    expect(comparison.net_annual_delta.isZero()).toBe(true);
    expect(comparison.keep_percent).toBe('0.0');
  });

  it('increases CPP2 when a raise crosses the second additional threshold', () => {
    const comparison = comparePayroll(provinceFromCode('ON')!, Money.fromDollars('70000'), Money.fromDollars('80000'));
    expect(comparison.baseline.metrics.cpp2.isZero()).toBe(true);
    expect(comparison.modified.metrics.cpp2.isPositive()).toBe(true);
    expect(comparison.cpp2_delta.isPositive()).toBe(true);
  });
});

describe('hourly conversion', () => {
  it('converts hourly to annual', () => {
    expect(annualFromHourly(Money.fromDollars('40.00'), 40).dollars()).toBe('83200.00');
  });

  it('converts annual to hourly', () => {
    expect(hourlyFromAnnual(Money.fromDollars('80000'), 40).dollars()).toBe('38.46');
  });

  it('labels the 2080-hour assumption', () => {
    expect(assumptionLabel()).toContain('40 hours/week');
    expect(assumptionLabel()).toContain('2080');
  });
});
