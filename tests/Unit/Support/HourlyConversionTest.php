<?php

use App\Support\HourlyConversion;
use App\Support\Money;

it('converts hourly wages to annual salary using the configured hours', function () {
    $annual = HourlyConversion::annualFromHourly(Money::fromDollars('40.00'), 40);

    expect($annual->dollars())->toBe('83200.00')
        ->and(HourlyConversion::hoursPerYear())->toBe(2080);
});

it('converts annual salary to an approximate hourly wage', function () {
    $hourly = HourlyConversion::hourlyFromAnnual(Money::fromDollars('80000.00'), 40);

    expect($hourly->dollars())->toBe('38.46');
});

it('states the hour assumption used on salary pages', function () {
    expect(HourlyConversion::assumptionLabel())->toContain('40 hours/week')
        ->and(HourlyConversion::assumptionLabel())->toContain('2080');
});
