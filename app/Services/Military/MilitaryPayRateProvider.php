<?php

namespace App\Services\Military;

use App\Support\Money;
use InvalidArgumentException;

class MilitaryPayRateProvider
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $sources = null;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $tables = [];

    public function payYear(): int
    {
        return (int) config('military.pay_year', 2025);
    }

    /**
     * @return array<string, mixed>
     */
    public function sources(): array
    {
        return $this->sources ??= require $this->yearPath().'/sources.php';
    }

    /**
     * @return array<string, mixed>
     */
    public function pensionRules(): array
    {
        return require $this->yearPath().'/pension.php';
    }

    /**
     * @return list<array{key: string, short: string, name: string, group: string}>
     */
    public function ranks(string $component): array
    {
        $ranks = [];

        foreach ($this->table($component)['ranks'] as $key => $rank) {
            $ranks[] = [
                'key' => $key,
                'short' => $rank['short'],
                'name' => $rank['name'],
                'group' => $rank['group'],
            ];
        }

        return $ranks;
    }

    /**
     * @return list<string>
     */
    public function payLevels(string $component, string $rank): array
    {
        return array_keys($this->rank($component, $rank)['levels']);
    }

    /**
     * @return array<string, string>
     */
    public function increments(string $component, string $rank, string $level): array
    {
        $levels = $this->rank($component, $rank)['levels'];

        if (! isset($levels[$level])) {
            throw new InvalidArgumentException("Unknown pay level [{$level}] for {$rank}.");
        }

        return $levels[$level];
    }

    /**
     * @return array{
     *     rank: string,
     *     rank_name: string,
     *     rank_short: string,
     *     level: string,
     *     increment: string,
     *     increment_label: string,
     *     unit: string,
     *     rate: Money,
     *     component: string
     * }
     */
    public function lookup(string $component, string $rank, string $increment, ?string $level = null): array
    {
        $resolvedLevel = $level ?: $this->payLevels($component, $rank)[0];
        $increments = $this->increments($component, $rank, $resolvedLevel);

        if (! isset($increments[$increment])) {
            throw new InvalidArgumentException("Unknown pay increment [{$increment}] for {$rank}.");
        }

        $meta = $this->rank($component, $rank);

        return [
            'rank' => $rank,
            'rank_name' => $meta['name'],
            'rank_short' => $meta['short'],
            'level' => $resolvedLevel,
            'increment' => $increment,
            'increment_label' => $this->incrementLabel($increment),
            'unit' => $this->table($component)['unit'],
            'rate' => Money::fromDollars($increments[$increment]),
            'component' => $component,
        ];
    }

    public function incrementLabel(string $increment): string
    {
        return $increment === 'basic' ? 'Basic' : 'Pay increment '.$increment;
    }

    /**
     * @return array<string, mixed>
     */
    private function table(string $component): array
    {
        $file = match ($component) {
            'regular' => 'regular.php',
            'reserve' => 'reserve.php',
            default => throw new InvalidArgumentException("Unknown CAF component [{$component}]."),
        };

        return $this->tables[$component] ??= require $this->yearPath().'/'.$file;
    }

    /**
     * @return array<string, mixed>
     */
    private function rank(string $component, string $rank): array
    {
        $ranks = $this->table($component)['ranks'];

        if (! isset($ranks[$rank])) {
            throw new InvalidArgumentException("Unknown CAF rank [{$rank}].");
        }

        return $ranks[$rank];
    }

    private function yearPath(): string
    {
        return rtrim((string) config('military.path'), '/').'/'.$this->payYear();
    }
}
