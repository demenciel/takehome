<?php

use App\Services\Tax\CppCalculator;
use App\Services\Tax\EiCalculator;
use App\Services\Tax\QpipCalculator;
use App\Services\Tax\QppCalculator;
use App\Services\Tax\TaxRuleProvider;
use App\Support\Money;
use App\Support\Province;

beforeEach(function () {
    $this->rules = app(TaxRuleProvider::class)->forYear(2026);
});

it('calculates 2026 CPP below the YMPE', function () {
    $result = app(CppCalculator::class)->annual(Money::fromDollars('50000.00'), $this->rules->cpp);

    expect($result['contribution']->dollars())->toBe('2766.75')
        ->and($result['second_additional']->isZero())->toBeTrue()
        ->and($result['base']->dollars())->toBe('2301.75');
});

it('caps 2026 CPP at the published maximum', function () {
    $atYmpe = app(CppCalculator::class)->annual(Money::fromDollars('74600.00'), $this->rules->cpp);
    $above = app(CppCalculator::class)->annual(Money::fromDollars('120000.00'), $this->rules->cpp);

    expect($atYmpe['contribution']->dollars())->toBe('4230.45')
        ->and($above['contribution']->dollars())->toBe('4230.45');
});

it('calculates 2026 CPP2 between YMPE and YAMPE', function () {
    $partial = app(CppCalculator::class)->annual(Money::fromDollars('80000.00'), $this->rules->cpp);
    $max = app(CppCalculator::class)->annual(Money::fromDollars('85000.00'), $this->rules->cpp);
    $above = app(CppCalculator::class)->annual(Money::fromDollars('200000.00'), $this->rules->cpp);

    expect($partial['second_additional']->dollars())->toBe('216.00')
        ->and($max['second_additional']->dollars())->toBe('416.00')
        ->and($above['second_additional']->dollars())->toBe('416.00');
});

it('returns zero CPP on zero and sub-exemption income', function () {
    $zero = app(CppCalculator::class)->annual(Money::zero(), $this->rules->cpp);
    $low = app(CppCalculator::class)->annual(Money::fromDollars('3000.00'), $this->rules->cpp);

    expect($zero['contribution']->isZero())->toBeTrue()
        ->and($low['contribution']->isZero())->toBeTrue();
});

it('calculates 2026 QPP at a higher employee rate than CPP', function () {
    $result = app(QppCalculator::class)->annual(Money::fromDollars('50000.00'), $this->rules->qpp);

    expect($result['contribution']->dollars())->toBe('2929.50')
        ->and($result['base']->dollars())->toBe('2464.50');
});

it('caps 2026 QPP at the published maximum', function () {
    $result = app(QppCalculator::class)->annual(Money::fromDollars('74600.00'), $this->rules->qpp);

    expect($result['contribution']->dollars())->toBe('4479.30');
});

it('calculates 2026 EI and the Quebec reduced rate', function () {
    $ei = app(EiCalculator::class);

    expect($ei->annual(Money::fromDollars('50000.00'), Province::NewBrunswick, $this->rules->ei)->dollars())->toBe('815.00')
        ->and($ei->annual(Money::fromDollars('68900.00'), Province::Ontario, $this->rules->ei)->dollars())->toBe('1123.07')
        ->and($ei->annual(Money::fromDollars('80000.00'), Province::Ontario, $this->rules->ei)->dollars())->toBe('1123.07')
        ->and($ei->annual(Money::fromDollars('68900.00'), Province::Quebec, $this->rules->ei)->dollars())->toBe('895.70')
        ->and($ei->annual(Money::zero(), Province::Ontario, $this->rules->ei)->isZero())->toBeTrue();
});

it('calculates 2026 QPIP only for Quebec', function () {
    $qpip = app(QpipCalculator::class);

    expect($qpip->annual(Money::fromDollars('75000.00'), Province::Quebec, $this->rules->qpip)->dollars())->toBe('322.50')
        ->and($qpip->annual(Money::fromDollars('120000.00'), Province::Quebec, $this->rules->qpip)->dollars())->toBe('442.90')
        ->and($qpip->annual(Money::fromDollars('75000.00'), Province::Ontario, $this->rules->qpip)->isZero())->toBeTrue();
});
