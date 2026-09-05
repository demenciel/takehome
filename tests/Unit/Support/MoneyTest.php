<?php

use App\Support\Money;

it('stores dollars as integer cents', function () {
    expect(Money::fromDollars('80000.00')->cents)->toBe(8000000);
    expect(Money::fromDollars(80_000)->cents)->toBe(8000000);
});

it('formats CAD with commas and two decimals', function () {
    expect(Money::fromDollars('75123.50')->format())->toBe('$75,123.50');
    expect(Money::fromDollars('-12.04')->format())->toBe('-$12.04');
});

it('applies CRA half-up rounding on rate products', function () {
    expect(Money::fromRateProduct(100, '0.163')->cents)->toBe(16);
    expect(Money::fromRateProduct(100, '0.165')->cents)->toBe(17);
});

it('does not use floating point for addition', function () {
    $sum = Money::fromDollars('0.10')->add(Money::fromDollars('0.20'));

    expect($sum->cents)->toBe(30);
    expect($sum->format())->toBe('$0.30');
});
