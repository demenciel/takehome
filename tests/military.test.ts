import { describe, expect, it } from 'vitest';
import { Money } from '../src/lib/money';
import { provinceFromCode } from '../src/lib/province';
import { calculateMilitarySalary, cafPension, lookupMilitaryPay } from '../src/lib/military';
import { calculatePayroll } from '../src/lib/tax/payroll';

describe('military', () => {
  it('junior Regular Force NCM', () => {
    const pay = lookupMilitaryPay('regular', 'private', '1');
    expect(pay.rate.dollars()).toBe('4337.00');
    expect(pay.unit).toBe('monthly');
    expect(pay.rank_short).toBe('Private');
    const result = calculateMilitarySalary({
      component: 'regular',
      rank: 'private',
      increment: '1',
      province: provinceFromCode('ON')!,
      frequency: 'semimonthly',
    });
    expect(result.base_annual.dollars()).toBe('52044.00');
    expect(result.allowances.isZero()).toBe(true);
  });

  it('senior Regular Force NCM', () => {
    const result = calculateMilitarySalary({
      component: 'regular',
      rank: 'sergeant',
      increment: '4',
      province: provinceFromCode('AB')!,
      frequency: 'monthly',
    });
    expect(result.pay.rate.dollars()).toBe('8340.00');
    expect(result.base_annual.dollars()).toBe('100080.00');
  });

  it('officer ranks', () => {
    expect(lookupMilitaryPay('regular', 'captain', 'basic').rate.dollars()).toBe('8861.00');
    expect(lookupMilitaryPay('regular', 'captain', '5').rate.dollars()).toBe('10513.00');
  });

  it('Reserve daily', () => {
    const result = calculateMilitarySalary({
      component: 'reserve',
      rank: 'corporal',
      increment: 'basic',
      province: provinceFromCode('ON')!,
      frequency: 'monthly',
      reserveDays: 37,
    });
    expect(result.pay.unit).toBe('daily');
    expect(result.pay.rate.dollars()).toBe('209.28');
    expect(result.base_annual.dollars()).toBe('7743.36');
    expect(result.pension.included).toBe(false);
    expect(result.pension.amount.isZero()).toBe(true);
  });

  it('Regular Force take-home matches payroll engine', () => {
    for (const code of ['ON', 'QC', 'AB'] as const) {
      const result = calculateMilitarySalary({
        component: 'regular',
        rank: 'private',
        increment: '1',
        province: provinceFromCode(code)!,
        frequency: 'annual',
      });
      const payroll = calculatePayroll({
        annual_salary: '52044',
        province: code,
        frequency: 'annual',
        pension: result.pension.amount.dollars(),
      });
      expect(result.payroll.metrics.net_annual.cents).toBe(payroll.metrics.net_annual.cents);
      expect(result.payroll.metrics.federal_tax.isPositive()).toBe(true);
    }
  });

  it('CAF pension 2026', () => {
    expect(cafPension('regular', Money.fromDollars('52044')).amount.dollars()).toBe('4736.00');
    const high = cafPension('regular', Money.fromDollars('100000'));
    expect(high.amount.cents).toBe(
      Money.fromDollars('74600').multiply('0.0910').add(Money.fromDollars('25400').multiply('0.1169')).cents,
    );
  });

  it('unknown rank/increment', () => {
    expect(() => lookupMilitaryPay('regular', 'admiral', '1')).toThrow();
    expect(() => lookupMilitaryPay('regular', 'private', '9')).toThrow();
  });

  it('taxable allowances', () => {
    const result = calculateMilitarySalary({
      component: 'regular',
      rank: 'private',
      increment: '1',
      province: provinceFromCode('ON')!,
      frequency: 'annual',
      taxableAllowances: Money.fromDollars('5000'),
    });
    expect(result.gross_annual.dollars()).toBe('57044.00');
    expect(result.allowances.dollars()).toBe('5000.00');
    expect(result.pension.amount.dollars()).toBe('4736.00');
  });
});
