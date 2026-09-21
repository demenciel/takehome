import { describe, expect, it } from 'vitest';
import { allProvinces, provinceFromCode } from '../src/lib/province';
import { Money } from '../src/lib/money';
import { estimateOvertime, overtimeGross, overtimeRules, splitWeeklyHours } from '../src/lib/overtime';
import { comparePayroll } from '../src/lib/tax/comparison';

describe('overtime', () => {
  it('Ontario standard overtime at 1.5×', () => {
    const result = overtimeGross(provinceFromCode('ON')!, Money.fromDollars('30'), 40, 8);
    expect(result.regular_pay.dollars()).toBe('1200.00');
    expect(result.overtime_rate.dollars()).toBe('45.00');
    expect(result.overtime_pay.dollars()).toBe('360.00');
    expect(result.total_gross.dollars()).toBe('1560.00');
    expect(result.rules.weekly_threshold).toBe(44);
    expect(result.rules.daily_threshold).toBeNull();
  });

  it('Alberta 8/44 rules', () => {
    const result = overtimeGross(provinceFromCode('AB')!, Money.fromDollars('25'), 40, 6);
    expect(result.rules.weekly_threshold).toBe(44);
    expect(result.rules.daily_threshold).toBe(8);
    expect(result.overtime_rate.dollars()).toBe('37.50');
    expect(result.overtime_pay.dollars()).toBe('225.00');
  });

  it('BC daily 1.5× and double 2×', () => {
    const result = overtimeGross(provinceFromCode('BC')!, Money.fromDollars('20'), 40, 4, 2);
    expect(result.rules.weekly_threshold).toBe(40);
    expect(result.rules.daily_threshold).toBe(8);
    expect(result.rules.daily_double_threshold).toBe(12);
    expect(result.overtime_rate.dollars()).toBe('30.00');
    expect(result.double_rate?.dollars()).toBe('40.00');
    expect(result.overtime_pay.dollars()).toBe('120.00');
    expect(result.double_pay.dollars()).toBe('80.00');
    expect(result.total_gross.dollars()).toBe('1000.00');
  });

  it('Quebec 40-hour week', () => {
    const result = overtimeGross(provinceFromCode('QC')!, Money.fromDollars('30'), 40, 5);
    expect(result.rules.weekly_threshold).toBe(40);
    expect(result.rules.daily_threshold).toBeNull();
    expect(result.overtime_rate.dollars()).toBe('45.00');
    expect(result.overtime_pay.dollars()).toBe('225.00');
  });

  it('Manitoba rules metadata', () => {
    const rules = overtimeRules(provinceFromCode('MB')!);
    expect(rules.weekly_threshold).toBe(40);
    expect(rules.daily_threshold).toBe(8);
    expect(rules.multiplier).toBe('1.5');
  });

  it('zero OT hours', () => {
    const result = overtimeGross(provinceFromCode('ON')!, Money.fromDollars('30'), 40, 0);
    expect(result.overtime_pay.isZero()).toBe(true);
    expect(result.total_gross.dollars()).toBe('1200.00');
  });

  it('splitWeeklyHours', () => {
    expect(splitWeeklyHours(provinceFromCode('ON')!, 44)).toEqual({ regular_hours: 44, overtime_hours: 0 });
    expect(splitWeeklyHours(provinceFromCode('ON')!, 52)).toEqual({ regular_hours: 44, overtime_hours: 8 });
  });

  it('NB statutory vs contract premium', () => {
    const statutory = overtimeGross(provinceFromCode('NB')!, Money.fromDollars('30'), 40, 8, 0, false);
    const contract = overtimeGross(provinceFromCode('NB')!, Money.fromDollars('30'), 40, 8, 0, true);
    expect(statutory.overtime_rate.dollars()).toBe('30.00');
    expect(contract.overtime_rate.dollars()).toBe('45.00');
  });

  it('after-tax overtime matches payroll comparison', () => {
    const on = provinceFromCode('ON')!;
    const estimate = estimateOvertime(on, Money.fromDollars('30'), 40, 8, 'weekly');
    const comparison = comparePayroll(on, Money.fromDollars('62400'), Money.fromDollars('81120'), 'weekly');
    expect(estimate.regular_pay.dollars()).toBe('1200.00');
    expect(estimate.overtime_pay.dollars()).toBe('360.00');
    expect(estimate.after_tax_overtime.cents).toBe(
      comparison.modified.metrics.net_annual.divideBy(52).subtract(comparison.baseline.metrics.net_annual.divideBy(52))
        .cents,
    );
  });

  it('rejects negative overtime', () => {
    expect(() => overtimeGross(provinceFromCode('ON')!, Money.fromDollars('30'), 40, -1)).toThrow();
  });

  it('has official sources for every jurisdiction', () => {
    for (const province of allProvinces()) {
      const rules = overtimeRules(province);
      expect(rules.source.url.startsWith('https://')).toBe(true);
      expect(Number(rules.weekly_threshold)).toBeGreaterThan(0);
      expect(rules.multiplier).toBeTruthy();
    }
  });
});
