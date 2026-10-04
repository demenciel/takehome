import type { Province } from './province';

export function relatedLinks(province?: Province | null, context = 'paycheck') {
  const paycheckHref = province ? `/${province.slug}-paycheck-calculator` : '/canada-paycheck-calculator';
  const paycheckLabel = province ? `${province.name} paycheck calculator` : 'Canada paycheck calculator';

  const all = {
    paycheck: { label: paycheckLabel, href: paycheckHref },
    overtime: {
      label: province ? `${province.name} overtime pay calculator` : 'Overtime pay calculator',
      href: province ? `/${province.slug}-overtime-pay-calculator` : '/overtime-pay-calculator',
    },
    bonus: {
      label: province ? `${province.name} bonus tax calculator` : 'Bonus tax calculator',
      href: province ? `/${province.slug}-bonus-tax-calculator` : '/bonus-tax-calculator',
    },
    raise: {
      label: province ? `${province.name} raise calculator` : 'Raise calculator',
      href: province ? `/${province.slug}-raise-calculator` : '/raise-calculator',
    },
    hourly: { label: 'Hourly to salary calculator', href: '/hourly-to-salary-calculator' },
    salary_after_tax: { label: 'Salary after tax calculator', href: '/salary-after-tax-calculator' },
    military: { label: 'Canadian Armed Forces salary calculator', href: '/military-salary-calculator' },
    parental: { label: 'Canadian parental leave calculator', href: '/parental-leave-calculator' },
    ei_benefits: { label: 'EI maternity and parental benefits', href: '/ei-maternity-parental-benefits' },
    baby: { label: 'Canadian baby cost calculator', href: '/baby-cost-calculator' },
    methodology: { label: 'How the estimate is calculated', href: '/methodology' },
  };

  const order: Array<keyof typeof all> =
    context === 'overtime'
      ? ['paycheck', 'hourly', 'raise', 'methodology']
      : context === 'bonus'
        ? ['paycheck', 'raise', 'salary_after_tax', 'methodology']
        : context === 'raise'
          ? ['paycheck', 'bonus', 'hourly', 'methodology']
          : context === 'military'
            ? ['paycheck', 'salary_after_tax', 'methodology']
            : context === 'parental'
              ? ['ei_benefits', 'baby', 'paycheck', 'methodology']
              : context === 'ei_benefits'
                ? ['parental', 'baby', 'paycheck', 'methodology']
                : context === 'baby'
                  ? ['parental', 'ei_benefits', 'paycheck', 'methodology']
                  : ['overtime', 'bonus', 'raise', 'hourly', 'military', 'methodology'];

  const links = order.map((key) => all[key]);

  if (province && context === 'paycheck') {
    links.unshift(all.paycheck);
  }

  return links;
}
