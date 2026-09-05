# Paycheque.app

A Canadian paycheck / take-home pay calculator, and the foundation for other low-maintenance payroll utilities on the same Laravel site.

**Know exactly how much of your salary you take home.**

This is not a payroll product, a tax-filing app, a government service, or a financial platform. It is a fast, anonymous estimator built on official CRA / Revenu Québec published rules.

## Local development

Requires PHP 8.3+, Composer, Node 20+, and SQLite.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

Vite development workflow (in a second terminal):

```bash
npm run dev
```

Or:

```bash
composer run dev
```

Useful commands:

```bash
php artisan test
./vendor/bin/pest
vendor/bin/pint
php artisan migrate --seed
```

`DB_CONNECTION=sqlite` is the default. The database file is `database/database.sqlite`. No Redis and no external database are required.

Set `APP_NAME=Paycheque.app` and `APP_URL` to the production host. Canonical URLs, Open Graph URLs, and the sitemap all use `APP_URL`.

## Tax data

Tax rules must not live in controllers, Livewire, or Blade.

```text
resources/tax/{year}/
    sources.php          # edition, retrieved date, official citations
    federal.php
    cpp.php
    qpp.php
    ei.php
    qpip.php
    ontario.php
    quebec.php
    ...                  # one file per province/territory
```

Amounts are dollar strings so they can be checked against CRA tables. The engine converts them to integer cents.

`CURRENT_TAX_YEAR` in `.env` selects the active year. `TaxFreshness` reads `sources.php` for “Tax year” and “Last updated” on every tax-related page.

### Updating for a new year

1. Copy `resources/tax/2026` to `resources/tax/2027`.
2. Edit the PHP files from official sources. Do not invent rates.
3. Update `sources.php` with the new edition, `retrieved_date`, and citation list.
4. Set `CURRENT_TAX_YEAR=2027`.
5. Run `php artisan test`. Update fixtures only after recalculating from the official source.
6. Compare a few salaries against [PDOC](https://www.canada.ca/en/revenue-agency/services/e-services/digital-services-businesses/payroll-deductions-online-calculator.html).

Primary sources:

- [T4127 Payroll Deductions Formulas](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4127-payroll-deductions-formulas-computer-programs.html)
- [T4032 Payroll Deductions Tables](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4032-payroll-deductions-tables.html)
- [CPP rates](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/payroll-deductions-contributions/canada-pension-plan-cpp/cpp-contribution-rates-maximums-exemptions.html)
- [Revenu Québec tax rates](https://www.revenuquebec.ca/en/citizens/income-tax-return/completing-your-income-tax-return/income-tax-rates/)
- Quebec Finance personal-tax parameters PDF for the new year

The engine follows CRA T4127 **Option 1** for a full-year employee with claim code 1:

1. Annual CPP/QPP, CPP2/QPP2, EI, and QPIP
2. Additional CPP/QPP (factor F5) reduces taxable income
3. Federal T3 / T1, including the 16.5% Quebec abatement
4. Provincial T4 / T2, including Ontario health premium/surtax, BC tax reduction, Alberta K5P
5. Quebec provincial tax uses Revenu Québec parameters (CRA does not compute Quebec T2)

## Adding a province

All 13 jurisdictions already have calculator landing pages.

1. Add the enum case and slug in `app/Support/Province.php`.
2. Add `resources/tax/{year}/{province}.php`.
3. Register the file in `TaxRuleProvider`.
4. Write distinct copy in `app/Content/PaycheckContent.php` (intro, how tax works, FAQs). Do not only swap the province name.
5. If the province should get indexable salary pages, add its code to `config/tools.php` → `salary_page_provinces`.

Quebec must keep its own QPP / QPIP / abatement path. Do not treat it as a regular province.

## Adding a salary page

Do not create a controller or Blade file per salary.

1. Add the integer amount to `config/tools.php` → `popular_salaries`.
2. The amount is only indexable in provinces listed in `salary_page_provinces`.
3. Example take-home tables on province pages use `example_salaries`.
4. Routes are `/{province}/{salary}-salary`. The sitemap includes only catalog-allowed combinations.

Do not generate thousands of thin pages. Only add amounts people actually search.

## Adding a new calculator

The app is a utility platform. A new tool should need:

1. A class implementing `App\Calculators\Contracts\Calculator` (if it calculates something)
2. Input validation (Livewire or a form request)
3. A result object or `CalculatorResult`
4. A Blade/Livewire page
5. Unique SEO copy (see `CalculatorHubContent` or a new content class)
6. A named route
7. Pest tests
8. An entry in `ToolCatalog` / sitemap if the page should be indexed

Register calculation modules in `config/tools.php`. Reuse `x-layouts.app`, `x-seo.meta`, `x-ad-slot`, `x-tax-freshness`, and analytics. Do not call an external API for calculations. Do not couple new tools to `PayrollCalculator` unless they actually need payroll tax.

## SEO

- Server-rendered Blade. Calculator inputs are Livewire state, not query-string URLs.
- Every indexable page sets title, description, canonical, Open Graph, robots, and JSON-LD through `SeoPage`.
- Query-string variants (`?salary=80000`) are `noindex,follow` and keep the clean canonical.
- Province pages: `/{province}-paycheck-calculator`
- Salary pages: `/{province}/{salary}-salary` for catalog entries only
- Specialized hubs: `/paycheque-calculator`, `/take-home-pay-calculator`, `/salary-after-tax-calculator`, `/hourly-to-salary-calculator`, `/salary-to-hourly-calculator`, `/biweekly-pay-calculator`, `/weekly-pay-calculator`
- Guides: `/methodology`, `/about`, `/tax-rates`
- `/sitemap.xml` is generated from routes + `SalaryCatalog` + `ToolCatalog`. It excludes admin, login, and non-indexable salary combinations.
- `/robots.txt` allows public pages, disallows `/admin`, and points at the sitemap.
- Structured data: `WebSite` (home), `Organization`, `WebApplication` on calculator pages, `BreadcrumbList`, and `FAQPage` only when FAQs are visible. No fake ratings.

Internal linking is built into the province and salary templates: salary tables, adjacent salaries, and `x-related-calculators`.

## Ads

Ads stay off until an ad network is ready.

```env
ADS_ENABLED=false
```

```blade
<x-ad-slot placement="top" />
<x-ad-slot placement="middle" />
<x-ad-slot placement="bottom" />
```

Slot names are configured in `config/ads.php`. Never place an ad inside the salary input or above the primary calculate button.

## Analytics

```env
ANALYTICS_ENABLED=true
ANALYTICS_PROVIDER=local
```

Events are stored without exact salaries. Useful events: `calculator_started`, `calculator_completed`, `province_selected`, `pay_frequency_selected`, `share_clicked`. Admin is at `/admin` after seeding `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

## Deployment

Typical production stack: Linux, Nginx, PHP 8.3+, SQLite.

1. Set `APP_URL` to the public HTTPS origin (this is the canonical host).
2. `php artisan migrate --force`
3. `npm run build` (or build in CI and deploy `public/build`)
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`

No Redis. Queue can stay `sync`. Nginx `root` must be `public/`. Deny `.env`.

```bash
chown -R www-data:www-data storage bootstrap/cache database
chmod -R ug+rwx storage bootstrap/cache
```

Back up `database/database.sqlite` before deploys.

## Testing

```bash
php artisan test
```

Coverage includes known New Brunswick and Ontario cases, Quebec payroll treatment, CPP/CPP2/EI ceilings, pay-frequency consistency, hourly conversion, province/salary routes, sitemap/robots/canonicals, and admin authorization.

## Maintenance

Annually: copy the tax-year folder, update official sources, run Pest, review province copy, recheck sitemap and `APP_URL`.

Monthly: glance at `/admin` for province mix and completion rate. Do not store salaries.
