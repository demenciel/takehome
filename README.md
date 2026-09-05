# TakeHome.ca

A Canadian paycheck / take-home pay calculator, and the first product on a small Laravel foundation for launching other low-cost, SEO-driven utilities.

**Know exactly how much of your salary you take home.**

This is not a payroll product, a tax-filing app, or a financial platform. It is a fast, anonymous estimator built on official CRA / Revenu Québec published rules.

## Product overview

A visitor can enter a salary, choose a province or territory, pick a pay frequency, and immediately see estimated:

- Gross pay
- Federal and provincial / territorial income tax
- CPP or QPP (including CPP2 / QPP2 where it applies)
- EI (and QPIP in Quebec)
- Net take-home, effective tax rate, and an annual breakdown

No account. No email wall. Calculations run locally in PHP.

## Architecture

One Laravel 12 monolith. Future utilities should reuse layout, SEO, ads, analytics, routing conventions, and the calculator contract — without appearing in this product’s UX.

```
app/
  Calculators/          # Calculator interface + paycheck module
  Content/              # Province-specific copy and FAQs
  Livewire/             # Interactive calculator
  Services/Tax/         # CRA Option 1 engine
  Services/Analytics/   # Local, salary-free event store
  Support/              # Money, Province, PayFrequency
resources/tax/{year}/   # Versioned, auditable tax data
```

Intended future brands on the same foundation:

- TakeHome.ca
- RuckCalc.ca
- MortgageMath.ca
- DebtCalc.ca
- ConvertMyFile.ca

Do not mix those products into the current paycheck UI.

## Setup

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

## SQLite

`DB_CONNECTION=sqlite` is the default. The database file is `database/database.sqlite`. No Redis and no external database are required.

## Development commands

```bash
php artisan serve
npm run dev
php artisan test
./vendor/bin/pest
php artisan migrate --seed
vendor/bin/pint
```

## Testing

Pest is the test runner.

```bash
php artisan test
```

Coverage includes:

- Money arithmetic
- CPP / QPP / EI / QPIP limits
- Federal brackets and BPA phase-out
- Known New Brunswick and Ontario paycheck cases
- Every province/territory at $80,000
- Pay frequency consistency
- HTTP / SEO / Livewire / admin authorization

Fixture notes live in `tests/Fixtures/tax/README.md`.

## Tax data structure

Rules live in versioned PHP files, not in controllers or Blade:

```text
resources/tax/2026/
    sources.php
    federal.php
    cpp.php
    qpp.php
    ei.php
    qpip.php
    alberta.php
    ...
```

Amounts are dollar strings so they can be checked against CRA tables. The engine converts them to integer cents.

`CURRENT_TAX_YEAR` selects the active year. Do not hardcode the year in application code.

## Updating tax rules

A yearly update should take hours, not days.

1. **Where data lives** — `resources/tax/{year}/`.
2. **Add a new year** — copy `resources/tax/2026` to `resources/tax/2027`, then edit the files. Set `CURRENT_TAX_YEAR=2027`.
3. **CRA sources to check**
   - [T4127 Payroll Deductions Formulas](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4127-payroll-deductions-formulas-computer-programs.html) (January and July editions)
   - [T4032 Payroll Deductions Tables](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4032-payroll-deductions-tables.html)
   - [CPP rates](https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/payroll-deductions-contributions/canada-pension-plan-cpp/cpp-contribution-rates-maximums-exemptions.html)
   - [Revenu Québec tax rates](https://www.revenuquebec.ca/en/citizens/income-tax-return/completing-your-income-tax-return/income-tax-rates/)
   - Quebec Finance personal-tax parameters PDF for the new year
4. **Federal values** — brackets, constants K, BPA min/max, CEA, lowest rate, Quebec abatement.
5. **Provincial values** — brackets, constants KP, basic personal amounts, Ontario surtax / health premium / reduction, BC reduction, Alberta K5P, Yukon K4P, Manitoba BPAMB.
6. **CPP / QPP** — YMPE, YAMPE, rates, maximums, base vs additional split.
7. **EI / QPIP** — MIE, employee rates, Quebec EI rate, QPIP rate and maximum.
8. **Tests** — run the suite. Update fixtures only after recalculating from the official source. Do not weaken assertions.
9. **Validate** — compare a few salaries against [PDOC](https://www.canada.ca/en/revenue-agency/services/e-services/digital-services-businesses/payroll-deductions-online-calculator.html). Small cent differences can come from per-period CPP exemption truncation (T4127 Table 6.1).

Document every source in that year’s `sources.php`. Do not invent citations.

## Calculation methodology

The engine follows CRA T4127 **Option 1** for a full-year employee with constant pay and claim code 1:

1. Annual CPP/QPP, CPP2/QPP2, EI, and QPIP
2. Additional CPP/QPP (factor F5) reduces taxable income
3. Federal T3 / T1, including the 16.5% Quebec abatement
4. Provincial T4 / T2, including Ontario health premium and surtax, BC tax reduction, and Alberta tax credit
5. Quebec provincial tax uses Revenu Québec 2026 brackets and the $18,952 basic personal amount (CRA does not compute Quebec T2)

Results are estimates. Label them that way in the UI.

## Adding a new calculator

1. Implement `App\Calculators\Contracts\Calculator`.
2. Register it in `config/tools.php`.
3. Add a Livewire (or Blade) input form and result formatter.
4. Add a `tool_pages` row and unique SEO copy.
5. Add Pest tests.

Reuse the existing layout, `x-ad-slot`, analytics, sitemap patterns, and `CalculatorResult`. Do not add a CMS.

## Adding a new province page

Province pages are generated from `App\Support\Province` plus unique copy in `App\Content\PaycheckContent`. Add:

1. Tax data file under `resources/tax/{year}/`
2. Enum case and slug
3. Distinct intro, body, and FAQs — do not only swap the province name

## SEO architecture

- Server-rendered Blade
- Unique title, description, canonical, Open Graph, and JSON-LD (`WebApplication`, `FAQPage`, `BreadcrumbList`)
- `/sitemap.xml` and `/robots.txt`
- Province URLs: `/{province}-paycheck-calculator`
- Popular salary URLs: `/{province}/{salary}-salary` (config-driven list only)

Do not generate tens of thousands of thin salary pages. Edit `config/tools.php` → `popular_salaries` to add indexable amounts.

Personalized query-string calculations are for sharing, not for bulk indexing.

## Ads configuration

```env
ADS_ENABLED=false
ADS_PROVIDER=
ADS_CLIENT=
```

Slots are components, not hardcoded scripts:

```blade
<x-ad-slot name="result-inline" />
<x-ad-slot name="content-mid" />
<x-ad-slot name="bottom-banner" />
<x-ad-slot name="sidebar" />
```

Ads stay off in development. Never place an ad inside the salary input or above the primary calculate button.

## Analytics configuration

```env
ANALYTICS_ENABLED=true
ANALYTICS_PROVIDER=local
```

Events are stored in SQLite without exact salaries. Useful later: top provinces, salary ranges, frequencies, completion rate, top pages.

## Admin

Lightweight only. Set `ADMIN_EMAIL` and `ADMIN_PASSWORD`, then `php artisan migrate --seed`. Visit `/admin`.

## Localization

User-facing strings go through `__()` and `lang/en`. English ships first. French can be added later as `lang/fr` without rewriting views.

## Deployment (inexpensive VPS)

Typical production stack:

- Linux, Nginx, PHP 8.3+
- SQLite
- `php artisan migrate --force`
- `npm run build` (or build in CI and deploy `public/build`)
- `php artisan config:cache && php artisan route:cache && php artisan view:cache`

No Redis. No Docker requirement. Queue can stay `sync`. Scheduler is optional; add cron only if you later add a backup command:

```cron
* * * * * cd /var/www/takehome && php artisan schedule:run >> /dev/null 2>&1
```

Nginx should point `root` at `public/`, deny access to `.env`, and pass PHP to php-fpm.

Storage permissions:

```bash
chown -R www-data:www-data storage bootstrap/cache database
chmod -R ug+rwx storage bootstrap/cache
```

## Backup

SQLite is one file.

1. Copy `database/database.sqlite` daily (and before deploys).
2. Copy `storage/` if you later store uploads.
3. Keep the previous copy so you can roll back by replacing the file and running `php artisan migrate`.

Example:

```bash
cp database/database.sqlite storage/backups/database-$(date +%F).sqlite
```

## Security

- CSRF on all forms
- Request validation in Livewire
- Admin behind auth + `is_admin`
- Login throttled
- Blade output escaped
- No secrets in the frontend
- No SIN, bank, or exact salary in logs/analytics

## Maintenance checklist

Monthly:

- Review traffic and admin metrics
- Check application logs
- Check calculator completion rate

Annually:

- Update tax rules from CRA / Revenu Québec
- Recalculate fixtures and run Pest
- Review province pages and year references
- Recheck sitemap / robots / canonicals

## Future roadmap

P1: richer share pages, more salary examples, French copy  
P2: additional calculators, optional paid reports  
Not planned: accounts-for-accounts’-sake, mobile apps, AI chat, payroll integrations

## Environment variables

See `.env.example`. Important ones:

| Variable | Purpose |
| --- | --- |
| `APP_NAME` / `APP_URL` | Brand and canonical host |
| `CURRENT_TAX_YEAR` | Active rule set |
| `ADS_ENABLED` | Off by default |
| `ANALYTICS_ENABLED` | Local event store |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Seeded admin user |
