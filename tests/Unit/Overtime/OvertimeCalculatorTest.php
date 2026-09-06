<?php

use App\Services\Overtime\OvertimeCalculator;
use App\Services\Overtime\OvertimePayEstimator;
use App\Services\Overtime\OvertimeRuleProvider;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;

function overtime(): OvertimeCalculator
{
    return app(OvertimeCalculator::class);
}

it('calculates Ontario standard overtime at 1.5× the regular wage', function () {
    $result = overtime()->gross(Province::Ontario, Money::fromDollars(30), 40, 8);

    expect($result['regular_pay']->dollars())->toBe('1200.00')
        ->and($result['overtime_rate']->dollars())->toBe('45.00')
        ->and($result['overtime_pay']->dollars())->toBe('360.00')
        ->and($result['total_gross']->dollars())->toBe('1560.00')
        ->and($result['rules']['weekly_threshold'])->toBe(44)
        ->and($result['rules']['daily_threshold'])->toBeNull();
});

it('uses Alberta 8/44 rules and a 1.5× regular premium', function () {
    $rules = app(OvertimeRuleProvider::class)->for(Province::Alberta);
    $result = overtime()->gross(Province::Alberta, Money::fromDollars(25), 40, 6);

    expect($rules['weekly_threshold'])->toBe(44)
        ->and($rules['daily_threshold'])->toBe(8)
        ->and($result['overtime_rate']->dollars())->toBe('37.50')
        ->and($result['overtime_pay']->dollars())->toBe('225.00');
});

it('pays British Columbia daily overtime at 1.5× and double time at 2×', function () {
    $rules = app(OvertimeRuleProvider::class)->for(Province::BritishColumbia);
    $result = overtime()->gross(Province::BritishColumbia, Money::fromDollars(20), 40, 4, 2);

    expect($rules['weekly_threshold'])->toBe(40)
        ->and($rules['daily_threshold'])->toBe(8)
        ->and($rules['daily_double_threshold'])->toBe(12)
        ->and($result['overtime_rate']->dollars())->toBe('30.00')
        ->and($result['double_rate']->dollars())->toBe('40.00')
        ->and($result['overtime_pay']->dollars())->toBe('120.00')
        ->and($result['double_pay']->dollars())->toBe('80.00')
        ->and($result['total_gross']->dollars())->toBe('1000.00');
});

it('uses a 40-hour Quebec overtime week at 1.5×', function () {
    $rules = app(OvertimeRuleProvider::class)->for(Province::Quebec);
    $result = overtime()->gross(Province::Quebec, Money::fromDollars(30), 40, 5);

    expect($rules['weekly_threshold'])->toBe(40)
        ->and($rules['daily_threshold'])->toBeNull()
        ->and($result['overtime_rate']->dollars())->toBe('45.00')
        ->and($result['overtime_pay']->dollars())->toBe('225.00');
});

it('models a jurisdiction with daily overtime rules', function () {
    $rules = app(OvertimeRuleProvider::class)->for(Province::Manitoba);

    expect($rules['weekly_threshold'])->toBe(40)
        ->and($rules['daily_threshold'])->toBe(8)
        ->and($rules['multiplier'])->toBe('1.5');
});

it('returns only regular pay when overtime hours are zero', function () {
    $result = overtime()->gross(Province::Ontario, Money::fromDollars(30), 40, 0);

    expect($result['overtime_pay']->isZero())->toBeTrue()
        ->and($result['total_gross']->dollars())->toBe('1200.00');
});

it('splits weekly hours at the overtime threshold and above it', function () {
    $at = overtime()->splitWeeklyHours(Province::Ontario, 44);
    $over = overtime()->splitWeeklyHours(Province::Ontario, 52);

    expect($at['regular_hours'])->toBe(44.0)
        ->and($at['overtime_hours'])->toBe(0.0)
        ->and($over['regular_hours'])->toBe(44.0)
        ->and($over['overtime_hours'])->toBe(8.0);
});

it('scales overtime pay with the hourly rate', function () {
    $low = overtime()->gross(Province::Ontario, Money::fromDollars(20), 40, 8);
    $high = overtime()->gross(Province::Ontario, Money::fromDollars(40), 40, 8);

    expect($low['overtime_pay']->dollars())->toBe('240.00')
        ->and($high['overtime_pay']->dollars())->toBe('480.00');
});

it('uses the New Brunswick statutory overtime floor unless a contract premium is selected', function () {
    $statutory = overtime()->gross(Province::NewBrunswick, Money::fromDollars(30), 40, 8);
    $contract = overtime()->gross(Province::NewBrunswick, Money::fromDollars(30), 40, 8, useContractPremium: true);

    expect($statutory['overtime_rate']->dollars())->toBe('30.00')
        ->and($contract['overtime_rate']->dollars())->toBe('45.00');
});

it('compares after-tax overtime with and without the overtime amount', function () {
    $estimate = app(OvertimePayEstimator::class)->estimate(
        Province::Ontario,
        Money::fromDollars(30),
        40,
        8,
        PayFrequency::Weekly,
    );

    $comparison = $estimate['comparison'];

    expect($estimate['regular_pay']->dollars())->toBe('1200.00')
        ->and($estimate['overtime_pay']->dollars())->toBe('360.00')
        ->and($comparison['net_annual_delta']->cents)->toBe(
            $comparison['modified']->metrics['net_annual']->cents
            - $comparison['baseline']->metrics['net_annual']->cents
        )
        ->and($estimate['after_tax_overtime']->cents)->toBe(
            $estimate['period_net']->cents - $comparison['baseline']->metrics['net_annual']->divideBy(52)->cents
        );
});

it('rejects negative overtime inputs', function () {
    overtime()->gross(Province::Ontario, Money::fromDollars(30), 40, -1);
})->throws(InvalidArgumentException::class);

it('stores an official source for every jurisdiction', function () {
    $provider = app(OvertimeRuleProvider::class);

    foreach (Province::all() as $province) {
        $rules = $provider->for($province);

        expect($rules['source']['url'])->toStartWith('https://')
            ->and($rules['weekly_threshold'])->toBeGreaterThan(0)
            ->and($rules['multiplier'])->not->toBeEmpty();
    }
});
