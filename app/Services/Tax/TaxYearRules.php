<?php

namespace App\Services\Tax;

use App\Support\Province;
use InvalidArgumentException;

final class TaxYearRules
{
    /**
     * @param  array<string, mixed>  $federal
     * @param  array<string, mixed>  $cpp
     * @param  array<string, mixed>  $qpp
     * @param  array<string, mixed>  $ei
     * @param  array<string, mixed>  $qpip
     * @param  array<string, array<string, mixed>>  $provinces
     * @param  array<string, mixed>  $sources
     */
    public function __construct(
        public readonly int $year,
        public readonly array $federal,
        public readonly array $cpp,
        public readonly array $qpp,
        public readonly array $ei,
        public readonly array $qpip,
        public readonly array $provinces,
        public readonly array $sources,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function province(Province $province): array
    {
        $code = $province->code();

        if (! isset($this->provinces[$code])) {
            throw new InvalidArgumentException("No tax rules for {$code} in {$this->year}.");
        }

        return $this->provinces[$code];
    }
}
