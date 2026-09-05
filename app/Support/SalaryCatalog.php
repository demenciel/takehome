<?php

namespace App\Support;

final class SalaryCatalog
{
    /**
     * @return list<int>
     */
    public static function amounts(): array
    {
        return array_values(array_map('intval', config('tools.popular_salaries', [])));
    }

    /**
     * @return list<int>
     */
    public static function examples(): array
    {
        return array_values(array_map('intval', config('tools.example_salaries', self::amounts())));
    }

    /**
     * @return list<Province>
     */
    public static function indexableProvinces(): array
    {
        $codes = config('tools.salary_page_provinces', []);

        return array_values(array_filter(array_map(
            fn (string $code) => Province::fromCode($code),
            $codes,
        )));
    }

    public static function allows(Province $province, int $salary): bool
    {
        return in_array($province, self::indexableProvinces(), true)
            && in_array($salary, self::amounts(), true);
    }

    /**
     * @return array{previous: int|null, next: int|null}
     */
    public static function neighbors(int $salary): array
    {
        $amounts = self::amounts();
        $index = array_search($salary, $amounts, true);

        if ($index === false) {
            return ['previous' => null, 'next' => null];
        }

        return [
            'previous' => $amounts[$index - 1] ?? null,
            'next' => $amounts[$index + 1] ?? null,
        ];
    }

    /**
     * @return list<int>
     */
    public static function nearby(int $salary, int $limit = 6): array
    {
        $amounts = self::amounts();
        usort($amounts, fn (int $a, int $b) => abs($a - $salary) <=> abs($b - $salary));

        return array_values(array_filter(
            array_slice($amounts, 0, $limit + 1),
            fn (int $amount) => $amount !== $salary,
        ));
    }

    /**
     * @return list<int>
     */
    public static function compare(int $salary): array
    {
        $candidates = [
            $salary - 10000,
            $salary - 5000,
            $salary,
            $salary + 5000,
            $salary + 10000,
            $salary + 20000,
        ];

        return array_values(array_filter(
            $candidates,
            fn (int $amount) => in_array($amount, self::amounts(), true),
        ));
    }
}
