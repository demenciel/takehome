<?php

use App\Services\Family\ParentalBenefitsService;
use App\Support\Money;

function benefits(): ParentalBenefitsService
{
    return app(ParentalBenefitsService::class);
}

it('estimates weekly EI below the 2026 maximum', function () {
    $estimate = benefits()->estimate(Money::fromDollars(40000), 'standard_parental', 20);

    expect($estimate['weekly_benefit']->lessThan(Money::fromDollars('729')))->toBeTrue()
        ->and($estimate['capped'])->toBeFalse()
        ->and($estimate['weeks'])->toBe(20)
        ->and($estimate['total']->equals($estimate['weekly_benefit']->multiply('20')))->toBeTrue();
});

it('caps weekly EI at the 2026 standard maximum', function () {
    $estimate = benefits()->estimate(Money::fromDollars(80000), 'standard_parental', 20);

    expect($estimate['weekly_benefit']->format())->toBe('$729.00')
        ->and($estimate['capped'])->toBeTrue()
        ->and($estimate['total']->format())->toBe('$14,580.00');
});

it('caps weekly EI at the 2026 extended maximum', function () {
    $estimate = benefits()->estimate(Money::fromDollars(80000), 'extended_parental', 20);

    expect($estimate['weekly_benefit']->format())->toBe('$437.00')
        ->and($estimate['capped'])->toBeTrue();
});

it('caps maternity at 15 weeks', function () {
    $estimate = benefits()->estimate(Money::fromDollars(80000), 'maternity', 20);

    expect($estimate['weeks'])->toBe(15)
        ->and($estimate['warnings'])->not->toBeEmpty()
        ->and($estimate['total']->format())->toBe('$10,935.00');
});

it('caps standard parental at 35 weeks for one parent', function () {
    $estimate = benefits()->estimate(Money::fromDollars(80000), 'standard_parental', 40);

    expect($estimate['weeks'])->toBe(35)
        ->and($estimate['max_weeks'])->toBe(35)
        ->and($estimate['shared_max_weeks'])->toBe(40);
});

it('caps extended parental at 61 weeks for one parent', function () {
    $estimate = benefits()->estimate(Money::fromDollars(80000), 'extended_parental', 70);

    expect($estimate['weeks'])->toBe(61)
        ->and($estimate['shared_max_weeks'])->toBe(69);
});

it('reduces shared parental weeks when both parents exceed the pool', function () {
    $estimate = benefits()->estimate(Money::fromDollars(80000), 'standard_parental', 25, otherParentWeeks: 20);

    expect($estimate['weeks'])->toBe(20)
        ->and($estimate['warnings'][0])->toContain('share at most 40');
});

it('splits maternity plus standard parental weeks', function () {
    $split = benefits()->splitLeave('maternity_standard', 50);

    expect($split['maternity_weeks'])->toBe(15)
        ->and($split['parental_weeks'])->toBe(35)
        ->and($split['parental_program'])->toBe('standard_parental');
});

it('splits maternity plus extended parental weeks', function () {
    $split = benefits()->splitLeave('maternity_extended', 76);

    expect($split['maternity_weeks'])->toBe(15)
        ->and($split['parental_weeks'])->toBe(61)
        ->and($split['parental_program'])->toBe('extended_parental');
});
