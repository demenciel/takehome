<?php

use App\Livewire\BonusTaxCalculator;
use App\Livewire\OvertimePayCalculator;
use App\Livewire\RaiseCalculator;
use App\Support\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the overtime pay calculator with SEO tags', function () {
    $this->get('/overtime-pay-calculator')
        ->assertOk()
        ->assertSee('Canadian Overtime Pay Calculator', false)
        ->assertSee('<title>Canadian Overtime Pay Calculator 2026', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee(route('tools.overtime'), false)
        ->assertSee('Estimate overtime pay', false)
        ->assertSee('How much of my overtime do I actually keep?', false);
});

it('renders the bonus tax calculator with SEO tags', function () {
    $this->get('/bonus-tax-calculator')
        ->assertOk()
        ->assertSee('Bonus Tax Calculator', false)
        ->assertSee('<title>Bonus Tax Calculator Canada 2026', false)
        ->assertSee(route('tools.bonus'), false)
        ->assertSee('Estimate bonus tax', false);
});

it('renders the raise calculator with SEO tags', function () {
    $this->get('/raise-calculator')
        ->assertOk()
        ->assertSee('Raise Calculator', false)
        ->assertSee('<title>Raise Calculator Canada', false)
        ->assertSee(route('tools.raise'), false)
        ->assertSee('Estimate my raise', false);
});

it('redirects the salary-increase alias to the canonical raise calculator', function () {
    $this->get('/salary-increase-calculator')
        ->assertRedirect('/raise-calculator');
});

it('renders every province overtime page with the jurisdiction preset', function (Province $province) {
    $this->get('/'.$province->slug().'-overtime-pay-calculator')
        ->assertOk()
        ->assertSee($province->name().' Overtime Pay Calculator', false)
        ->assertSee('<title>'.$province->name().' Overtime Pay Calculator 2026', false)
        ->assertSee(route('tools.overtime.province', $province->slug()), false);
})->with(Province::all());

it('renders every province bonus page', function (Province $province) {
    $this->get('/'.$province->slug().'-bonus-tax-calculator')
        ->assertOk()
        ->assertSee($province->name().' Bonus Tax Calculator', false)
        ->assertSee(route('tools.bonus.province', $province->slug()), false);
})->with(Province::all());

it('renders every province raise page with a worked example', function (Province $province) {
    $this->get('/'.$province->slug().'-raise-calculator')
        ->assertOk()
        ->assertSee($province->name().' Raise Calculator', false)
        ->assertSee('$75,000 to $85,000', false)
        ->assertSee(route('tools.raise.province', $province->slug()), false);
})->with(Province::all());

it('redirects overtime alias slugs to the canonical province URL', function () {
    $this->get('/pei-overtime-pay-calculator')
        ->assertRedirect('/prince-edward-island-overtime-pay-calculator');
});

it('includes the new calculator URLs in the sitemap and omits the raise alias', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('overtime-pay-calculator', false)
        ->assertSee('bonus-tax-calculator', false)
        ->assertSee('raise-calculator', false)
        ->assertSee('ontario-overtime-pay-calculator', false)
        ->assertSee('quebec-bonus-tax-calculator', false)
        ->assertSee('alberta-raise-calculator', false)
        ->assertSee('british-columbia-overtime-pay-calculator', false)
        ->assertDontSee('salary-increase-calculator', false);
});

it('explains overtime, bonus, and raise methodology', function () {
    $this->get('/methodology')
        ->assertOk()
        ->assertSee('Overtime pay calculator', false)
        ->assertSee('Bonus tax calculator', false)
        ->assertSee('Raise calculator', false)
        ->assertSee('resources/employment/overtime.php', false);
});

it('calculates overtime through Livewire', function () {
    Livewire::test(OvertimePayCalculator::class)
        ->set('province', 'ON')
        ->set('hourlyWage', '30')
        ->set('regularHours', '40')
        ->set('overtimeHours', '8')
        ->set('frequency', 'weekly')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$1,200.00')
        ->assertSee('$360.00')
        ->assertSee('$1,560.00')
        ->assertSee('Estimated after-tax value of your overtime');
});

it('rejects negative overtime hours', function () {
    Livewire::test(OvertimePayCalculator::class)
        ->set('province', 'ON')
        ->set('hourlyWage', '30')
        ->set('regularHours', '40')
        ->set('overtimeHours', '-2')
        ->call('calculate')
        ->assertHasErrors(['overtimeHours']);
});

it('calculates a bonus through Livewire as net(salary + bonus) minus net(salary)', function () {
    Livewire::test(BonusTaxCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->set('bonus', '10000')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('Estimated net bonus')
        ->assertSee('$10,000.00');
});

it('rejects a negative bonus', function () {
    Livewire::test(BonusTaxCalculator::class)
        ->set('province', 'ON')
        ->set('salary', '80000')
        ->set('bonus', '-1000')
        ->call('calculate')
        ->assertHasErrors(['bonus']);
});

it('calculates a raise through Livewire', function () {
    Livewire::test(RaiseCalculator::class)
        ->set('province', 'ON')
        ->set('currentSalary', '75000')
        ->set('newSalary', '85000')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$10,000.00')
        ->assertSee('Before and after');
});

it('accepts a percentage raise', function () {
    Livewire::test(RaiseCalculator::class)
        ->set('province', 'ON')
        ->set('inputMode', 'percent')
        ->set('currentSalary', '75000')
        ->set('raisePercent', '10')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$7,500.00');
});

it('rejects a new salary lower than the current salary', function () {
    Livewire::test(RaiseCalculator::class)
        ->set('province', 'ON')
        ->set('currentSalary', '75000')
        ->set('newSalary', '70000')
        ->call('calculate')
        ->assertHasErrors(['newSalary']);
});

it('presets the province on a specialized calculator page', function () {
    Livewire::test(OvertimePayCalculator::class, ['province' => 'QC'])
        ->assertSet('province', 'QC');

    Livewire::test(BonusTaxCalculator::class, ['province' => 'AB'])
        ->assertSet('province', 'AB');

    Livewire::test(RaiseCalculator::class, ['province' => 'BC'])
        ->assertSet('province', 'BC');
});
