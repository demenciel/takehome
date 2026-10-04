import { allProvinces } from './province';
import { POPULAR_SALARIES, SALARY_PAGE_PROVINCES, SITE_ORIGIN, absoluteUrl } from './catalog';
import { TOOL_HUBS } from './content/hubs';

export const STATIC_PAGES = [
  '/',
  '/canada-paycheck-calculator',
  '/about',
  '/methodology',
  '/tax-rates',
  '/privacy',
  '/terms',
  '/contact',
];

export function canonicalPaths(): string[] {
  const paths = new Set<string>(STATIC_PAGES);

  for (const hub of TOOL_HUBS) {
    paths.add(hub.href);
  }

  for (const province of allProvinces()) {
    paths.add(`/${province.slug}-paycheck-calculator`);
    paths.add(`/${province.slug}-overtime-pay-calculator`);
    paths.add(`/${province.slug}-bonus-tax-calculator`);
    paths.add(`/${province.slug}-raise-calculator`);

    if (SALARY_PAGE_PROVINCES.includes(province.code)) {
      for (const salary of POPULAR_SALARIES) {
        paths.add(`/${province.slug}/${salary}-salary`);
      }
    }
  }

  return [...paths];
}

export function sitemapUrls() {
  return canonicalPaths().map((path) => absoluteUrl(path === '/' ? '/' : path));
}

export function sitemapXml() {
  const urls = sitemapUrls()
    .map((loc) => `  <url><loc>${loc}</loc></url>`)
    .join('\n');

  return `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls}\n</urlset>\n`;
}

export { SITE_ORIGIN };
