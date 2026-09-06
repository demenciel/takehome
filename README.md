# Paycheque.app

Independent Canadian paycheck / take-home pay calculator.

It estimates federal tax, provincial or territorial tax, CPP or QPP, EI (and QPIP in Quebec), and net pay from a salary, province, and pay frequency.

It is **not** a government service, payroll system, tax-filing product, or official CRA / Revenu Québec tool. Results are estimates.

Stack: Laravel 12, PHP 8.3+, Livewire 3, Blade, Tailwind, MySQL, Pest.

---

## Local development

Needs PHP 8.3+ with `pdo_mysql`, Composer, Node 20+, and MySQL 8.

```sql
CREATE DATABASE paycheque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set MySQL in `.env`:

```env
APP_NAME=Paycheque.app
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paycheque
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Dev assets:

```bash
npm run dev
# or
composer run dev
```

```bash
php artisan test
vendor/bin/pint
```

Pest uses in-memory SQLite (`phpunit.xml`). Tests do not need MySQL.

---

## Tax data

Rates must not live in controllers, Livewire, or Blade.

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

Amounts are CAD dollar strings. The engine converts them to integer cents.

- `CURRENT_TAX_YEAR` selects the active folder.
- `TaxFreshness` reads `sources.php` for **Tax year** and **Last updated** on every tax-related page.

### Update for a new year

1. Copy `resources/tax/2026` → `resources/tax/2027`.
2. Edit files from official sources. Do not invent rates.
3. Update `sources.php` (`edition`, `retrieved_date`, citations).
4. Set `CURRENT_TAX_YEAR=2027`.
5. Run `php artisan test`. Change fixtures only after recalculating from the source.
6. Spot-check a few salaries against [CRA PDOC](https://www.canada.ca/en/revenue-agency/services/e-services/digital-services-businesses/payroll-deductions-online-calculator.html).

Sources:

- [T4127 Payroll Deductions Formulas](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4127-payroll-deductions-formulas-computer-programs.html)
- [T4032 Payroll Deductions Tables](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4032-payroll-deductions-tables.html)
- [CPP rates](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/payroll-deductions-contributions/canada-pension-plan-cpp/cpp-contribution-rates-maximums-exemptions.html)
- [Revenu Québec tax rates](https://www.revenuquebec.ca/en/citizens/income-tax-return/completing-your-income-tax-return/income-tax-rates/)
- Quebec Finance personal-tax parameters PDF for the new year

### How a paycheck is calculated

CRA T4127 **Option 1**, full-year employee, claim code 1:

1. Annual CPP/QPP, CPP2/QPP2, EI, QPIP
2. Additional CPP/QPP (F5) reduces taxable income
3. Federal tax, including the 16.5% Quebec abatement
4. Provincial/territorial tax (Ontario health premium/surtax, BC reduction, Alberta K5P, Yukon K4P where they apply)
5. Quebec provincial tax uses Revenu Québec parameters (CRA T2 = 0 for Quebec)

Pay frequency only divides the **same annual totals** (12 / 24 / 26 / 52). Hourly conversion assumes 40 hours × 52 weeks unless the user changes hours.

---

## Adding a province

All 13 jurisdictions already have landing pages.

1. Enum + slug in `app/Support/Province.php` (add aliases if people will type short forms).
2. Tax file `resources/tax/{year}/{name}.php`.
3. Register the file in `app/Services/Tax/TaxRuleProvider.php`.
4. Unique copy in `app/Content/PaycheckContent.php`. Do not only swap the province name.
5. To index salary pages, add the code to `config/tools.php` → `salary_page_provinces`.

Quebec is not a regular province: QPP, QPIP, lower EI, federal abatement, Revenu Québec tax.

Hyphenated slugs (`british-columbia`, `prince-edward-island`, …) are listed explicitly in `Province::slugPattern()` so the `/{slug}-paycheck-calculator` route matches. Canadian spelling `/{slug}-paycheque-calculator` 301s to `paycheck`.

---

## Adding a salary page

Do **not** add a controller or Blade file per salary.

1. Add the amount to `config/tools.php` → `popular_salaries`.
2. It is only indexed in `salary_page_provinces` (currently ON, QC, BC, AB, NB, NS, MB).
3. Province example tables use `example_salaries`.
4. URL: `/{province}/{salary}-salary`. Alternate `/{province}/salary/{salary}` 301s.

Do not generate thousands of thin pages.

---

## Adding a new calculator

A new tool should need:

1. A class implementing `App\Calculators\Contracts\Calculator` (if it calculates)
2. Input validation (Livewire or form request)
3. A result object (`CalculatorResult` or similar)
4. A Blade/Livewire page
5. Unique SEO copy (`CalculatorHubContent` or a new content class)
6. A named route
7. Pest tests
8. `ToolCatalog` + sitemap if it should be indexed

Register modules in `config/tools.php`. Reuse `x-layouts.app`, `x-seo.meta`, `x-ad-slot`, `x-tax-freshness`. Calculate in PHP. Do not call an external tax API.

---

## SEO

- Server-rendered Blade. Calculator inputs are Livewire state, not query-string URLs.
- `SeoPage` sets title, description, canonical, Open Graph, robots, JSON-LD.
- `?salary=80000` is `noindex,follow` and keeps the clean canonical.
- `APP_URL` is the canonical host (set this on Laravel Cloud).

| Kind | URL |
| --- | --- |
| Home | `/` |
| National | `/canada-paycheck-calculator` |
| Province | `/{province}-paycheck-calculator` |
| Salary | `/{province}/{salary}-salary` |
| Hubs | `/paycheque-calculator`, `/take-home-pay-calculator`, `/salary-after-tax-calculator`, `/hourly-to-salary-calculator`, `/salary-to-hourly-calculator`, `/biweekly-pay-calculator`, `/weekly-pay-calculator` |
| Guides | `/methodology`, `/tax-rates` |
| Legal | `/about`, `/privacy`, `/terms`, `/contact` |
| Crawl | `/sitemap.xml`, `/robots.txt` |

Sitemap is built from routes + `SalaryCatalog` + `ToolCatalog`. It excludes admin, login, and non-catalog salary URLs.

Structured data: `WebSite` (home), `Organization`, `WebApplication` on calculator pages, `BreadcrumbList`, `FAQPage` only when FAQs are on the page. No fake ratings.

---

## Privacy, terms, contact

- `/privacy` must describe what is actually on: first-party session cookie, first-party event buckets (no exact salary), Google Analytics when `ANALYTICS_MEASUREMENT_ID` is set, AdSense when ads are enabled, contact-form fields, hosting logs.
- `/terms` — estimates only, not advice.
- `/contact` — name / email / message, throttled. Set `CONTACT_EMAIL` to an inbox you read. Mail uses `MAIL_*`.
- Update `config/site.php` dates when those policies change.

---

## Ads

```env
ADS_ENABLED=false
ADS_PROVIDER=adsense
ADS_CLIENT=
```

```blade
<x-ad-slot placement="top" />
<x-ad-slot placement="middle" />
<x-ad-slot placement="bottom" />
```

Slots: `config/ads.php`. Leave ads off until AdSense is approved. Do not put an ad inside the salary field or above Calculate.

---

## Analytics

```env
ANALYTICS_ENABLED=true
ANALYTICS_PROVIDER=local
ANALYTICS_MEASUREMENT_ID=G-XXXXXXXX
```

- First-party events (province, frequency, salary **range**, path) go to MySQL. Exact salary is not stored.
- GA4 loads only when `ANALYTICS_MEASUREMENT_ID` is set. Do not send salary values to gtag.
- Admin: set `ADMIN_EMAIL` / `ADMIN_PASSWORD`, migrate --seed, visit `/admin`.

---

## Laravel Cloud

1. Attach Cloud MySQL. Do **not** set `DB_HOST=127.0.0.1`.
2. Let Cloud inject `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
3. Set `APP_URL` to the public `https://` origin.
4. Set `ANALYTICS_MEASUREMENT_ID`, `CONTACT_EMAIL`, `ADMIN_*` as needed.
5. Deploy command should run `php artisan migrate --force`.
6. After changing env vars, redeploy so config cache refreshes.

`CACHE_STORE=database` and `SESSION_DRIVER=database` need migrations (`cache`, `sessions`). The tax-year badge uses the cache table.

---

## Environment variables

| Variable | Purpose |
| --- | --- |
| `APP_NAME` / `APP_URL` | Brand and canonical host |
| `DB_*` | MySQL |
| `CURRENT_TAX_YEAR` | Active rule folder |
| `TAX_RULES_CACHE_TTL` | Seconds to cache loaded tax files |
| `ADS_ENABLED` / `ADS_PROVIDER` / `ADS_CLIENT` | AdSense |
| `ANALYTICS_ENABLED` / `ANALYTICS_MEASUREMENT_ID` | First-party events + GA4 |
| `CONTACT_EMAIL` / `LEGAL_NAME` | Contact form and legal pages |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Seeded admin user |
| `MAIL_*` | Contact form delivery |

See `.env.example`.

---

## Testing

```bash
php artisan test
```

Covers known NB/ON cases, Quebec payroll, CPP/CPP2/EI ceilings, frequencies, hourly conversion, all 13 province routes, salary catalog 404s, hyphenated slugs, paycheque redirects, sitemap/robots/canonicals, privacy/terms/contact, admin auth.

---

## Maintenance

**Monthly:** `/admin` for province mix and completion rate. Do not store salaries.

**When enabling ads:** set AdSense env vars, turn `ADS_ENABLED=true`, reread `/privacy`.

**Annually:** copy the tax-year folder, update official sources, run Pest, review province copy, confirm `APP_URL` and sitemap in Search Console.
