import { describe, expect, it } from 'vitest';
import { canonicalPaths, sitemapUrls } from '../src/lib/urls';
import { redirectMap } from '../src/lib/redirects';
import { allProvinces } from '../src/lib/province';
import { POPULAR_SALARIES, SALARY_PAGE_PROVINCES, absoluteUrl } from '../src/lib/catalog';

describe('URL inventory', () => {
  it('includes every canonical calculator and salary page', () => {
    const paths = canonicalPaths();

    expect(paths).toContain('/');
    expect(paths).toContain('/canada-paycheck-calculator');
    expect(paths).toContain('/paycheque-calculator');
    expect(paths).toContain('/overtime-pay-calculator');
    expect(paths).toContain('/bonus-tax-calculator');
    expect(paths).toContain('/raise-calculator');
    expect(paths).toContain('/military-salary-calculator');
    expect(paths).toContain('/parental-leave-calculator');
    expect(paths).toContain('/ei-maternity-parental-benefits');
    expect(paths).toContain('/baby-cost-calculator');
    expect(paths).toContain('/ontario-paycheck-calculator');
    expect(paths).toContain('/ontario/80000-salary');
    expect(paths).not.toContain('/admin');
    expect(paths).not.toContain('/canada-paycheque-calculator');
  });

  it('generates salary pages only for the Laravel salary-page provinces', () => {
    const salaryPaths = canonicalPaths().filter((path) => path.endsWith('-salary'));
    expect(salaryPaths).toHaveLength(SALARY_PAGE_PROVINCES.length * POPULAR_SALARIES.length);
    expect(salaryPaths).toContain('/quebec/100000-salary');
    expect(salaryPaths.some((path) => path.startsWith('/yukon/'))).toBe(false);
  });

  it('preserves alias 301s', () => {
    const redirects = redirectMap();
    expect(redirects['/salary-increase-calculator']).toBe('/raise-calculator');
    expect(redirects['/caf-salary-calculator']).toBe('/military-salary-calculator');
    expect(redirects['/canadian-military-pay-calculator']).toBe('/military-salary-calculator');
    expect(redirects['/pei-paycheck-calculator']).toBe('/prince-edward-island-paycheck-calculator');
    expect(redirects['/ontario-paycheque-calculator']).toBe('/ontario-paycheck-calculator');
    expect(redirects['/ontario/salary/80000']).toBe('/ontario/80000-salary');
  });

  it('does not redirect canonical province pages', () => {
    const redirects = redirectMap();
    for (const province of allProvinces()) {
      expect(redirects[`/${province.slug}-paycheck-calculator`]).toBeUndefined();
    }
  });

  it('builds a sitemap on the production origin', () => {
    const urls = sitemapUrls();
    expect(urls[0]).toMatch(/^https:\/\/paycheque\.app/);
    expect(urls).toContain(absoluteUrl('/'));
    expect(urls).toContain(absoluteUrl('/ontario/80000-salary'));
    expect(urls.some((url) => url.includes('/admin'))).toBe(false);
  });
});
