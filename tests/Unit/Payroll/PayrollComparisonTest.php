<?php

use App\Calculators\Payroll\PayrollCalculator;
use App\Services\Payroll\PayrollComparison;
use App\Support\Money;
use App\Support\Province;

function comparePay(string $province, string $baseline, string $modified, array $shared = []): array
{
    return app(PayrollComparison::class)->compare(
        Province::fromCode($province),
        Money::fromDollars($baseline),
        Money::fromDollars($modified),
        sharedInputs: $shared,
    );
}

it('calculates a $50,000 salary plus a $5,000 bonus as the net difference', function () {
    $result = comparePay('ON', '50000', '55000');
    $engine = app(PayrollCalculator::class);

    $without = $engine->calculate(['annual_salary' => '50000', 'province' => 'ON', 'frequency' => 'annual']);
    $with = $engine->calculate(['annual_salary' => '55000', 'province' => 'ON', 'frequency' => 'annual']);

    expect($result['gross_delta']->dollars())->toBe('5000.00')
        ->and($result['net_annual_delta']->cents)->toBe(
            $with->metrics['net_annual']->cents - $without->metrics['net_annual']->cents
        );
});

it('calculates an $80,000 salary plus a $10,000 bonus', function () {
    $result = comparePay('ON', '80000', '90000');

    expect($result['gross_delta']->dollars())->toBe('10000.00')
        ->and($result['net_annual_delta']->cents)->toBe(
            $result['modified']->metrics['net_annual']->cents
            - $result['baseline']->metrics['net_annual']->cents
        )
        ->and((float) $result['keep_percent'])->toBeGreaterThan(0)
        ->and((float) $result['keep_percent'])->toBeLessThan(100);
});

it('calculates a $100,000 salary plus a $20,000 bonus', function () {
    $result = comparePay('ON', '100000', '120000');

    expect($result['gross_delta']->dollars())->toBe('20000.00')
        ->and($result['cpp_delta']->isZero())->toBeTrue()
        ->and($result['ei_delta']->isZero())->toBeTrue()
        ->and($result['tax_delta']->isPositive())->toBeTrue();
});

it('does not add CPP or EI on a bonus once ceilings are already maxed', function () {
    $result = comparePay('ON', '200000', '210000');

    expect($result['cpp_delta']->isZero())->toBeTrue()
        ->and($result['cpp2_delta']->isZero())->toBeTrue()
        ->and($result['ei_delta']->isZero())->toBeTrue()
        ->and($result['tax_delta']->isPositive())->toBeTrue();
});

it('uses QPP and QPIP for a Quebec bonus', function () {
    $result = comparePay('QC', '75000', '85000');

    expect($result['modified']->metrics['qpip']->isPositive())->toBeTrue()
        ->and($result['baseline']->inputs['province'])->toBe('QC')
        ->and($result['net_annual_delta']->cents)->toBe(
            $result['modified']->metrics['net_annual']->cents
            - $result['baseline']->metrics['net_annual']->cents
        );
});

it('calculates Alberta and Ontario bonus comparisons independently', function () {
    $alberta = comparePay('AB', '80000', '90000');
    $ontario = comparePay('ON', '80000', '90000');

    expect($alberta['net_annual_delta']->isPositive())->toBeTrue()
        ->and($ontario['net_annual_delta']->isPositive())->toBeTrue()
        ->and($alberta['baseline']->metrics['provincial_tax']->lessThan(
            $ontario['baseline']->metrics['provincial_tax']
        ))->toBeTrue();
});

it('returns a zero net bonus when the bonus is zero', function () {
    $result = comparePay('ON', '80000', '80000');

    expect($result['gross_delta']->isZero())->toBeTrue()
        ->and($result['net_annual_delta']->isZero())->toBeTrue()
        ->and($result['keep_percent'])->toBe('0.0');
});

it('calculates a $50,000 to $55,000 raise as the net difference', function () {
    $result = comparePay('ON', '50000', '55000');

    expect($result['gross_delta']->dollars())->toBe('5000.00')
        ->and($result['net_annual_delta']->cents)->toBe(
            $result['modified']->metrics['net_annual']->cents
            - $result['baseline']->metrics['net_annual']->cents
        );
});

it('calculates $75,000 to $85,000 and $100,000 to $120,000 raises', function () {
    $mid = comparePay('ON', '75000', '85000');
    $high = comparePay('ON', '100000', '120000');

    expect($mid['gross_delta']->dollars())->toBe('10000.00')
        ->and($high['gross_delta']->dollars())->toBe('20000.00')
        ->and($high['cpp_delta']->isZero())->toBeTrue()
        ->and($high['ei_delta']->isZero())->toBeTrue();
});

it('calculates Quebec and Alberta raises with the same net-difference rule', function (string $code) {
    $result = comparePay($code, '75000', '85000');

    expect($result['net_annual_delta']->cents)->toBe(
        $result['modified']->metrics['net_annual']->cents
        - $result['baseline']->metrics['net_annual']->cents
    )->and($result['gross_delta']->dollars())->toBe('10000.00');
})->with(['QC', 'AB', 'ON']);

it('treats a zero raise as no incremental income', function () {
    $result = comparePay('ON', '75000', '75000');

    expect($result['net_annual_delta']->isZero())->toBeTrue()
        ->and($result['keep_percent'])->toBe('0.0');
});

it('increases CPP2 when a raise crosses the second additional threshold', function () {
    $result = comparePay('ON', '70000', '80000');

    expect($result['baseline']->metrics['cpp2']->isZero())->toBeTrue()
        ->and($result['modified']->metrics['cpp2']->isPositive())->toBeTrue()
        ->and($result['cpp2_delta']->isPositive())->toBeTrue();
});
