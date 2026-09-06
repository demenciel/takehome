<?php

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
