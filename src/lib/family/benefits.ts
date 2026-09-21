import benefits from '../../data/benefits.json';
import { Money } from '../money';
import { formatDateLabel } from '../tax/rules';

export type EiProgram = 'maternity' | 'standard_parental' | 'extended_parental';
export type LeaveType = 'maternity_standard' | 'maternity_extended' | 'standard' | 'extended';

export function benefitsYear(): number {
  return Number(benefits.year);
}

export function benefitsLastReviewedLabel(): string {
  return formatDateLabel(benefits.last_reviewed);
}

export function eiRules(year = benefitsYear()) {
  const rules = (benefits.years as Record<string, { ei: Record<string, unknown> }>)[String(year)]?.ei;

  if (!rules) {
    throw new Error(`No EI parental-benefit rules for ${year}.`);
  }

  return rules;
}

export function eiProgram(key: EiProgram) {
  const program = eiRules()[key] as Record<string, unknown> | undefined;

  if (!program) {
    throw new Error(`Unknown EI parental program [${key}].`);
  }

  return program;
}

export function maximumInsurableEarnings(): Money {
  return Money.fromDollars(String(eiRules().maximum_insurable_earnings));
}

export function weeklyInsurableEarnings(annualSalary: Money): Money {
  return annualSalary.divideBy(52).min(maximumInsurableEarnings().divideBy(52));
}

export function weeklyBenefit(annualSalary: Money, program: EiProgram): Money {
  const rules = eiProgram(program);
  const insurable = weeklyInsurableEarnings(annualSalary);
  const maximum = Money.fromDollars(String(rules.max_weekly));

  if (insurable.equals(maximumInsurableEarnings().divideBy(52))) {
    return maximum;
  }

  return insurable.multiply(String(rules.rate)).min(maximum);
}

export function estimateBenefits(
  annualSalary: Money,
  program: EiProgram,
  weeks: number,
  sharedWeeks: number | null = null,
  otherParentWeeks: number | null = null,
) {
  if (weeks < 0) {
    throw new Error('Weeks cannot be negative.');
  }

  const rules = eiProgram(program);
  const weekly = weeklyBenefit(annualSalary, program);
  const maxWeekly = Money.fromDollars(String(rules.max_weekly));
  const atInsurableMax = weeklyInsurableEarnings(annualSalary).equals(maximumInsurableEarnings().divideBy(52));
  const individualMax = Number(rules.individual_max_weeks ?? rules.max_weeks);
  const sharedMax = Number(rules.shared_max_weeks ?? individualMax);
  const warnings: string[] = [];
  let used = weeks;

  if (used > individualMax) {
    warnings.push(
      `One parent can usually receive at most ${individualMax} weeks of this benefit. The estimate uses ${individualMax} weeks.`,
    );
    used = individualMax;
  }

  if (otherParentWeeks !== null) {
    const combined = used + Math.max(0, otherParentWeeks);

    if (combined > sharedMax) {
      warnings.push(
        `Parents can usually share at most ${sharedMax} weeks. Combined weeks were reduced for this estimate.`,
      );
      used = Math.max(0, sharedMax - Math.max(0, otherParentWeeks));
    }
  } else if (sharedWeeks !== null && sharedWeeks > sharedMax) {
    warnings.push(`Parents can usually share at most ${sharedMax} weeks.`);
  }

  return {
    program,
    rate: String(rules.rate),
    rate_percent: (Number(rules.rate) * 100).toFixed(0),
    weekly_insurable: weeklyInsurableEarnings(annualSalary),
    weekly_benefit: weekly,
    max_weekly: maxWeekly,
    capped: atInsurableMax || weekly.equals(maxWeekly),
    monthly_equivalent: weekly.multiply('52').divideBy(12),
    weeks: used,
    requested_weeks: weeks,
    max_weeks: individualMax,
    shared_max_weeks: sharedMax,
    total: weekly.multiply(String(used)),
    warnings,
  };
}

export function splitLeave(leaveType: LeaveType, plannedWeeks: number, partnerWeeks = 0) {
  const includesMaternity = leaveType === 'maternity_standard' || leaveType === 'maternity_extended';
  const parentalProgram: EiProgram = leaveType.includes('extended') ? 'extended_parental' : 'standard_parental';
  const maternityMax = Number(eiProgram('maternity').max_weeks);
  const parental = eiProgram(parentalProgram);
  const individualMax = Number(parental.individual_max_weeks);
  const sharedMax = Number(parental.shared_max_weeks);
  const warnings: string[] = [];
  let remaining = Math.max(0, plannedWeeks);
  let maternityWeeks = 0;

  if (includesMaternity) {
    maternityWeeks = Math.min(maternityMax, remaining);
    remaining -= maternityWeeks;
  }

  let parentalWeeks = remaining;

  if (parentalWeeks > individualMax) {
    warnings.push(
      `One parent can usually receive at most ${individualMax} parental weeks. Extra weeks are not counted in the EI total.`,
    );
    parentalWeeks = individualMax;
  }

  if (partnerWeeks > 0 && parentalWeeks + partnerWeeks > sharedMax) {
    warnings.push(
      `Parents can usually share at most ${sharedMax} parental weeks. Combined parental weeks were reduced for this estimate.`,
    );
    parentalWeeks = Math.max(0, sharedMax - partnerWeeks);
  }

  if (plannedWeeks < 1) {
    warnings.push('Enter at least one planned leave week to estimate benefits.');
  }

  return {
    maternity_weeks: maternityWeeks,
    parental_weeks: parentalWeeks,
    parental_program: parentalProgram,
    leave_type: leaveType,
    warnings,
  };
}

export function eiOnly(
  annualOrWeekly: Money,
  program: EiProgram,
  weeks: number,
  fromWeekly: boolean,
  sharing = false,
  parentA = 0,
  parentB = 0,
) {
  const annual = fromWeekly ? annualOrWeekly.multiply('52') : annualOrWeekly;
  const other = sharing ? (program === 'maternity' ? 0 : parentB) : null;
  const usedWeeks = sharing && program !== 'maternity' ? parentA : weeks;

  return estimateBenefits(annual, program, usedWeeks, null, other);
}
