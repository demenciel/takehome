<?php

namespace App\Services\Military;

use App\Support\Money;

class CafPensionCalculator
{
    public function __construct(private MilitaryPayRateProvider $provider) {}

    /**
     * Regular Force CFSA contribution using official Treasury Board 2026 rates.
     * Reserve Force is not estimated.
     */
    public function annual(string $component, Money $pensionablePay, ?Money $override = null): array
    {
        if ($override instanceof Money) {
            return [
                'included' => true,
                'amount' => $override,
                'source' => 'override',
                'note' => 'Using the annual pension contribution you entered.',
            ];
        }

        $rules = $this->provider->pensionRules()[$component] ?? ['included' => false];

        if (! ($rules['included'] ?? false)) {
            return [
                'included' => false,
                'amount' => Money::zero(),
                'source' => 'omitted',
                'note' => $rules['note'] ?? 'CAF pension deductions are not included for this component.',
            ];
        }

        $ympe = Money::fromDollars($rules['ympe']);
        $toYmpe = $pensionablePay->min($ympe)->multiply((string) $rules['rate_to_ympe']);
        $above = $pensionablePay->greaterThan($ympe)
            ? $pensionablePay->subtract($ympe)->multiply((string) $rules['rate_above_ympe'])
            : Money::zero();

        return [
            'included' => true,
            'amount' => $toYmpe->add($above),
            'source' => 'regular_force_2026',
            'note' => 'Estimated Regular Force pension using 9.10% up to the 2026 YMPE of $74,600 and 11.69% above it. Members with 35 years of pensionable service are not modeled.',
        ];
    }
}
