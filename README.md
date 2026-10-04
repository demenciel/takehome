# Paycheque.app

Independent Canadian paycheck / take-home pay calculator.

The public site is a static **Astro + TypeScript** application. Payroll math runs in the browser. Ordinary page requests are static HTML, CSS, and JS on Cloudflare. There is no PHP runtime, Laravel, database, admin panel, or calculator API.

Production origin: [https://paycheque.app](https://paycheque.app)

## Local setup

```bash
npm install
npm run dev
```

Other scripts:

```bash
npm test
npm run build
npm run preview
npm run deploy
```

`npm run deploy` builds the static site and uploads `dist/` with Wrangler.

## Architecture

- `src/pages/` — prerendered public pages (calculators, province pages, salary pages, legal)
- `src/components/` — Astro UI plus calculator forms
- `src/scripts/` — vanilla TypeScript calculator clients (loaded only on the pages that need them)
- `src/lib/tax/` — CRA T4127-style payroll engine
- `src/lib/overtime.ts`, `src/lib/tax/comparison.ts`, `src/lib/military.ts`, `src/lib/family/` — specialized engines
- `src/data/` — versioned official rates and tables
- `src/lib/content/` — SEO/AEO copy ported from the previous Laravel site
- `public/_redirects` — permanent aliases for Cloudflare

Calculations are deterministic TypeScript ports of the previous Laravel engines. They do not call a network API.

## Calculators

- Canadian paycheck / take-home pay
- Hourly ↔ salary
- Overtime (jurisdiction rules + after-tax comparison)
- Bonus tax (`net(salary + bonus) − net(salary)`)
- Raise (`net(new) − net(old)`)
- CAF Regular / Reserve salary
- Parental leave and EI maternity / parental benefits
- Baby cost planner

Vacation and statutory-holiday calculators did not exist in the previous site and are not invented here.

## Tax, employment, and CAF data

| Data | Location |
| --- | --- |
| 2026 payroll rates | `src/data/tax/2026/` |
| Overtime rules | `src/data/employment/overtime.json` |
| CAF 2025 published tables | `src/data/military/2025/` |
| EI maternity / parental | `src/data/benefits.json` |
| Baby budget defaults | `src/data/baby-budget.json` |

Do not invent newer CAF tables. The published Regular Force monthly and Reserve daily scales used here remain effective **1 April 2025**.

### Adding a tax year

1. Copy `src/data/tax/2026/` to `src/data/tax/2027/`.
2. Update brackets, credits, CPP/QPP, EI, QPIP, sources, and retrieved dates from official publications.
3. Register the year in `src/lib/tax/rules.ts` and switch `CURRENT_TAX_YEAR`.
4. Add or extend Vitest cases. Do not change formulas just to make new rates “look simpler.”

### Adding a calculator

1. Put pure math in `src/lib/`.
2. Add a client script in `src/scripts/` and an Astro form component.
3. Add a prerendered page that already contains the H1, answer summary, and sources in HTML.
4. Add the URL to `src/lib/content/hubs.ts` and `src/lib/urls.ts` so it enters the sitemap.

### Adding an SEO page

Salary and province pages are generated at build time (`getStaticPaths`). Google should see H1, the answer summary, tax breakdown, sources, and metadata without JavaScript. Do not mass-generate thin extra salary pages.

## Sitemap, robots, ads.txt

- `/sitemap.xml` lists every canonical indexable page (`https://paycheque.app` origin)
- `/robots.txt` allows crawling and points at the sitemap
- `/ads.txt` uses the existing AdSense publisher ID

Redirects and aliases are excluded from the sitemap.

## Redirects

Aliases such as `/salary-increase-calculator` → `/raise-calculator` and PEI/short-slug province aliases are listed in `src/lib/redirects.ts` and written to `public/_redirects` as HTTP 301s.

## Analytics and AdSense

Environment variables (see `.env.example`):

| Variable | Purpose |
| --- | --- |
| `PUBLIC_ANALYTICS_ENABLED` | Load GA4 unless set to `false` |
| `PUBLIC_ANALYTICS_MEASUREMENT_ID` | GA4 measurement ID |
| `PUBLIC_ADS_ENABLED` | Must be `true` to show ads |
| `PUBLIC_ADS_PROVIDER` | `adsense` |
| `PUBLIC_ADS_CLIENT` | `ca-pub-…` slot client |
| `PUBLIC_CONTACT_EMAIL` | Contact mailto |
| `PUBLIC_LEGAL_NAME` | Legal name on Terms |

Never send exact salary, wage, bonus, raise, overtime, military pay, benefit, or take-home values to analytics.

Ads are globally disabled unless `PUBLIC_ADS_ENABLED=true`.

## Tests

Vitest ports the previous Laravel behavioral checks:

```bash
npm test
```

Coverage includes payroll (federal/provincial/Quebec, CPP/QPP/CPP2/QPP2, EI/QPIP), overtime, bonus/raise comparisons, CAF pay, parental/EI, money rounding, URL inventory, and SEO helpers.

Rounding follows the previous integer-cent, half-up money implementation.

## Cloudflare deployment

The site is static assets. Wrangler config (`wrangler.jsonc`) points `assets.directory` at `./dist` and uses a 404 page. There is no Worker `main`, D1, KV, R2, or Durable Objects.

```bash
npm run build
npx wrangler deploy
```

Or `npm run deploy`.

Custom domain: `paycheque.app` (HTTPS). `www.paycheque.app` should 301 to the apex in the Cloudflare zone. Do not cut over DNS until a workers.dev / preview deploy has been smoke-tested.

## Environment variables you still configure

Set production values in Cloudflare (or a local `.env`) from `.env.example`. Do not commit secrets. The AdSense publisher ID already used on the live site is public; do not invent a different one.