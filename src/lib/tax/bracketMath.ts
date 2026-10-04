import { Money } from '../money';
import type { Bracket } from './types';

export function applyBrackets(income: Money, brackets: Bracket[]) {
  let selected = brackets[0];

  for (const bracket of brackets) {
    const threshold = Money.fromDollars(bracket.threshold);

    if (income.greaterThan(threshold) || income.equals(threshold)) {
      selected = bracket;
    }
  }

  let tax = income.multiply(selected.rate).subtract(Money.fromDollars(selected.constant));

  if (tax.isNegative()) {
    tax = Money.zero();
  }

  return {
    tax,
    rate: selected.rate,
    constant: Money.fromDollars(selected.constant),
    threshold: selected.threshold,
  };
}
