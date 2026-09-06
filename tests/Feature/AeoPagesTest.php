<?php

use App\Models\AnalyticsEvent;
use App\Services\Military\MilitarySalaryCalculator;
use App\Services\Overtime\OvertimePayEstimator;
use App\Services\Payroll\PayrollComparison;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the answer summary component with source metadata', function () {
    $this->blade(
        '<x-answer-summary question="How much is take-home?" answer="About sixty thousand dollars." :facts="[[\'label\' => \'Annual net\', \'value\' => \'$60,303.09\']]" year="2026" updated="September 5, 2026" methodology-url="/methodology" :sources="[[\'title\' => \'CRA T4127\', \'url\' => \'https://www.canada.ca/en/revenue-agency.html\']]" />'
    )->assertSee('id="direct-answer-heading"', false)
        ->assertSee('How much is take-home?', false)
        ->assertSee('About sixty thousand dollars.', false)
        ->assertSee('$60,303.09', false)
        ->assertSee('Tax year: 2026', false)
        ->assertSee('Last updated: September 5, 2026', false)
        ->assertSee('Methodology', false)
        ->assertSee('CRA T4127', false);
});

it('puts a direct answer below the H1 on an Ontario salary page', function () {
    $html = $this->get('/ontario/80000-salary')->assertOk()->getContent();

    expect($html)
        ->toContain('$80,000 Salary After Tax in Ontario')
        ->toContain('id="direct-answer-heading"')
        ->toContain('How much is an $80,000 salary after tax in Ontario?')
        ->toContain('$60,303.09')
        ->toContain('Annual net')
        ->toContain('Monthly net')
        ->toContain('Biweekly net')
        ->toContain('Weekly net')
        ->toContain('Federal tax')
        ->toContain('Ontario tax')
        ->toContain('CPP / CPP2')
        ->toContain('What is $80,000 per month after tax?')
        ->toContain('What is $80,000 biweekly after tax?')
        ->toContain('What deductions come off this salary?')
        ->toContain('Methodology')
        ->toContain(route('methodology'));

    expect(strpos($html, '$80,000 Salary After Tax in Ontario'))
        ->toBeLessThan(strpos($html, 'id="direct-answer-heading"'));
});

it('exposes a direct answer and example take-home on the Ontario paycheck page', function () {
    $this->get('/ontario-paycheck-calculator')
        ->assertOk()
        ->assertSee('id="direct-answer-heading"', false)
        ->assertSee('How is take-home pay calculated in Ontario?', false)
        ->assertSee('$60,303.09', false)
        ->assertSee('Methodology', false)
        ->assertSee(route('paycheck.province', 'ontario'), false)
        ->assertSee('rel="canonical"', false);
});

it('exposes overtime threshold, multiplier, source, and after-tax example', function () {
    $example = app(OvertimePayEstimator::class)->estimate(
        Province::Ontario,
        Money::fromDollars('30'),
        40,
        8,
        PayFrequency::Weekly,
    );

    $this->get('/ontario-overtime-pay-calculator')
        ->assertOk()
        ->assertSee('How much overtime pay do I keep after tax in Ontario?', false)
        ->assertSee('44 hours', false)
        ->assertSee('1.5×', false)
        ->assertSee('No general daily threshold', false)
        ->assertSee('https://www.ontario.ca/document/your-guide-employment-standards-act-0/overtime-pay', false)
        ->assertSee($example['after_tax_overtime']->format(), false)
        ->assertSee($example['overtime_pay']->format(), false)
        ->assertSee('Methodology', false)
        ->assertSee(route('tools.overtime.province', 'ontario'), false);
});

it('exposes estimated net bonus on the bonus calculator', function () {
    $example = app(PayrollComparison::class)->compare(
        Province::Ontario,
        Money::fromDollars(80000),
        Money::fromDollars(90000),
    );

    $this->get('/bonus-tax-calculator')
        ->assertOk()
        ->assertSee('How much of a $10,000 bonus do I keep after tax in Canada?', false)
        ->assertSee('Estimated net bonus', false)
        ->assertSee($example['net_annual_delta']->format(), false)
        ->assertSee($example['keep_percent'].'%', false)
        ->assertSee('Employer withholding may differ', false)
        ->assertSee('Methodology', false);
});

it('exposes estimated net raise on the raise calculator', function () {
    $example = app(PayrollComparison::class)->compare(
        Province::Ontario,
        Money::fromDollars(75000),
        Money::fromDollars(85000),
    );

    $this->get('/raise-calculator')
        ->assertOk()
        ->assertSee('How much of a $10,000 raise do I actually keep in Canada?', false)
        ->assertSee($example['net_annual_delta']->format(), false)
        ->assertSee($example['net_annual_delta']->divideBy(12)->format(), false)
        ->assertSee($example['net_annual_delta']->divideBy(26)->format(), false)
        ->assertSee($example['keep_percent'].'%', false)
        ->assertSee('Methodology', false);
});

it('exposes CAF pay-table date, exclusions, and a computed take-home example', function () {
    $example = app(MilitarySalaryCalculator::class)->calculate(
        'regular',
        'corporal',
        '1',
        Province::Ontario,
        PayFrequency::Monthly,
    );

    $this->get('/military-salary-calculator')
        ->assertOk()
        ->assertSee('How much does a Regular Force Corporal earn and take home?', false)
        ->assertSee('1 April 2025', false)
        ->assertSee('April 1, 2025', false)
        ->assertSee('Allowances included', false)
        ->assertSee('No, unless entered', false)
        ->assertSee($example['pay']['rate']->format(), false)
        ->assertSee($example['payroll']->metrics['net_annual']->format(), false)
        ->assertSee('Methodology', false)
        ->assertSee('https://www.canada.ca/en/department-national-defence/services/benefits-military/pay-pension-benefits/pay/regular.html', false);
});

it('keeps salary-page canonicals and does not add thin question URLs', function () {
    $this->get('/ontario/80000-salary')
        ->assertOk()
        ->assertSee('rel="canonical"', false)
        ->assertSee(route('paycheck.salary', ['ontario', 80000]), false)
        ->assertDontSee('noindex', false);

    $this->get('/how-much-is-80000-after-tax-ontario')->assertNotFound();
    $this->get('/how-much-is-80000-monthly-ontario')->assertNotFound();
});

it('keeps the sitemap on existing calculator URLs only', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('ontario/80000-salary', false)
        ->assertSee('ontario-paycheck-calculator', false)
        ->assertSee('ontario-overtime-pay-calculator', false)
        ->assertSee('bonus-tax-calculator', false)
        ->assertSee('raise-calculator', false)
        ->assertSee('military-salary-calculator', false)
        ->assertSee('methodology', false)
        ->assertDontSee('how-much-is-80000', false)
        ->assertDontSee('salary-increase-calculator', false)
        ->assertDontSee('caf-salary-calculator', false);
});

it('emits WebPage structured data that matches visible content and no review markup', function () {
    $response = $this->get('/ontario/80000-salary')->assertOk();
    $html = $response->getContent();

    expect($html)
        ->toContain('application/ld+json')
        ->toContain('"@type":"WebPage"')
        ->toContain('"@type":"FAQPage"')
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('How much is $80,000 after tax in Ontario?')
        ->not->toContain('"@type":"Review"')
        ->not->toContain('AggregateRating');

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $json) {
        expect(json_decode($json, true))->toBeArray();
        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    }
});

it('does not store exact financial amounts in analytics events', function () {
    $this->get('/ontario/80000-salary')->assertOk();
    $this->get('/bonus-tax-calculator')->assertOk();
    $this->get('/military-salary-calculator')->assertOk();

    $events = AnalyticsEvent::query()->get();

    expect($events)->not->toBeEmpty();

    foreach ($events as $event) {
        expect($event->getAttributes())->not->toHaveKey('annual_salary_cents');
        expect($event->salary_range === null || ! str_contains((string) $event->salary_range, '60303'))->toBeTrue();
        expect((string) $event->salary_range)->not->toContain('80000');
        expect((string) $event->tool_key)->not->toContain('$');
    }

    $salaryEvent = $events->firstWhere('path', 'ontario/80000-salary');
    expect($salaryEvent?->salary_range)->toBe('80_90k');
});
