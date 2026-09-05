<?php

use App\Services\Tax\FederalTaxCalculator;
use App\Services\Tax\TaxRuleProvider;
use App\Support\Money;
use App\Support\Province;

beforeEach(function () {
    $this->federal = app(TaxRuleProvider::class)->forYear(2026)->federal;
    $this->calculator = app(FederalTaxCalculator::class);
});

it('uses the maximum federal BPA below the phase-out', function () {
    expect($this->calculator->basicPersonalAmount(Money::fromDollars('80000.00'), $this->federal)->dollars())
        ->toBe('16452.00');
});

it('uses the minimum federal BPA at and above the top threshold', function () {
    expect($this->calculator->basicPersonalAmount(Money::fromDollars('258482.00'), $this->federal)->dollars())
        ->toBe('14829.00')
        ->and($this->calculator->basicPersonalAmount(Money::fromDollars('400000.00'), $this->federal)->dollars())
        ->toBe('14829.00');
});

it('phases out the federal BPA between the 29% and 33% thresholds', function () {
    $mid = Money::fromDollars('219961.00');
    $amount = $this->calculator->basicPersonalAmount($mid, $this->federal);

    expect($amount->cents)->toBeGreaterThan(1482900)
        ->and($amount->cents)->toBeLessThan(1645200);
});

it('returns zero federal tax on zero income', function () {
    $result = $this->calculator->annual(
        Money::zero(),
        Money::zero(),
        Money::zero(),
        Money::zero(),
        Money::zero(),
        Province::NewBrunswick,
        $this->federal,
    );

    expect($result['t1']->isZero())->toBeTrue();
});

it('applies the 14% federal bracket on $50,000 NB taxable income', function () {
    $result = $this->calculator->annual(
        Money::fromDollars('49535.00'),
        Money::fromDollars('50000.00'),
        Money::fromDollars('2301.75'),
        Money::fromDollars('815.00'),
        Money::zero(),
        Province::NewBrunswick,
        $this->federal,
    );

    expect($result['bracket_rate'])->toBe('0.1400')
        ->and($result['k1']->dollars())->toBe('2303.28')
        ->and($result['k4']->dollars())->toBe('210.14')
        ->and($result['t1']->dollars())->toBe('3985.13');
});

it('applies the Quebec abatement to basic federal tax', function () {
    $outside = $this->calculator->annual(
        Money::fromDollars('50000.00'),
        Money::fromDollars('50000.00'),
        Money::fromDollars('2301.75'),
        Money::fromDollars('815.00'),
        Money::zero(),
        Province::Ontario,
        $this->federal,
    );

    $quebec = $this->calculator->annual(
        Money::fromDollars('50000.00'),
        Money::fromDollars('50000.00'),
        Money::fromDollars('2301.75'),
        Money::fromDollars('815.00'),
        Money::zero(),
        Province::Quebec,
        $this->federal,
    );

    expect($quebec['t3']->equals($outside['t3']))->toBeTrue()
        ->and($quebec['t1']->cents)->toBe($outside['t3']->subtract($outside['t3']->multiply('0.165'))->cents)
        ->and($quebec['t1']->lessThan($outside['t1']))->toBeTrue();
});
