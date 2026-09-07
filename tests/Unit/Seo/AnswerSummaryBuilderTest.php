<?php

use App\Services\Family\BabyBudgetService;
use App\Services\Family\ParentalLeaveProjectionService;
use App\Services\Payroll\ExampleResultService;
use App\Services\Seo\AnswerSummaryBuilder;
use App\Support\HourlyConversion;
use App\Support\Money;
use App\Support\Province;

it('builds a salary answer from calculator results', function () {
    $page = app(ExampleResultService::class)->salaryPage(Province::Ontario, 80000);
    $hourly = HourlyConversion::hourlyFromAnnual(Money::fromDollars(80000));
    $summary = app(AnswerSummaryBuilder::class)->salary(
        Province::Ontario,
        80000,
        $page['result'],
        $page['frequencies'],
        $hourly,
    );

    expect($summary['question'])->toBe('How much is an $80,000 salary after tax in Ontario?')
        ->and($summary['answer'])->toContain('$60,303.09')
        ->and($summary['answer'])->toContain('2026')
        ->and($summary['year'])->toBe(2026)
        ->and($summary['methodology_url'])->toEndWith('/methodology')
        ->and(collect($summary['facts'])->pluck('label')->all())->toContain('Annual net', 'Monthly net', 'Biweekly net', 'Federal tax');
});

it('builds a parental-leave answer from the Ontario example', function () {
    $example = app(ParentalLeaveProjectionService::class)->project([
        'province' => Province::Ontario,
        'salary' => 80000,
        'frequency' => 'biweekly',
        'leave_type' => 'maternity_standard',
        'planned_weeks' => 50,
    ]);

    $summary = app(AnswerSummaryBuilder::class)->parental($example);

    expect($summary['question'])->toBe('How much will I make on parental leave in Canada?')
        ->and($summary['answer'])->toContain('$729.00')
        ->and($summary['answer'])->toContain('QPIP')
        ->and($summary['updated'])->toBe('September 7, 2026');
});

it('builds an EI maternity maximum answer', function () {
    $example = app(ParentalLeaveProjectionService::class)
        ->eiOnly(Money::fromDollars(80000), 'maternity', 15, false);

    $summary = app(AnswerSummaryBuilder::class)->eiBenefits($example);

    expect($summary['question'])->toBe('What is the maximum EI maternity benefit in 2026?')
        ->and($summary['answer'])->toContain('$729.00')
        ->and($summary['answer'])->toContain('$10,935.00');
});

it('builds a baby-cost answer that refuses a single national total', function () {
    $service = app(BabyBudgetService::class);
    $summary = app(AnswerSummaryBuilder::class)->baby($service->summarize(
        $service->defaultStartupState(),
        $service->defaultRecurringState(),
    ));

    expect($summary['question'])->toBe('How much does a baby cost in Canada?')
        ->and($summary['answer'])->toContain('no single Canadian baby-cost figure')
        ->and($summary['updated'])->toBe('September 7, 2026');
});
