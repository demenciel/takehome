import { bcadd, bccomp, bcmul, bcdiv, bcsub } from './bcmath';

export class Money {
  constructor(readonly cents: number) {
    if (!Number.isInteger(cents)) {
      throw new Error('Money cents must be an integer.');
    }
  }

  static zero(): Money {
    return new Money(0);
  }

  static ofCents(cents: number): Money {
    return new Money(cents);
  }

  static fromDollars(dollars: number | string): Money {
    if (typeof dollars === 'number') {
      if (Number.isInteger(dollars)) {
        return new Money(dollars * 100);
      }

      return Money.fromDollars(dollars.toFixed(2));
    }

    const normalized = dollars.replace(/[, $]/g, '').trim();

    if (normalized === '' || Number.isNaN(Number(normalized))) {
      throw new Error('Invalid dollar amount.');
    }

    const negative = normalized.startsWith('-');
    const unsigned = normalized.replace(/^[+-]/, '');

    if (!unsigned.includes('.')) {
      const cents = Number.parseInt(unsigned, 10) * 100;
      return new Money(negative ? -cents : cents);
    }

    const [whole = '0', fractionRaw = '0'] = unsigned.split('.', 2);
    const fraction = fractionRaw.slice(0, 2).padEnd(2, '0');
    const cents = Number.parseInt(whole, 10) * 100 + Number.parseInt(fraction, 10);

    return new Money(negative ? -cents : cents);
  }

  static fromRateProduct(cents: number, rate: string): Money {
    const product = bcmul(String(cents), rate, 8);

    return new Money(roundHalfUp(product));
  }

  add(other: Money): Money {
    return new Money(this.cents + other.cents);
  }

  subtract(other: Money): Money {
    return new Money(this.cents - other.cents);
  }

  multiply(rate: string): Money {
    return Money.fromRateProduct(this.cents, rate);
  }

  divideBy(divisor: number): Money {
    if (divisor === 0) {
      throw new Error('Cannot divide money by zero.');
    }

    const quotient = bcdiv(String(this.cents), String(divisor), 8);

    return new Money(roundHalfUp(quotient));
  }

  min(other: Money): Money {
    return this.cents <= other.cents ? this : other;
  }

  max(other: Money): Money {
    return this.cents >= other.cents ? this : other;
  }

  negated(): Money {
    return new Money(-this.cents);
  }

  abs(): Money {
    return new Money(Math.abs(this.cents));
  }

  isZero(): boolean {
    return this.cents === 0;
  }

  isNegative(): boolean {
    return this.cents < 0;
  }

  isPositive(): boolean {
    return this.cents > 0;
  }

  greaterThan(other: Money): boolean {
    return this.cents > other.cents;
  }

  greaterThanOrEqual(other: Money): boolean {
    return this.cents >= other.cents;
  }

  lessThan(other: Money): boolean {
    return this.cents < other.cents;
  }

  lessThanOrEqual(other: Money): boolean {
    return this.cents <= other.cents;
  }

  equals(other: Money): boolean {
    return this.cents === other.cents;
  }

  dollars(): string {
    const negative = this.cents < 0;
    const absolute = Math.abs(this.cents);
    const whole = Math.trunc(absolute / 100);
    const fraction = String(absolute % 100).padStart(2, '0');

    return `${negative ? '-' : ''}${whole}.${fraction}`;
  }

  format(): string {
    const negative = this.cents < 0;
    const absolute = Math.abs(this.cents);
    const whole = Math.trunc(absolute / 100).toLocaleString('en-CA');
    const fraction = String(absolute % 100).padStart(2, '0');

    return `${negative ? '-' : ''}$${whole}.${fraction}`;
  }

  toJSON(): string {
    return this.dollars();
  }

  toString(): string {
    return this.format();
  }
}

export function moneyFrom(value: Money | number | string | null | undefined): Money {
  if (value instanceof Money) {
    return value;
  }

  if (value === null || value === undefined || value === '') {
    return Money.zero();
  }

  if (typeof value === 'number' && Number.isInteger(value)) {
    return Money.fromDollars(value);
  }

  return Money.fromDollars(String(value));
}

export function formatPercent(numeratorCents: number, denominatorCents: number, decimals = 1): string {
  if (denominatorCents === 0) {
    return (0).toFixed(decimals);
  }

  return ((numeratorCents / denominatorCents) * 100).toFixed(decimals);
}

function roundHalfUp(value: string): number {
  if (bccomp(value, '0', 8) >= 0) {
    return Number.parseInt(bcadd(value, '0.5', 0), 10);
  }

  return Number.parseInt(bcsub(value, '0.5', 0), 10);
}
