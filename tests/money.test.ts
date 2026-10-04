import { describe, expect, it } from 'vitest';
import { Money } from '../src/lib/money';

describe('Money', () => {
  it('stores cents from dollar strings and integers', () => {
    expect(Money.fromDollars('80000.00').cents).toBe(8_000_000);
    expect(Money.fromDollars(80000).cents).toBe(8_000_000);
  });

  it('formats CAD with separators', () => {
    expect(Money.fromDollars('75123.50').format()).toBe('$75,123.50');
    expect(Money.fromDollars('-12.04').format()).toBe('-$12.04');
  });

  it('uses CRA half-up rate products', () => {
    expect(Money.fromRateProduct(100, '0.163').cents).toBe(16);
    expect(Money.fromRateProduct(100, '0.165').cents).toBe(17);
  });

  it('adds without floating-point drift', () => {
    expect(Money.fromDollars('0.10').add(Money.fromDollars('0.20')).cents).toBe(30);
    expect(Money.fromDollars('0.10').add(Money.fromDollars('0.20')).format()).toBe('$0.30');
  });
});
