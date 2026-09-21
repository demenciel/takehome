import { allProvinces, provinceAliases, provinceFromCode } from './province';
import { POPULAR_SALARIES, SALARY_PAGE_PROVINCES } from './catalog';

export function redirectMap(): Record<string, string> {
  const redirects: Record<string, string> = {
    '/canada-paycheque-calculator': '/canada-paycheck-calculator',
    '/salary-increase-calculator': '/raise-calculator',
    '/caf-salary-calculator': '/military-salary-calculator',
    '/canadian-military-pay-calculator': '/military-salary-calculator',
    '/maternity-leave-calculator': '/parental-leave-calculator',
    '/ei-parental-benefits': '/ei-maternity-parental-benefits',
  };

  for (const province of allProvinces()) {
    redirects[`/${province.slug}-paycheque-calculator`] = `/${province.slug}-paycheck-calculator`;
  }

  const aliases = provinceAliases();

  for (const [alias, slug] of Object.entries(aliases)) {
    redirects[`/${alias}-paycheck-calculator`] = `/${slug}-paycheck-calculator`;
    redirects[`/${alias}-paycheque-calculator`] = `/${slug}-paycheck-calculator`;
    redirects[`/${alias}-overtime-pay-calculator`] = `/${slug}-overtime-pay-calculator`;
    redirects[`/${alias}-bonus-tax-calculator`] = `/${slug}-bonus-tax-calculator`;
    redirects[`/${alias}-raise-calculator`] = `/${slug}-raise-calculator`;
  }

  for (const code of SALARY_PAGE_PROVINCES) {
    const province = provinceFromCode(code);
    if (!province) continue;

    for (const salary of POPULAR_SALARIES) {
      redirects[`/${province.slug}/salary/${salary}`] = `/${province.slug}/${salary}-salary`;

      for (const [alias, slug] of Object.entries(aliases)) {
        if (slug !== province.slug) continue;
        redirects[`/${alias}/${salary}-salary`] = `/${province.slug}/${salary}-salary`;
        redirects[`/${alias}/salary/${salary}`] = `/${province.slug}/${salary}-salary`;
      }
    }
  }

  return redirects;
}

export function astroRedirects() {
  return Object.fromEntries(
    Object.entries(redirectMap()).map(([from, destination]) => [from, { status: 301 as const, destination }]),
  );
}

export function cloudflareRedirects() {
  return Object.entries(redirectMap())
    .map(([from, to]) => `${from} ${to} 301`)
    .join('\n');
}
