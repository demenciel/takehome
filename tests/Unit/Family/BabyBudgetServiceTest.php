<?php

use App\Services\Family\BabyBudgetService;

function baby(): BabyBudgetService
{
    return app(BabyBudgetService::class);
}

it('totals planned startup costs', function () {
    $summary = baby()->summarize([
        ['planned' => '100', 'skip' => false],
        ['planned' => '50', 'skip' => false],
    ], []);

    expect($summary['planned_startup']->format())->toBe('$150.00')
        ->and($summary['remaining_purchases']->format())->toBe('$150.00');
});

it('counts already purchased items as spent instead of remaining', function () {
    $summary = baby()->summarize([
        ['planned' => '100', 'purchased' => true, 'actual' => '90'],
        ['planned' => '40', 'purchased' => false],
    ], []);

    expect($summary['already_spent']->format())->toBe('$90.00')
        ->and($summary['remaining_purchases']->format())->toBe('$40.00');
});

it('treats gifts as savings and not remaining purchases', function () {
    $summary = baby()->summarize([
        ['planned' => '250', 'gift' => true],
        ['planned' => '30'],
    ], []);

    expect($summary['gift_savings']->format())->toBe('$250.00')
        ->and($summary['remaining_purchases']->format())->toBe('$30.00');
});

it('records second-hand savings when a used cost is entered', function () {
    $summary = baby()->summarize([
        ['planned' => '300', 'used' => true, 'used_cost' => '120'],
    ], []);

    expect($summary['used_savings']->format())->toBe('$180.00')
        ->and($summary['remaining_purchases']->format())->toBe('$120.00');
});

it('annualizes recurring monthly expenses', function () {
    $summary = baby()->summarize([], [
        ['monthly' => '80'],
        ['monthly' => '20'],
    ]);

    expect($summary['monthly_recurring']->format())->toBe('$100.00')
        ->and($summary['recurring_first_year']->format())->toBe('$1,200.00');
});

it('delays childcare until the entered start month', function () {
    $summary = baby()->summarize([], [], [
        'needed' => true,
        'start_month' => 7,
        'monthly' => '1200',
        'subsidy' => '200',
    ]);

    expect($summary['childcare_months'])->toBe(6)
        ->and($summary['childcare_monthly']->format())->toBe('$1,000.00')
        ->and($summary['childcare_first_year']->format())->toBe('$6,000.00');
});

it('projects savings and a funding gap', function () {
    $summary = baby()->summarize(
        [['planned' => '1000']],
        [['monthly' => '100']],
        [],
        [
            'current_savings' => '500',
            'monthly_saved' => '100',
            'months_until' => 4,
        ],
    );

    expect($summary['projected_savings']->format())->toBe('$900.00')
        ->and($summary['first_year_cost']->format())->toBe('$2,200.00')
        ->and($summary['amount_left_to_prepare']->isPositive())->toBeTrue();
});

it('handles zero-value edge cases without going negative on remaining purchases', function () {
    $summary = baby()->summarize([], [], [], []);

    expect($summary['planned_startup']->isZero())->toBeTrue()
        ->and($summary['remaining_purchases']->isZero())->toBeTrue()
        ->and($summary['first_year_cost']->isZero())->toBeTrue()
        ->and($summary['amount_left_to_prepare']->isZero())->toBeTrue();
});
