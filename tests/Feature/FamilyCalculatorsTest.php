<?php

use App\Livewire\BabyCostCalculator;
use App\Livewire\EiMaternityParentalCalculator;
use App\Livewire\ParentalLeaveCalculator;
use App\Models\AnalyticsEvent;
use App\Services\Family\BabyBudgetService;
use App\Services\Family\ParentalBenefitsService;
use App\Services\Family\ParentalLeaveProjectionService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the parental leave calculator with SEO tags', function () {
    $example = app(ParentalLeaveProjectionService::class)->project([
        'province' => 'ON',
        'salary' => 80000,
        'frequency' => 'biweekly',
        'leave_type' => 'maternity_standard',
        'planned_weeks' => 50,
    ]);

    $this->get('/parental-leave-calculator')
        ->assertOk()
        ->assertSee('Canadian Parental Leave Calculator', false)
        ->assertSee('<title>Parental Leave Calculator Canada 2026', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee(route('tools.parental'), false)
        ->assertSee('id="direct-answer-heading"', false)
        ->assertSee('How much will I make on parental leave in Canada?', false)
        ->assertSee($example['weekly_ei']->format(), false)
        ->assertSee($example['ei_total']->format(), false)
        ->assertSee('Estimate my leave income', false)
        ->assertSee('EI maternity and parental benefits, 2026', false);
});

it('redirects the maternity-leave alias to the parental leave calculator', function () {
    $this->get('/maternity-leave-calculator')
        ->assertRedirect('/parental-leave-calculator');
});

it('renders the EI benefits calculator with 2026 maxima', function () {
    $this->get('/ei-maternity-parental-benefits')
        ->assertOk()
        ->assertSee('EI Maternity & Parental Benefits Calculator', false)
        ->assertSee('<title>EI Maternity and Parental Benefits Calculator 2026', false)
        ->assertSee(route('tools.ei_benefits'), false)
        ->assertSee('What is the maximum EI maternity benefit in 2026?', false)
        ->assertSee('$729', false)
        ->assertSee('$437', false)
        ->assertSee('Rates verified for 2026', false)
        ->assertSee('https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental.html', false)
        ->assertSee('Want to see what this means for your actual household budget?', false);
});

it('redirects the EI alias to the canonical benefits page', function () {
    $this->get('/ei-parental-benefits')
        ->assertRedirect('/ei-maternity-parental-benefits');
});

it('renders the baby cost calculator without a single fabricated total', function () {
    $example = app(BabyBudgetService::class)->summarize(
        app(BabyBudgetService::class)->defaultStartupState(),
        app(BabyBudgetService::class)->defaultRecurringState(),
    );

    $this->get('/baby-cost-calculator')
        ->assertOk()
        ->assertSee('Canadian Baby Cost Calculator', false)
        ->assertSee('<title>Baby Cost Calculator Canada 2026', false)
        ->assertSee(route('tools.baby'), false)
        ->assertSee('How much does a baby cost in Canada?', false)
        ->assertSee($example['remaining_purchases']->format(), false)
        ->assertSee('CRA Child and Family Benefits Calculator', false)
        ->assertSee('Expecting your income to change during leave?', false)
        ->assertDontSee('A baby costs $', false);
});

it('includes family calculator URLs in the sitemap and omits aliases', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('parental-leave-calculator', false)
        ->assertSee('ei-maternity-parental-benefits', false)
        ->assertSee('baby-cost-calculator', false)
        ->assertDontSee('maternity-leave-calculator', false)
        ->assertDontSee('ei-parental-benefits', false);
});

it('explains family calculator methodology', function () {
    $this->get('/methodology')
        ->assertOk()
        ->assertSee('Parental leave calculator', false)
        ->assertSee('EI maternity and parental benefits', false)
        ->assertSee('Baby cost calculator', false)
        ->assertSee('config/benefits.php', false)
        ->assertSee('config/baby-budget.php', false);
});

it('caps an $80,000 parental-leave estimate at the 2026 weekly maximum', function () {
    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->set('leaveType', 'maternity_standard')
        ->set('plannedWeeks', '50')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$729.00')
        ->assertSee('$36,450.00')
        ->assertSee('Your estimated weekly benefit');
});

it('estimates parental leave below the weekly maximum', function () {
    $weekly = app(ParentalBenefitsService::class)
        ->weeklyBenefit(Money::fromDollars(40000), 'standard_parental');

    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '40000')
        ->set('leaveType', 'standard')
        ->set('plannedWeeks', '20')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee($weekly->format())
        ->assertDontSee('The 2026 weekly maximum was applied.')
        ->assertSee('Your estimated weekly benefit');
});

it('does not apply federal EI to Québec parental leave', function () {
    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'QC')
        ->set('salary', '80000')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('QPIP')
        ->assertDontSee('Your estimated weekly benefit');
});

it('adds an employer top-up to parental leave income', function () {
    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->set('topUpEnabled', true)
        ->set('topUpPercent', '80')
        ->set('topUpWeeks', '17')
        ->set('topUpMode', 'to_percent')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('Employer top-up')
        ->assertSee('Combined weekly EI + top-up');
});

it('keeps a parental leave estimate without a top-up', function () {
    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->set('topUpEnabled', false)
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertDontSee('Top-up per week')
        ->assertDontSee('Combined weekly EI + top-up');
});

it('caps maternity weeks on the EI calculator', function () {
    Livewire::test(EiMaternityParentalCalculator::class)
        ->set('province', 'ON')
        ->set('program', 'maternity')
        ->set('amount', '80000')
        ->set('weeks', '20')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$729.00')
        ->assertSee('$10,935.00')
        ->assertSee('15 / 15');
});

it('caps shared standard parental weeks', function () {
    Livewire::test(EiMaternityParentalCalculator::class)
        ->set('province', 'ON')
        ->set('program', 'standard_parental')
        ->set('amount', '80000')
        ->set('sharing', true)
        ->set('parentA', '25')
        ->set('parentB', '20')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('share at most 40');
});

it('caps extended parental weeks at 61 for one parent', function () {
    Livewire::test(EiMaternityParentalCalculator::class)
        ->set('province', 'ON')
        ->set('program', 'extended_parental')
        ->set('amount', '80000')
        ->set('weeks', '70')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$437.00')
        ->assertSee('61 / 61');
});

it('does not invent federal EI for Québec on the EI calculator', function () {
    Livewire::test(EiMaternityParentalCalculator::class)
        ->set('province', 'QC')
        ->set('amount', '80000')
        ->call('calculate')
        ->assertSee('QPIP')
        ->assertDontSee('Estimated weekly benefit');
});

it('shows the parental planner CTA after results when a URL is configured', function () {
    config(['external-links.etsy.parental_leave_planner' => 'https://www.etsy.com/listing/example-parental']);

    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->call('calculate')
        ->assertSee('Plan My Parental Leave Budget')
        ->assertSee('Available on Etsy')
        ->assertSee('https://www.etsy.com/listing/example-parental');
});

it('calculates baby startup, gifts, used items, and a funding gap', function () {
    $component = Livewire::test(BabyCostCalculator::class);

    $startup = $component->get('startup');
    $startup[0]['planned'] = '400';
    $startup[0]['purchased'] = true;
    $startup[0]['actual'] = '350';
    $startup[1]['planned'] = '150';
    $startup[1]['gift'] = true;
    $startup[2]['planned'] = '80';
    $startup[2]['used'] = true;
    $startup[2]['used_cost'] = '30';

    foreach (array_keys($startup) as $index) {
        if ($index > 2) {
            $startup[$index]['skip'] = true;
        }
    }

    $component
        ->set('startup', $startup)
        ->set('recurring', [['id' => 'diapers', 'label' => 'Diapers', 'monthly' => '80']])
        ->set('childcareNeeded', true)
        ->set('childcareStartMonth', '7')
        ->set('childcareMonthly', '1200')
        ->set('childcareSubsidy', '200')
        ->set('currentSavings', '500')
        ->set('monthlySaved', '100')
        ->set('monthsUntil', '4')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('Estimated amount left to prepare')
        ->assertSee('$6,000.00');
});

it('carries a leave-income reduction into the baby calculator without putting it in the URL', function () {
    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->call('calculate')
        ->call('continueToBabyBudget')
        ->assertRedirect('/baby-cost-calculator');

    expect(session('family.monthly_leave_reduction'))->not->toBeEmpty()
        ->and(session('family.monthly_leave_reduction'))->not->toContain('$');
});

it('does not store exact family-finance amounts in analytics', function () {
    Livewire::test(ParentalLeaveCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->call('calculate');

    Livewire::test(EiMaternityParentalCalculator::class)
        ->set('province', 'ON')
        ->set('amount', '80000')
        ->call('calculate');

    Livewire::test(BabyCostCalculator::class)->call('calculate');

    $events = AnalyticsEvent::query()->get();

    expect($events->pluck('name')->all())->toContain(
        'parental_leave_calculation_completed',
        'ei_benefit_calculation_completed',
        'baby_cost_calculation_completed',
    );

    foreach ($events as $event) {
        expect($event->getAttributes())->not->toHaveKey('annual_salary_cents');
        expect((string) $event->salary_range)->not->toContain('80000');
        expect((string) $event->salary_range)->not->toContain('729');
        expect($event->path ?? '')->not->toContain('80000');
    }

    $parental = $events->firstWhere('name', 'parental_leave_calculation_completed');
    expect($parental?->salary_range)->toBe('80_90k')
        ->and($parental?->province)->toBe('ON')
        ->and($parental?->frequency)->toBe('standard');
});
