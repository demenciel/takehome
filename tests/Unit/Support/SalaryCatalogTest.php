<?php

use App\Support\Province;
use App\Support\SalaryCatalog;

it('indexes configured salaries only in the priority provinces', function () {
    expect(SalaryCatalog::allows(Province::Ontario, 80000))->toBeTrue()
        ->and(SalaryCatalog::allows(Province::Quebec, 50000))->toBeTrue()
        ->and(SalaryCatalog::allows(Province::Yukon, 80000))->toBeFalse()
        ->and(SalaryCatalog::allows(Province::Ontario, 80123))->toBeFalse();
});

it('returns adjacent and comparison salaries from the catalog', function () {
    expect(SalaryCatalog::neighbors(80000))->toBe([
        'previous' => 75000,
        'next' => 85000,
    ])->and(SalaryCatalog::compare(80000))->toBe([
        70000,
        75000,
        80000,
        85000,
        90000,
        100000,
    ]);
});
