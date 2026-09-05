<?php

use App\Calculators\Payroll\PayrollCalculator;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;

function paycheck(array $overrides = []): array
{
    return array_merge([
        'annual_salary' => '80000',
        'province' => 'NB',
        'frequency' => 'annual',
        'tax_year' => 2026,
    ], $overrides);
}

function metric($result, string $key): Money
{
    return $result->metrics[$key];
}

it('calculates a known $50,000 New Brunswick case from T4127 Option 1', function () {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'annual_salary' => '50000',
        'province' => 'NB',
    ]));

    expect(metric($result, 'cpp')->dollars())->toBe('2766.75')
        ->and(metric($result, 'ei')->dollars())->toBe('815.00')
        ->and(metric($result, 'taxable_income')->dollars())->toBe('49535.00')
        ->and(metric($result, 'federal_tax')->dollars())->toBe('3985.13')
        ->and(metric($result, 'provincial_tax')->dollars())->toBe('3078.90')
        ->and(metric($result, 'net_annual')->dollars())->toBe('39354.22');
});

it('calculates a known $80,000 Ontario case including health premium', function () {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'annual_salary' => '80000',
        'province' => 'ON',
    ]));

    expect(metric($result, 'cpp')->dollars())->toBe('4230.45')
        ->and(metric($result, 'cpp2')->dollars())->toBe('216.00')
        ->and(metric($result, 'ei')->dollars())->toBe('1123.07')
        ->and(metric($result, 'taxable_income')->dollars())->toBe('79073.00')
        ->and(metric($result, 'federal_tax')->dollars())->toBe('9242.60')
        ->and(metric($result, 'provincial_tax')->dollars())->toBe('4884.79')
        ->and(metric($result, 'net_annual')->dollars())->toBe('60303.09');
});

it('uses QPP, QPIP, reduced EI, and the federal abatement in Quebec', function () {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'annual_salary' => '75000',
        'province' => 'QC',
    ]));

    expect(metric($result, 'cpp')->dollars())->toBe('4479.30')
        ->and(metric($result, 'cpp2')->dollars())->toBe('16.00')
        ->and(metric($result, 'ei')->dollars())->toBe('895.70')
        ->and(metric($result, 'qpip')->dollars())->toBe('322.50')
        ->and(metric($result, 'federal_tax')->lessThan(Money::fromDollars('8210.44')))->toBeTrue()
        ->and(metric($result, 'provincial_tax')->isPositive())->toBeTrue()
        ->and(metric($result, 'net_annual')->isPositive())->toBeTrue();
});

it('returns zeros for zero income', function () {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'annual_salary' => '0',
        'province' => 'ON',
    ]));

    expect(metric($result, 'net_annual')->isZero())->toBeTrue()
        ->and(metric($result, 'federal_tax')->isZero())->toBeTrue()
        ->and(metric($result, 'provincial_tax')->isZero())->toBeTrue()
        ->and(metric($result, 'cpp')->isZero())->toBeTrue()
        ->and(metric($result, 'ei')->isZero())->toBeTrue();
});

it('keeps annual totals identical across pay frequencies', function () {
    $annual = app(PayrollCalculator::class)->calculate(paycheck(['frequency' => 'annual']));
    $biweekly = app(PayrollCalculator::class)->calculate(paycheck(['frequency' => 'biweekly']));
    $weekly = app(PayrollCalculator::class)->calculate(paycheck(['frequency' => 'weekly']));

    expect(metric($biweekly, 'net_annual')->equals(metric($annual, 'net_annual')))->toBeTrue()
        ->and(metric($weekly, 'net_annual')->equals(metric($annual, 'net_annual')))->toBeTrue()
        ->and(metric($biweekly, 'net_period')->cents)->toBe(metric($annual, 'net_annual')->divideBy(26)->cents)
        ->and(metric($weekly, 'net_period')->cents)->toBe(metric($annual, 'net_annual')->divideBy(52)->cents);
});

it('reduces taxable income for RRSP contributions', function () {
    $base = app(PayrollCalculator::class)->calculate(paycheck(['rrsp' => '0']));
    $rrsp = app(PayrollCalculator::class)->calculate(paycheck(['rrsp' => '5000']));

    expect(metric($rrsp, 'taxable_income')->cents)->toBe(metric($base, 'taxable_income')->cents - 500000)
        ->and(metric($rrsp, 'federal_tax')->lessThan(metric($base, 'federal_tax')))->toBeTrue();
});

it('produces a result for every province and territory at $80,000', function (Province $province) {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'province' => $province->value,
    ]));

    expect(metric($result, 'net_annual')->isPositive())->toBeTrue()
        ->and(metric($result, 'federal_tax')->isPositive())->toBeTrue()
        ->and($result->headlineAmount->isPositive())->toBeTrue();
})->with(Province::all());

it('applies a British Columbia tax reduction on low income', function () {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'annual_salary' => '20000',
        'province' => 'BC',
    ]));

    expect(metric($result, 'provincial_tax')->isZero())->toBeTrue();
});

it('applies Alberta tax credit and higher BPA relative to Ontario', function () {
    $alberta = app(PayrollCalculator::class)->calculate(paycheck(['province' => 'AB']));
    $ontario = app(PayrollCalculator::class)->calculate(paycheck(['province' => 'ON']));

    expect(metric($alberta, 'provincial_tax')->lessThan(metric($ontario, 'provincial_tax')))->toBeTrue();
});

it('handles very high income without going negative', function () {
    $result = app(PayrollCalculator::class)->calculate(paycheck([
        'annual_salary' => '1000000',
        'province' => 'ON',
    ]));

    expect(metric($result, 'net_annual')->isPositive())->toBeTrue()
        ->and(metric($result, 'cpp')->dollars())->toBe('4230.45')
        ->and(metric($result, 'cpp2')->dollars())->toBe('416.00')
        ->and(metric($result, 'ei')->dollars())->toBe('1123.07')
        ->and((float) $result->metrics['effective_tax_rate'])->toBeGreaterThan(30);
});

it('converts hourly wages into an annual salary', function () {
    $result = app(PayrollCalculator::class)->calculate([
        'input_mode' => 'hourly',
        'hourly_wage' => '38.46',
        'hours_per_week' => 40,
        'province' => 'NB',
        'frequency' => 'biweekly',
        'tax_year' => 2026,
    ]);

    expect($result->inputs['annual_salary']->cents)->toBe(7999680);
});

it('rejects an invalid province', function () {
    app(PayrollCalculator::class)->calculate([
        'annual_salary' => '50000',
        'province' => 'XX',
        'frequency' => 'annual',
    ]);
})->throws(InvalidArgumentException::class);
