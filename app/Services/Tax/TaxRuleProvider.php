<?php

namespace App\Services\Tax;

use App\Support\Province;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class TaxRuleProvider
{
    /**
     * @var array<string, string>
     */
    private array $provinceFiles = [
        'AB' => 'alberta.php',
        'BC' => 'british_columbia.php',
        'MB' => 'manitoba.php',
        'NB' => 'new_brunswick.php',
        'NL' => 'newfoundland_and_labrador.php',
        'NS' => 'nova_scotia.php',
        'ON' => 'ontario.php',
        'PE' => 'prince_edward_island.php',
        'QC' => 'quebec.php',
        'SK' => 'saskatchewan.php',
        'NT' => 'northwest_territories.php',
        'NU' => 'nunavut.php',
        'YT' => 'yukon.php',
    ];

    public function currentYear(): int
    {
        return (int) config('tax.current_year');
    }

    public function forYear(?int $year = null): TaxYearRules
    {
        $year ??= $this->currentYear();

        $ttl = (int) config('tax.cache_ttl', 86400);
        $key = "tax-rules:{$year}";

        if ($ttl <= 0) {
            return $this->loadYear($year);
        }

        return Cache::remember($key, $ttl, fn () => $this->loadYear($year));
    }

    public function availableYears(): array
    {
        $path = (string) config('tax.path');

        if (! is_dir($path)) {
            return [];
        }

        $years = [];

        foreach (scandir($path) ?: [] as $entry) {
            if (ctype_digit($entry) && is_dir($path.DIRECTORY_SEPARATOR.$entry)) {
                $years[] = (int) $entry;
            }
        }

        sort($years);

        return $years;
    }

    private function loadYear(int $year): TaxYearRules
    {
        $directory = (string) config('tax.path').DIRECTORY_SEPARATOR.$year;

        if (! is_dir($directory)) {
            throw new InvalidArgumentException("Tax rules for {$year} are not available.");
        }

        $provinces = [];

        foreach ($this->provinceFiles as $code => $file) {
            $provinces[$code] = $this->requireFile($directory.DIRECTORY_SEPARATOR.$file);
        }

        foreach (Province::all() as $province) {
            if (! isset($provinces[$province->code()])) {
                throw new InvalidArgumentException("Missing province rules for {$province->code()} in {$year}.");
            }
        }

        return new TaxYearRules(
            year: $year,
            federal: $this->requireFile($directory.DIRECTORY_SEPARATOR.'federal.php'),
            cpp: $this->requireFile($directory.DIRECTORY_SEPARATOR.'cpp.php'),
            qpp: $this->requireFile($directory.DIRECTORY_SEPARATOR.'qpp.php'),
            ei: $this->requireFile($directory.DIRECTORY_SEPARATOR.'ei.php'),
            qpip: $this->requireFile($directory.DIRECTORY_SEPARATOR.'qpip.php'),
            provinces: $provinces,
            sources: $this->requireFile($directory.DIRECTORY_SEPARATOR.'sources.php'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requireFile(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Missing tax data file: {$path}");
        }

        /** @var array<string, mixed> $data */
        $data = require $path;

        return $data;
    }
}
