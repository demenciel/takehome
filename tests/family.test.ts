import { describe, expect, it } from 'vitest';
import { Money } from '../src/lib/money';
import { estimateBenefits, splitLeave, weeklyBenefit } from '../src/lib/family/benefits';
import { summarizeBaby } from '../src/lib/family/baby';
import { projectParentalLeave } from '../src/lib/family/parental';
import { salaryCompare, salaryNeighbors, salaryPageAllowed } from '../src/lib/catalog';
import { provinceFromCode } from '../src/lib/province';

describe('parental benefits', () => {
  it('weekly EI below max', () => {
    const result = estimateBenefits(Money.fromDollars('40000'), 'standard_parental', 20);
    expect(result.weekly_benefit.lessThan(Money.fromDollars('729'))).toBe(true);
    expect(result.capped).toBe(false);
    expect(result.weeks).toBe(20);
    expect(result.total.cents).toBe(result.weekly_benefit.multiply('20').cents);
  });

  it('standard max', () => {
    const result = estimateBenefits(Money.fromDollars('80000'), 'standard_parental', 20);
    expect(result.weekly_benefit.dollars()).toBe('729.00');
    expect(result.capped).toBe(true);
    expect(result.total.dollars()).toBe('14580.00');
  });

  it('extended max', () => {
    const result = estimateBenefits(Money.fromDollars('80000'), 'extended_parental', 20);
    expect(result.weekly_benefit.dollars()).toBe('437.00');
    expect(result.capped).toBe(true);
  });

  it('maternity cap', () => {
    const result = estimateBenefits(Money.fromDollars('80000'), 'maternity', 20);
    expect(result.weeks).toBe(15);
    expect(result.warnings.length).toBeGreaterThan(0);
    expect(result.total.dollars()).toBe('10935.00');
  });

  it('standard parental cap one parent', () => {
    const result = estimateBenefits(Money.fromDollars('80000'), 'standard_parental', 40);
    expect(result.weeks).toBe(35);
    expect(result.max_weeks).toBe(35);
    expect(result.shared_max_weeks).toBe(40);
  });

  it('extended parental cap', () => {
    const result = estimateBenefits(Money.fromDollars('80000'), 'extended_parental', 70);
    expect(result.weeks).toBe(61);
    expect(result.shared_max_weeks).toBe(69);
  });

  it('shared pool warning', () => {
    const result = estimateBenefits(Money.fromDollars('80000'), 'standard_parental', 25, null, 20);
    expect(result.weeks).toBe(20);
    expect(result.warnings.some((warning) => warning.includes('share at most 40'))).toBe(true);
  });

  it('split maternity+standard', () => {
    const split = splitLeave('maternity_standard', 50);
    expect(split.maternity_weeks).toBe(15);
    expect(split.parental_weeks).toBe(35);
    expect(split.parental_program).toBe('standard_parental');
  });

  it('split maternity+extended', () => {
    const split = splitLeave('maternity_extended', 76);
    expect(split.maternity_weeks).toBe(15);
    expect(split.parental_weeks).toBe(61);
    expect(split.parental_program).toBe('extended_parental');
  });
});

describe('baby budget', () => {
  it('planned startup', () => {
    const result = summarizeBaby(
      [
        { planned: '100' },
        { planned: '50' },
      ],
      [],
    );
    expect(result.planned_startup.dollars()).toBe('150.00');
    expect(result.remaining_purchases.dollars()).toBe('150.00');
  });

  it('purchased vs remaining', () => {
    const result = summarizeBaby(
      [
        { planned: '100', purchased: true, actual: '90' },
        { planned: '40' },
      ],
      [],
    );
    expect(result.already_spent.dollars()).toBe('90.00');
    expect(result.remaining_purchases.dollars()).toBe('40.00');
  });

  it('gifts', () => {
    const result = summarizeBaby(
      [
        { planned: '250', gift: true },
        { planned: '30' },
      ],
      [],
    );
    expect(result.gift_savings.dollars()).toBe('250.00');
    expect(result.remaining_purchases.dollars()).toBe('30.00');
  });

  it('second-hand', () => {
    const result = summarizeBaby([{ planned: '300', used: true, used_cost: '120' }], []);
    expect(result.used_savings.dollars()).toBe('180.00');
    expect(result.remaining_purchases.dollars()).toBe('120.00');
  });

  it('recurring annualized', () => {
    const result = summarizeBaby([], [{ monthly: '80' }, { monthly: '20' }]);
    expect(result.monthly_recurring.dollars()).toBe('100.00');
    expect(result.recurring_first_year.dollars()).toBe('1200.00');
  });

  it('childcare from month 7', () => {
    const result = summarizeBaby([], [], { needed: true, start_month: 7, monthly: '1200', subsidy: '200' });
    expect(result.childcare_months).toBe(6);
    expect(result.childcare_monthly.dollars()).toBe('1000.00');
    expect(result.childcare_first_year.dollars()).toBe('6000.00');
  });

  it('savings gap', () => {
    const result = summarizeBaby(
      [{ planned: '1000' }],
      [{ monthly: '100' }],
      {},
      { current_savings: '500', monthly_saved: '100', months_until: 4 },
    );
    expect(result.projected_savings.dollars()).toBe('900.00');
    expect(result.first_year_cost.dollars()).toBe('2200.00');
    expect(result.amount_left_to_prepare.isPositive()).toBe(true);
  });
});

describe('parental leave projection', () => {
  it('Ontario below EI cap', () => {
    const result = projectParentalLeave({
      province: 'ON',
      salary: '40000',
      frequency: 'biweekly',
      leave_type: 'standard',
      planned_weeks: 20,
    });
    expect(result.supported).toBe(true);
    expect(result.quebec).toBe(false);
    expect(result.weekly_ei!.lessThan(Money.fromDollars('729'))).toBe(true);
    expect(result.top_up.enabled).toBe(false);
    expect(result.leave_weeks).toBe(20);
  });

  it('top-up to percent', () => {
    const result = projectParentalLeave({
      province: 'ON',
      salary: '80000',
      frequency: 'biweekly',
      leave_type: 'maternity_standard',
      planned_weeks: 50,
      top_up_enabled: true,
      top_up_percent: '80',
      top_up_weeks: 17,
      top_up_mode: 'to_percent',
    });
    expect(result.top_up.enabled).toBe(true);
    expect(result.top_up.weekly.isPositive()).toBe(true);
    expect(result.leave_weekly.greaterThan(result.weekly_ei!)).toBe(true);
  });

  it('Québec no federal EI', () => {
    const result = projectParentalLeave({
      province: 'QC',
      salary: '80000',
      frequency: 'monthly',
      leave_type: 'maternity_standard',
      planned_weeks: 50,
    });
    expect(result.supported).toBe(false);
    expect(result.quebec).toBe(true);
    expect(result.qpip_url).toContain('rqap.gouv.qc.ca');
    expect('weekly_ei' in result).toBe(false);
  });

  it('standard vs extended comparison', () => {
    const result = projectParentalLeave({
      province: 'ON',
      salary: '80000',
      leave_type: 'maternity_standard',
      planned_weeks: 50,
    });
    expect(result.comparison.standard.weeks).toBe(50);
    expect(result.comparison.extended.weeks).toBeGreaterThan(50);
    expect(result.comparison.standard.weekly.greaterThan(result.comparison.extended.weekly)).toBe(true);
  });
});

describe('salary catalog', () => {
  it('priority provinces', () => {
    expect(salaryPageAllowed(provinceFromCode('ON')!, 80000)).toBe(true);
    expect(salaryPageAllowed(provinceFromCode('QC')!, 50000)).toBe(true);
    expect(salaryPageAllowed(provinceFromCode('YT')!, 80000)).toBe(false);
    expect(salaryPageAllowed(provinceFromCode('ON')!, 80123)).toBe(false);
  });

  it('neighbors / compare', () => {
    expect(salaryNeighbors(80000)).toEqual({ previous: 75000, next: 85000 });
    expect(salaryCompare(80000)).toEqual([70000, 75000, 80000, 85000, 90000, 100000]);
  });
});
