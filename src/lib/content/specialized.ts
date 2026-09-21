import babyBudget from '../../data/baby-budget.json';
import benefits from '../../data/benefits.json';
import { overtimeRules } from '../overtime';
import type { Province } from '../province';
import { CURRENT_TAX_YEAR } from '../tax/rules';

const year = CURRENT_TAX_YEAR;
const eiYear = Number(benefits.year);
const maternity = (benefits.years as Record<string, { ei: Record<string, { max_weekly: string; max_weeks: number; rate?: string; shared_max_weeks?: number; individual_max_weeks?: number; window_weeks?: number }> }>)[String(eiYear)].ei.maternity;
const standard = (benefits.years as Record<string, { ei: Record<string, { max_weekly: string; max_weeks?: number; shared_max_weeks?: number; individual_max_weeks?: number; window_weeks?: number }> }>)[String(eiYear)].ei.standard_parental;
const extended = (benefits.years as Record<string, { ei: Record<string, { max_weekly: string; shared_max_weeks?: number; individual_max_weeks?: number; window_weeks?: number }> }>)[String(eiYear)].ei.extended_parental;

export function overtimeNational() {
  return {
    h1: 'Canadian Overtime Pay Calculator',
    title: `Canadian Overtime Pay Calculator ${year} | Paycheque.app`,
    description:
      'Estimate overtime pay and how much you may keep after tax, CPP or QPP, and EI. Rules follow official provincial employment standards, not a single national overtime formula.',
    intro:
      'Overtime is not the same in every province. Enter your hourly wage and hours to estimate regular pay, overtime pay, and the after-tax value of that overtime using the same payroll engine as the paycheck calculator.',
    sections: [
      {
        heading: 'How overtime is calculated here',
        copy: 'Regular hours are paid at your hourly wage. Overtime hours use that jurisdiction’s standard premium — usually 1.5×, and 2× after 12 hours in a day in British Columbia. New Brunswick’s statutory floor is 1.5× the minimum wage, not automatically 1.5× your regular rate.',
      },
      {
        heading: 'How after-tax overtime is estimated',
        copy: 'The calculator assumes the same hours repeat every pay period for a full year, then compares take-home pay with and without the overtime. That difference is the estimated after-tax value. It is not overtime pay multiplied by a guessed tax rate.',
      },
      {
        heading: 'What this does not cover',
        copy: 'Managers, some professions, averaging agreements, collective agreements, and federally regulated workplaces can follow different rules. This tool models the standard employment-standards default, not every exemption.',
      },
    ],
    faqs: overtimeFaqs(),
  };
}

export function overtimeProvince(province: Province) {
  const rules = overtimeRules(province);
  const threshold = rules.weekly_threshold;
  const daily = rules.daily_threshold;
  const how = daily
    ? `Most employees in ${province.name} become entitled to overtime after ${daily} hours in a day or ${threshold} hours in a week, paid at ${rules.multiplier}× the regular wage.`
    : `Most employees in ${province.name} become entitled to overtime after the standard weekly threshold of ${threshold} hours, paid at ${rules.multiplier}×.`;

  return {
    h1: `${province.name} Overtime Pay Calculator`,
    title: `${province.name} Overtime Pay Calculator ${year} | Paycheque.app`,
    description: `Estimate ${province.name} overtime pay after the standard ${threshold}-hour weekly threshold, then see how much of that overtime may remain after payroll deductions.`,
    intro: `${how} Enter your hourly rate and hours worked to estimate gross overtime and what may remain after income tax, ${province.usesQpp ? 'QPP, EI, and QPIP' : 'CPP, and EI'}.`,
    rules,
    sections: [
      { heading: `Standard overtime in ${province.name}`, copy: rules.summary },
      {
        heading: 'After-tax overtime',
        copy: `The tax estimate annualizes the pay period you enter and runs the ${province.name} payroll calculation twice — with overtime and without it. The difference is how much of the overtime this calculator thinks you keep. Employer withholding on one cheque can still differ.`,
      },
      {
        heading: 'Exemptions and contracts',
        copy: `${rules.exceptions} Collective agreements and employment contracts can also set a different (usually more generous) overtime premium.`,
      },
    ],
    faqs: [
      {
        question: `When does overtime start in ${province.name}?`,
        answer: daily
          ? `The standard rule is after ${daily} hours in a day or ${threshold} hours in a week. Some jobs are exempt or use averaging.`
          : `The standard rule is after ${threshold} hours in a work week. Some jobs are exempt or use averaging agreements.`,
      },
      {
        question: `Is overtime always time and a half in ${province.name}?`,
        answer:
          rules.rate_basis === 'greater_of_regular_or_min_ot'
            ? 'The Employment Standards Act sets a minimum of 1.5× the minimum wage. Many contracts pay 1.5× the regular wage when that is higher. Use the optional checkbox if your contract pays the higher premium.'
            : rules.daily_double_threshold
              ? `Hours over ${rules.daily_threshold} in a day or ${threshold} in a week are usually 1.5×. Hours over ${rules.daily_double_threshold} in a day are 2×.`
              : `The standard premium is ${rules.multiplier}× the regular wage. Contracts can pay more.`,
      },
      ...overtimeFaqs(),
    ],
  };
}

function overtimeFaqs() {
  return [
    {
      question: 'How much of my overtime do I actually keep?',
      answer:
        'This calculator estimates that by comparing annual take-home pay with and without the overtime hours. The gap is the after-tax value, after income tax and payroll contributions.',
    },
    {
      question: 'Does every Canadian province use a 40-hour overtime week?',
      answer:
        'No. Ontario and New Brunswick generally use 44 hours. Nova Scotia and Prince Edward Island generally use 48. Several jurisdictions also have daily thresholds.',
    },
    {
      question: 'Is this legal advice?',
      answer:
        'No. Employment-standards rules have exemptions. Use the official source linked on the page, or an employment-standards office, for your situation.',
    },
  ];
}

export function bonusNational() {
  return {
    h1: 'Bonus Tax Calculator',
    title: `Bonus Tax Calculator Canada ${year} | Paycheque.app`,
    description:
      'Estimate how much of a Canadian employment bonus you keep after income tax, CPP or QPP, and EI. Compare salary alone with salary plus bonus.',
    intro:
      'A bonus is employment income. This calculator does not multiply the bonus by a flat tax rate. It runs the payroll engine on your salary, then on salary plus bonus, and shows the difference.',
    sections: [
      {
        heading: 'How bonuses are taxed',
        copy: 'A cash bonus is added to employment income for the year. It can increase federal and provincial income tax and, if you are still under the annual ceilings, CPP or QPP and EI (plus QPIP in Quebec).',
      },
      {
        heading: 'Withholding vs final tax',
        copy: 'Employers often withhold tax on a bonus using a payroll method that can look harsher than the annual result. This page estimates the incremental annual liability, not the exact amount that will come off one bonus cheque.',
      },
      {
        heading: 'Contribution ceilings',
        copy: 'Once you have already reached the CPP/QPP or EI maximum for the year, extra bonus income should not create more of those contributions. The existing payroll engine already applies those ceilings.',
      },
    ],
    faqs: bonusFaqs(),
  };
}

export function bonusProvince(province: Province) {
  const contributions = province.usesQpp
    ? 'federal tax, Quebec provincial tax, QPP, EI, and QPIP'
    : `federal tax, ${province.adjective} provincial or territorial tax, CPP, and EI`;

  return {
    h1: `${province.name} Bonus Tax Calculator`,
    title: `${province.name} Bonus Tax Calculator ${year} | Paycheque.app`,
    description: `Estimate how much of a bonus you keep in ${province.name} after ${contributions}. Uses the same ${year} payroll engine as the paycheck calculator.`,
    intro: `A bonus paid for work in ${province.name} is employment income. Enter your salary and bonus to see the estimated incremental tax and payroll contributions — not a flat bonus tax rate.`,
    sections: [
      {
        heading: `Bonuses in ${province.name}`,
        copy: province.usesQpp
          ? 'Quebec employees still pay federal tax (with the Quebec abatement), Revenu Québec provincial tax, QPP instead of CPP, a lower EI rate, and QPIP. A bonus can increase each of those until the contribution ceilings are reached.'
          : `In ${province.name}, a bonus is added to the same employment income used for federal tax, provincial or territorial tax, CPP, and EI. If you are already at the CPP or EI maximum, the extra deductions are mostly income tax.`,
      },
      {
        heading: 'What your employer may withhold',
        copy: 'Payroll software may treat a bonus as a lump-sum or extra-period payment. The amount withheld on the cheque can be higher or lower than this annual estimate. The year-end T4 / Relevé 1 is what matters for your actual tax.',
      },
    ],
    faqs: [
      {
        question: `Is a bonus taxed differently in ${province.name} than regular salary?`,
        answer: 'For annual tax, no — it is employment income. Withholding on the bonus payment itself can still look different from regular paycheques.',
      },
      ...bonusFaqs(),
    ],
  };
}

function bonusFaqs() {
  return [
    {
      question: 'How is the net bonus calculated?',
      answer: 'Net bonus is estimated take-home on salary plus bonus minus estimated take-home on salary alone.',
    },
    {
      question: 'Will I pay more CPP or EI on a bonus?',
      answer:
        'Only if you have not already reached the annual maximums. High earners who have already maxed CPP/QPP and EI should see mostly income tax on the extra amount. Quebec also uses QPIP.',
    },
    {
      question: 'Why doesn’t this match my bonus cheque?',
      answer:
        'Employers withhold using payroll formulas that can differ from the final annual tax on the combined income. This page estimates the annual incremental effect.',
    },
  ];
}

export function raiseNational() {
  return {
    h1: 'Raise Calculator',
    title: 'Raise Calculator Canada — How Much of Your Raise Do You Keep? | Paycheque.app',
    description:
      'See how much of a Canadian salary increase you keep after income tax, CPP or QPP, and EI. Compare take-home pay before and after the raise.',
    intro:
      'A raise increases gross pay, but not dollar-for-dollar in your bank account. This calculator runs the payroll engine on your current salary and your new salary, then shows the extra take-home by month, biweekly cheque, and week.',
    sections: [
      {
        heading: 'How the net raise is calculated',
        copy: 'Net raise is estimated annual take-home on the new salary minus take-home on the current salary. Extra monthly, biweekly, and weekly amounts divide that annual difference by 12, 26, and 52.',
      },
      {
        heading: 'Why you do not keep 100%',
        copy: 'The extra income can attract more federal and provincial tax. If you are still below the ceilings, CPP or QPP and EI (and QPIP in Quebec) can also rise. Crossing a bracket or the CPP2 threshold changes the share you keep.',
      },
    ],
    faqs: raiseFaqs(),
  };
}

export function raiseProvince(province: Province) {
  return {
    h1: `${province.name} Raise Calculator`,
    title: `${province.name} Raise Calculator ${year} — How Much Do You Keep? | Paycheque.app`,
    description: `Estimate how much of a salary increase you keep in ${province.name} after ${year} income tax and payroll contributions.`,
    intro: province.usesQpp
      ? 'In Quebec, a raise can change federal tax, Revenu Québec tax, QPP, EI, and QPIP. Compare your current and new salary to see the extra take-home.'
      : `In ${province.name}, a raise is taxed through the same federal and provincial or territorial payroll rules as your current salary. Compare both sides to see how much of the increase you keep.`,
    sections: [
      {
        heading: `What changes in ${province.name}`,
        copy: province.usesQpp
          ? 'Quebec uses QPP and QPIP instead of the rest-of-Canada CPP/EI pairing, plus a federal abatement. A raise that crosses the QPP or QPP2 range will not increase those contributions further once the annual maximums are reached.'
          : `Provincial brackets and credits in ${province.name} affect how much of the raise remains after tax. CPP and EI increase only until their annual maximums. The table on this page uses a worked ${province.name} example so you can see the before/after split.`,
      },
    ],
    faqs: [
      {
        question: `Does a raise push me into a higher tax bracket in ${province.name}?`,
        answer:
          'Only the dollars above a bracket threshold are taxed at the higher rate. This calculator uses the full payroll estimate, so bracket crossings are already in the before/after difference.',
      },
      ...raiseFaqs(),
    ],
  };
}

function raiseFaqs() {
  return [
    {
      question: 'How much of my raise will I actually keep?',
      answer:
        'The keep percentage is net raise divided by gross raise. It is based on two annual payroll estimates, not a single marginal rate applied to the raise.',
    },
    {
      question: 'Can I enter a raise as a percentage?',
      answer: 'Yes. Choose Raise % and the calculator converts it into a new annual salary before running the comparison.',
    },
  ];
}

export function militaryPage() {
  return {
    h1: 'Canadian Armed Forces Salary Calculator',
    title: `Canadian Armed Forces Salary Calculator ${year} | Paycheque.app`,
    description:
      'Estimate CAF take-home pay by component, rank, pay increment, and province. Uses official Regular Force and Reserve Force pay tables and the Paycheque.app payroll engine.',
    intro:
      'Estimate CAF take-home pay by component, rank, pay increment, and province. Base pay comes from official National Defence tables. Income tax, CPP or QPP, and EI are estimated with the same engine as the paycheck calculator.',
    sections: [
      {
        heading: 'How CAF pay works',
        copy: 'Regular Force members and reservists on Class C service are paid a monthly rate for their rank, pay level, and pay increment. Reserve Force Class A and Class B service is paid a published daily rate. This calculator uses those published base-pay tables. It does not invent occupation-specific or specialist rates.',
      },
      {
        heading: 'Regular Force vs Reserve Force pay',
        copy: 'Choose Regular Force for the official monthly scale (also used for Reserve Class C). Choose Reserve Force for Class A or Class B daily rates, then enter how many days you expect to be paid in a year. Daily rates are stored separately — they are not monthly pay divided by 30.',
      },
      {
        heading: 'Pay increments',
        copy: 'A pay increment is the step on the published scale for that rank. “Basic” is the starting rate on the official table. Higher numbered increments are later annual steps. Some officer and CWO ranks also have pay levels (A, B, C, and so on) for different entry plans or appointments.',
      },
      {
        heading: 'Taxes and payroll deductions',
        copy: `Federal and provincial or territorial income tax, CPP or QPP (including CPP2/QPP2), EI, and QPIP in Quebec use the same ${year} payroll formulas as the rest of this site. Regular Force pension is estimated from official Treasury Board rates for 2026. Reserve Force pension is not estimated.`,
      },
      {
        heading: 'What is not included',
        copy: 'Allowances and benefits are not included unless entered manually. That means PLD/CFHD, LDA, sea duty, aircrew, special operations, deployment, environmental allowances, Military Service Pay, and other CAF benefits are omitted. Specialist, Special Forces, Search and Rescue, pilot, legal, medical, and dental occupation tables are also omitted.',
      },
    ],
    faqs: [
      {
        question: 'Are these the current official CAF pay rates?',
        answer:
          'Yes for base pay. National Defence still publishes Regular Force monthly and Reserve Force daily tables as effective 1 April 2025. Those remain the official published rank scales as of this page’s retrieved date. April 2026 CAF compensation updates were mainly allowances, which this calculator does not estimate.',
      },
      {
        question: 'Does this include CAF pension?',
        answer:
          'For Regular Force only. The estimate uses the Treasury Board 2026 rates of 9.10% up to the YMPE and 11.69% above it. It does not model members who have reached 35 years of pensionable service. Reserve Force pension is not included.',
      },
      {
        question: 'Why is my actual CAF pay different?',
        answer:
          'Occupation group, specialist pay, allowances, posting, Class of reserve service, and your real pensionable service all change a pay statement. This page estimates standard-occupation base pay plus optional amounts you type in.',
      },
    ],
  };
}

export function parentalPage() {
  return {
    h1: 'Canadian Parental Leave Calculator',
    title: `Parental Leave Calculator Canada ${eiYear} | Paycheque.app`,
    description:
      'Estimate maternity and parental leave income in Canada, including EI weekly benefits, standard vs extended leave, employer top-up, and household cash-flow.',
    intro: `Estimate what your paycheque may look like during maternity or parental leave. The calculator uses ${eiYear} federal EI rates for provinces and territories outside Québec, then compares that leave income with your regular take-home pay.`,
    sections: [
      {
        heading: 'How parental leave income is estimated',
        copy: 'Federal EI maternity benefits pay 55% of average insurable weekly earnings for up to 15 weeks, and parental benefits pay either 55% (standard) or 33% (extended), each subject to a weekly maximum. This page does not treat salary as proof of eligibility. Service Canada still reviews insurable hours and your claim.',
      },
      {
        heading: 'Standard vs extended parental benefits',
        copy: 'Standard parental benefits pay more per week for a shorter period. Extended parental benefits pay less per week for longer. The financially better path depends on your expenses, top-up, and how long you want to be off work. This calculator does not pick a winner.',
      },
      {
        heading: 'Employer top-ups',
        copy: 'Some employers add a top-up so leave income reaches a share of regular salary, often for a set number of weeks. Plans differ. The default here is the common “top up to X%” interpretation. Enter your own percentage and duration, or leave top-up off.',
      },
      {
        heading: 'Québec uses QPIP, not federal EI',
        copy: 'Most Québec births and adoptions are paid through the Québec Parental Insurance Plan. This calculator does not apply federal EI rates to Québec or invent QPIP amounts. If you select Québec, you will see your regular take-home estimate and a link to the official RQAP service.',
      },
      {
        heading: 'Household cash-flow during leave',
        copy: 'If you enter a partner’s income and monthly expenses, the page estimates household income before and during leave and the amount your household may need to cover from savings. That is a planning figure, not financial advice.',
      },
    ],
    faqs: [
      {
        question: 'How much will I make on parental leave in Canada?',
        answer: `Federal EI maternity and standard parental benefits pay 55% of insurable weekly earnings, up to the published ${eiYear} weekly maximum. Extended parental benefits pay 33%. Québec uses QPIP instead.`,
      },
      {
        question: 'Is this an eligibility checker?',
        answer: 'No. You still need enough insurable hours and a Service Canada claim. This page estimates amounts if you qualify.',
      },
      {
        question: 'Does Québec use this calculator?',
        answer: 'The leave-income estimate is not produced for Québec. Regular take-home is still shown, with a link to RQAP.',
      },
    ],
  };
}

export function eiBenefitsPage() {
  return {
    h1: 'EI Maternity & Parental Benefits Calculator',
    title: `EI Maternity and Parental Benefits Calculator ${eiYear} | Paycheque.app`,
    description: `Estimate ${eiYear} EI maternity and parental benefits in Canada, including weekly maxima, standard vs extended rates, shared weeks, and total benefits.`,
    intro:
      'Use this page when you want the federal EI maternity or parental benefit estimate itself — weekly amount, rate, maximum, and total — without a full household budget.',
    sections: [
      {
        heading: `What is the maximum EI maternity benefit in ${eiYear}?`,
        copy: `The ${eiYear} maximum weekly EI maternity benefit is $${Number(maternity.max_weekly).toFixed(0)}. Maternity benefits pay 55% of average insurable weekly earnings for up to ${maternity.max_weeks} weeks. Most people receive less than the maximum if their insurable earnings are below the yearly ceiling.`,
      },
      {
        heading: `What is the maximum EI parental benefit in ${eiYear}?`,
        copy: `Standard parental benefits have the same ${eiYear} weekly maximum as maternity: $${Number(standard.max_weekly).toFixed(0)} at 55%. Extended parental benefits have a lower weekly maximum of $${Number(extended.max_weekly).toFixed(0)} at 33%.`,
      },
      {
        heading: 'What is the difference between standard and extended parental benefits?',
        copy: `Standard parental benefits pay 55% for up to ${standard.shared_max_weeks} weeks shared, with one parent usually limited to ${standard.individual_max_weeks} weeks, generally within ${standard.window_weeks} weeks of birth or adoption. Extended parental benefits pay 33% for up to ${extended.shared_max_weeks} weeks shared, with one parent usually limited to ${extended.individual_max_weeks} weeks, generally within ${extended.window_weeks} weeks.`,
      },
      {
        heading: 'Can both parents receive parental benefits?',
        copy: `Yes. Parental weeks are a shared pool. In ${eiYear}, standard parental weeks total ${standard.shared_max_weeks} and extended weeks total ${extended.shared_max_weeks}. One parent cannot usually take the entire pool.`,
      },
      {
        heading: 'Can parents take parental benefits at the same time?',
        copy: 'Yes. Parents can take parental benefits at the same time or at different times, as long as the shared week limits and claiming window are respected. Service Canada explains the current claiming rules.',
      },
      {
        heading: 'Can I switch from standard to extended later?',
        copy: 'Once you choose standard or extended parental benefits, that choice generally applies to the claim. Confirm the current rule with Service Canada before you apply. This calculator does not change a submitted claim.',
      },
      {
        heading: 'Is EI maternity/parental income taxable?',
        copy: 'Yes. EI benefits are taxable. Tax may be withheld from the benefit, and the amount still belongs on your tax return. Figures on this page are estimated benefits, not after-tax take-home.',
      },
      {
        heading: 'How does an employer top-up work?',
        copy: 'A top-up is employer-paid income on top of EI, often for a limited number of weeks. It is not part of the federal EI calculation. Use the parental leave calculator if you want to include a top-up in a household estimate.',
      },
      {
        heading: 'How does parental leave work in Québec?',
        copy: 'Québec administers maternity, paternity, parental, and adoption benefits through QPIP. Federal EI maternity and parental rules do not apply to most Québec claims. This page will not produce a federal EI estimate for Québec.',
      },
    ],
    faqs: [
      {
        question: `What is the maximum EI maternity benefit in ${eiYear}?`,
        answer: `The published weekly maximum is $${Number(maternity.max_weekly).toFixed(0)} for up to ${maternity.max_weeks} weeks at 55% of insurable weekly earnings.`,
      },
      {
        question: `What is the maximum EI parental benefit in ${eiYear}?`,
        answer: `Standard parental benefits max out at $${Number(standard.max_weekly).toFixed(0)} a week. Extended parental benefits max out at $${Number(extended.max_weekly).toFixed(0)} a week.`,
      },
    ],
  };
}

export function babyPage() {
  const ccb = babyBudget.ccb;

  return {
    h1: 'Canadian Baby Cost Calculator',
    title: `Baby Cost Calculator Canada ${year} | Paycheque.app`,
    description:
      'Plan Canadian baby startup costs, monthly expenses, childcare, and savings with editable defaults. See remaining purchases and the estimated amount left to prepare.',
    intro:
      'Baby costs vary widely. This calculator does not publish a single “a baby costs $X in Canada” figure. It starts from editable planning defaults so you can mark gifts, used items, skipped purchases, childcare, and savings.',
    sections: [
      {
        heading: 'Startup costs vs monthly costs',
        copy: 'Startup costs are one-time preparation purchases: sleep, feeding, diapering, clothing, transportation, health supplies, and nursery items. Monthly costs are recurring first-year expenses such as diapers, formula, and replacements. Edit or skip any line.',
      },
      {
        heading: 'Childcare',
        copy: 'There is no Canada-wide $10/day assumption here. Enter the monthly amount you actually expect and the month you expect care to start. If you know a subsidy or benefit, subtract it as a monthly amount.',
      },
      {
        heading: 'Canada Child Benefit',
        copy: `This page does not estimate CCB. For ${ccb.period_label}, the published maximum for a child under 6 is $${Number(ccb.under_6_annual_maximum).toLocaleString('en-CA')} a year, or $${ccb.under_6_monthly_maximum} a month. Actual payment depends on adjusted family net income and family circumstances. Use the CRA Child and Family Benefits Calculator if you are unsure.`,
      },
      {
        heading: 'Savings and readiness',
        copy: 'Enter savings already set aside, the amount you are saving each month, and months until arrival. If you already estimated a parental-leave income reduction, you can include that optional figure. The headline result is the estimated amount left to prepare — not a warning score.',
      },
    ],
    faqs: [
      {
        question: 'How much does a baby cost in Canada?',
        answer:
          'There is no single national figure. Costs depend on gifts, second-hand items, feeding method, childcare, and where you live. Use the editable categories on this page instead of a fixed total.',
      },
      {
        question: 'How much should I save before having a baby?',
        answer:
          'A useful planning approach is remaining startup purchases, plus a few months of recurring baby expenses, plus any expected income drop during leave, minus savings you will have by the due date.',
      },
      {
        question: 'What are typical newborn expenses in Canada?',
        answer:
          'Common startup categories include a safe sleep setup, car seat, diapers, clothing, and feeding supplies. Many families also budget for a stroller or carrier. Gift and used items can reduce the cash needed.',
      },
      {
        question: 'Does this include childcare?',
        answer: 'Only if you turn childcare on and enter a monthly amount. The calculator does not assume a national childcare rate or subsidy.',
      },
      {
        question: 'Does everyone receive the maximum Canada Child Benefit?',
        answer: `No. The ${ccb.period_label} maximum for a child under 6 is a ceiling. Actual CCB depends on income and family circumstances. Enter your own estimate or use the CRA calculator.`,
      },
    ],
  };
}
