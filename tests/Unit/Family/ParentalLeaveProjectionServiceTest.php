<?php

use App\Services\Family\ParentalLeaveProjectionService;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;

function projection(): ParentalLeaveProjectionService
{
    return app(ParentalLeaveProjectionService::class);
}

it('projects Ontario leave income below the EI cap without a top-up', function () {
    $result = projection()->project([
        'province' => Province::Ontario,
        'salary' => Money::fromDollars(40000),
        'frequency' => PayFrequency::Biweekly,
        'leave_type' => 'standard',
        'planned_weeks' => 20,
        'top_up_enabled' => false,
    ]);

    expect($result['supported'])->toBeTrue()
        ->and($result['quebec'])->toBeFalse()
        ->and($result['weekly_ei']->lessThan(Money::fromDollars('729')))->toBeTrue()
        ->and($result['top_up']['enabled'])->toBeFalse()
        ->and($result['leave_weeks'])->toBe(20)
        ->and($result['employment']['net_annual']->isPositive())->toBeTrue();
});

it('applies a top-up-to-percent of regular salary', function () {
    $result = projection()->project([
        'province' => 'ON',
        'salary' => 80000,
        'frequency' => 'biweekly',
        'leave_type' => 'maternity_standard',
        'planned_weeks' => 50,
        'top_up_enabled' => true,
        'top_up_percent' => '80',
        'top_up_weeks' => 17,
        'top_up_mode' => 'to_percent',
    ]);

    expect($result['top_up']['enabled'])->toBeTrue()
        ->and($result['top_up']['weekly']->isPositive())->toBeTrue()
        ->and($result['leave_weekly']->greaterThan($result['weekly_ei']))->toBeTrue();
});

it('does not invent an EI estimate for Québec', function () {
    $result = projection()->project([
        'province' => Province::Quebec,
        'salary' => 80000,
        'frequency' => 'monthly',
        'leave_type' => 'maternity_standard',
        'planned_weeks' => 50,
    ]);

    expect($result['supported'])->toBeFalse()
        ->and($result['quebec'])->toBeTrue()
        ->and($result['qpip_url'])->toContain('rqap.gouv.qc.ca')
        ->and($result)->not->toHaveKey('weekly_ei');
});

it('compares standard and extended totals', function () {
    $result = projection()->project([
        'province' => 'ON',
        'salary' => 80000,
        'leave_type' => 'maternity_standard',
        'planned_weeks' => 50,
    ]);

    expect($result['comparison']['standard']['weeks'])->toBe(50)
        ->and($result['comparison']['extended']['weeks'])->toBeGreaterThan(50)
        ->and($result['comparison']['standard']['weekly']->greaterThan($result['comparison']['extended']['weekly']))->toBeTrue();
});
