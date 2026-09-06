<?php

use App\Livewire\MilitarySalaryCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the CAF salary calculator with SEO tags', function () {
    $this->get('/military-salary-calculator')
        ->assertOk()
        ->assertSee('Canadian Armed Forces Salary Calculator', false)
        ->assertSee('<title>Canadian Armed Forces Salary Calculator 2026', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee(route('tools.military'), false)
        ->assertSee('Estimate CAF take-home', false)
        ->assertSee('Allowances and benefits are not included unless entered manually.', false);
});

it('redirects alternate CAF URLs to the canonical calculator', function () {
    $this->get('/caf-salary-calculator')->assertRedirect('/military-salary-calculator');
    $this->get('/canadian-military-pay-calculator')->assertRedirect('/military-salary-calculator');
});

it('includes the military calculator in the sitemap and omits aliases', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('military-salary-calculator', false)
        ->assertDontSee('caf-salary-calculator', false)
        ->assertDontSee('canadian-military-pay-calculator', false);
});

it('explains CAF pay methodology', function () {
    $this->get('/methodology')
        ->assertOk()
        ->assertSee('Canadian Armed Forces salary calculator', false)
        ->assertSee('1 April 2025', false)
        ->assertSee('Reserve Force pension is omitted', false);
});

it('calculates Regular Force take-home through Livewire', function () {
    Livewire::test(MilitarySalaryCalculator::class)
        ->set('component', 'regular')
        ->set('rank', 'private')
        ->set('increment', '1')
        ->set('province', 'ON')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$52,044.00')
        ->assertSee('Private / Aviator')
        ->assertSee('Pay increment 1');
});

it('calculates a Reserve Force example through Livewire', function () {
    Livewire::test(MilitarySalaryCalculator::class)
        ->set('component', 'reserve')
        ->set('rank', 'corporal')
        ->set('increment', 'basic')
        ->set('reserveDays', '37')
        ->set('province', 'QC')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('$7,743.36')
        ->assertSee('Reserve Force (Class A / B)');
});
