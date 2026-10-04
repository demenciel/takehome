export type PayFrequency = 'annual' | 'monthly' | 'semimonthly' | 'biweekly' | 'weekly';

export const PAY_FREQUENCIES: PayFrequency[] = ['annual', 'monthly', 'semimonthly', 'biweekly', 'weekly'];

const PERIODS: Record<PayFrequency, number> = {
  annual: 1,
  monthly: 12,
  semimonthly: 24,
  biweekly: 26,
  weekly: 52,
};

const LABELS: Record<PayFrequency, string> = {
  annual: 'Annual',
  monthly: 'Monthly',
  semimonthly: 'Semimonthly (24 pays)',
  biweekly: 'Biweekly',
  weekly: 'Weekly',
};

const ADVERBS: Record<PayFrequency, string> = {
  annual: 'annually',
  monthly: 'monthly',
  semimonthly: 'semimonthly',
  biweekly: 'biweekly',
  weekly: 'weekly',
};

export function frequencyPeriods(frequency: PayFrequency): number {
  return PERIODS[frequency];
}

export function frequencyLabel(frequency: PayFrequency): string {
  return LABELS[frequency];
}

export function frequencyAdverb(frequency: PayFrequency): string {
  return ADVERBS[frequency];
}

export function parseFrequency(value: string | undefined | null): PayFrequency | null {
  if (!value) {
    return null;
  }

  return PAY_FREQUENCIES.includes(value as PayFrequency) ? (value as PayFrequency) : null;
}
