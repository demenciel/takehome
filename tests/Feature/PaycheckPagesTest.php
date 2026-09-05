<?php

use App\Livewire\HourlySalaryConverter;
use App\Livewire\PaycheckCalculator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the homepage with the calculator and SEO tags', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('How much of your salary do you actually take home?', false)
        ->assertSee('Calculate my take-home', false)
        ->assertSee('<title>Canadian Paycheck Calculator', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@context":"https://schema.org"', false)
        ->assertSee('WebSite', false)
        ->assertSee('Organization', false)
        ->assertDontSee('$__contextArgs', false)
        ->assertSee('Tax year: 2026', false)
        ->assertSee('Last updated:', false);
});

it('renders the national calculator page', function () {
    $this->get('/canada-paycheck-calculator')
        ->assertOk()
        ->assertSee('Canada Paycheck Calculator', false)
        ->assertSee('Province paycheck calculators', false);
});

it('renders specialized calculator hubs with unique titles', function () {
    $this->get('/paycheque-calculator')
        ->assertOk()
        ->assertSee('<title>Canadian Paycheque Calculator', false)
        ->assertSee('Canadian Paycheque Calculator', false);

    $this->get('/take-home-pay-calculator')
        ->assertOk()
        ->assertSee('Take-Home Pay Calculator', false);

    $this->get('/salary-after-tax-calculator')
        ->assertOk()
        ->assertSee('Salary After Tax Calculator', false);

    $this->get('/biweekly-pay-calculator')
        ->assertOk()
        ->assertSee('Biweekly Pay Calculator', false);

    $this->get('/weekly-pay-calculator')
        ->assertOk()
        ->assertSee('Weekly Pay Calculator', false);
});

it('renders hourly conversion calculators', function () {
    $this->get('/hourly-to-salary-calculator')
        ->assertOk()
        ->assertSee('Hourly to Salary Calculator', false)
        ->assertSee('2080 hours/year', false);

    $this->get('/salary-to-hourly-calculator')
        ->assertOk()
        ->assertSee('Salary to Hourly Calculator', false);
});

it('renders unique Ontario province content', function () {
    $this->get('/ontario-paycheck-calculator')
        ->assertOk()
        ->assertSee('Ontario Paycheck Calculator', false)
        ->assertSee('Ontario Paycheck Calculator 2026', false)
        ->assertSee('Ontario Take-Home Pay', false)
        ->assertSee('How Ontario Income Tax Works', false)
        ->assertSee('Ontario Payroll Deductions', false)
        ->assertSee('Ontario Health Premium', false)
        ->assertSee('TD1ON', false)
        ->assertSee('$12,989', false)
        ->assertSee('$80,000 salary in Ontario', false);
});

it('renders unique Quebec province content', function () {
    $this->get('/quebec-paycheck-calculator')
        ->assertOk()
        ->assertSee('QPP', false)
        ->assertSee('QPIP', false)
        ->assertSee('Revenu Québec', false);
});

it('renders an indexable popular salary page with a server-rendered result', function () {
    $this->get('/ontario/80000-salary')
        ->assertOk()
        ->assertSee('$80,000 Salary After Tax in Ontario', false)
        ->assertSee('Estimated annual take-home', false)
        ->assertSee('$60,303.09', false)
        ->assertSee('Biweekly', false)
        ->assertSee('What Is $80,000 Per Hour?', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee(route('paycheck.salary', ['ontario', 80000]), false);
});

it('redirects alternate salary URLs to the canonical path', function () {
    $this->get('/ontario/salary/80000')
        ->assertRedirect('/ontario/80000-salary');
});

it('does not create thin salary pages outside the configured list', function () {
    $this->get('/ontario/80123-salary')->assertNotFound();
    $this->get('/yukon/80000-salary')->assertNotFound();
    $this->get('/saskatchewan/80000-salary')->assertNotFound();
});

it('noindexes calculator query-string variants and keeps a clean canonical', function () {
    $response = $this->get('/ontario-paycheck-calculator?salary=80000');

    $response->assertOk()
        ->assertSee('noindex,follow', false)
        ->assertSee('href="'.route('paycheck.province', 'ontario').'"', false)
        ->assertDontSee('ontario-paycheck-calculator?salary=80000', false);
});

it('exposes sitemap and robots files', function () {
    $sitemap = $this->get('/sitemap.xml');

    $sitemap->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('ontario-paycheck-calculator', false)
        ->assertSee('80000-salary', false)
        ->assertSee('methodology', false)
        ->assertSee('tax-rates', false)
        ->assertSee('hourly-to-salary-calculator', false)
        ->assertDontSee('yukon/80000-salary', false)
        ->assertDontSee('/admin', false)
        ->assertDontSee('salary=80000', false);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Sitemap:', false);
});

it('renders methodology and tax-rate pages from official sources', function () {
    $this->get('/methodology')
        ->assertOk()
        ->assertSee('How paycheck calculations work', false)
        ->assertSee('T4127', false)
        ->assertSee('Revenu Québec', false)
        ->assertSee('not a CRA or Revenu Québec service', false);

    $this->get('/tax-rates')
        ->assertOk()
        ->assertSee('2026 Canadian payroll tax rates', false)
        ->assertSee('74,600', false)
        ->assertSee('Ontario', false);

    $this->get('/about')
        ->assertOk()
        ->assertSee('Not a government service', false);
});

it('calculates take-home pay through Livewire', function () {
    Livewire::test(PaycheckCalculator::class)
        ->set('salary', '75000')
        ->set('province', 'NB')
        ->set('frequency', 'biweekly')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSet('hasCalculated', true)
        ->assertSee('Your estimated take-home pay');
});

it('shows a friendly error for a missing salary', function () {
    Livewire::test(PaycheckCalculator::class)
        ->set('salary', '')
        ->set('province', 'ON')
        ->call('calculate')
        ->assertHasErrors(['salary'])
        ->assertSee('Enter a valid salary amount.');
});

it('converts hourly and salary amounts in the dedicated converter', function () {
    Livewire::test(HourlySalaryConverter::class, ['mode' => 'hourly_to_salary'])
        ->set('hourlyWage', '40')
        ->set('hoursPerWeek', '40')
        ->call('convert')
        ->assertHasNoErrors()
        ->assertSee('$83,200.00');

    Livewire::test(HourlySalaryConverter::class, ['mode' => 'salary_to_hourly'])
        ->set('annualSalary', '80000')
        ->set('hoursPerWeek', '40')
        ->call('convert')
        ->assertHasNoErrors()
        ->assertSee('$38.46');
});

it('protects the admin dashboard', function () {
    $this->get('/admin')->assertRedirect('/admin/login');

    $user = User::factory()->create();
    $this->actingAs($user)->get('/admin')->assertForbidden();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Admin');
});
