<?php

use App\Calculators\Payroll\PayrollCalculator;
use App\Services\Military\CafPensionCalculator;
use App\Services\Military\MilitaryPayRateProvider;
use App\Services\Military\MilitarySalaryCalculator;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;

function military(): MilitarySalaryCalculator
{
    return app(MilitarySalaryCalculator::class);
}

function militaryPay(): MilitaryPayRateProvider
{
    return app(MilitaryPayRateProvider::class);
}

it('looks up a junior Regular Force NCM rate from the official table', function () {
    $pay = militaryPay()->lookup('regular', 'private', '1');

    expect($pay['rate']->dollars())->toBe('4337.00')
        ->and($pay['unit'])->toBe('monthly')
        ->and($pay['rank_short'])->toBe('Private');

    $result = military()->calculate('regular', 'private', '1', Province::Ontario, PayFrequency::Semimonthly);

    expect($result['base_annual']->dollars())->toBe('52044.00')
        ->and($result['allowances']->isZero())->toBeTrue();
});

it('looks up a senior Regular Force NCM increment', function () {
    $pay = militaryPay()->lookup('regular', 'sergeant', '4');

    expect($pay['rate']->dollars())->toBe('8340.00');

    $result = military()->calculate('regular', 'sergeant', '4', Province::Alberta, PayFrequency::Monthly);

    expect($result['base_annual']->dollars())->toBe('100080.00');
});

it('looks up an officer rank and a later pay increment', function () {
    $basic = militaryPay()->lookup('regular', 'captain', 'basic');
    $step = militaryPay()->lookup('regular', 'captain', '5');

    expect($basic['rate']->dollars())->toBe('8861.00')
        ->and($step['rate']->dollars())->toBe('10513.00');
});

it('looks up Reserve Force daily rates without converting from monthly pay', function () {
    $pay = militaryPay()->lookup('reserve', 'corporal', 'basic');

    expect($pay['unit'])->toBe('daily')
        ->and($pay['rate']->dollars())->toBe('209.28');

    $result = military()->calculate(
        'reserve',
        'corporal',
        'basic',
        Province::Ontario,
        PayFrequency::Monthly,
        reserveDays: 37,
    );

    expect($result['base_annual']->dollars())->toBe('7743.36')
        ->and($result['pension']['included'])->toBeFalse()
        ->and($result['pension']['amount']->isZero())->toBeTrue();
});

it('calculates Regular Force take-home through the payroll engine in Ontario, Quebec, and Alberta', function (string $code) {
    $result = military()->calculate('regular', 'private', '1', Province::fromCode($code), PayFrequency::Annual);
    $engine = app(PayrollCalculator::class)->calculate([
        'annual_salary' => '52044',
        'province' => $code,
        'frequency' => 'annual',
        'pension' => $result['pension']['amount']->dollars(),
    ]);

    expect($result['payroll']->metrics['net_annual']->cents)->toBe($engine->metrics['net_annual']->cents)
        ->and($result['payroll']->metrics['federal_tax']->isPositive())->toBeTrue();
})->with(['ON', 'QC', 'AB']);

it('estimates Regular Force pension from official 2026 rates', function () {
    $pension = app(CafPensionCalculator::class)->annual('regular', Money::fromDollars('52044'));

    expect($pension['included'])->toBeTrue()
        ->and($pension['amount']->dollars())->toBe('4736.00');

    $aboveYmpe = app(CafPensionCalculator::class)->annual('regular', Money::fromDollars('100000'));
    $expected = Money::fromDollars('74600')->multiply('0.0910')
        ->add(Money::fromDollars('25400')->multiply('0.1169'));

    expect($aboveYmpe['amount']->cents)->toBe($expected->cents);
});

it('rejects an unknown rank and an invalid increment', function () {
    expect(fn () => militaryPay()->lookup('regular', 'admiral', '1'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => militaryPay()->lookup('regular', 'private', '9'))
        ->toThrow(InvalidArgumentException::class);
});

it('adds only manually entered taxable allowances', function () {
    $result = military()->calculate(
        'regular',
        'private',
        '1',
        Province::Ontario,
        PayFrequency::Annual,
        taxableAllowances: Money::fromDollars('5000'),
    );

    expect($result['gross_annual']->dollars())->toBe('57044.00')
        ->and($result['allowances']->dollars())->toBe('5000.00')
        ->and($result['pension']['amount']->dollars())->toBe('4736.00');
});
