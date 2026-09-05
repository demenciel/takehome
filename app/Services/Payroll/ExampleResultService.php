<?php

namespace App\Services\Payroll;

use App\Calculators\Payroll\PayrollCalculator;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use App\Support\SalaryCatalog;
use Illuminate\Support\Facades\Cache;

class ExampleResultService
{
    public function __construct(private PayrollCalculator $calculator) {}

    /**
     * @param  list<int>  $salaries
     * @return list<array{salary: int, net_annual: Money, effective_tax_rate: string, url: string|null}>
     */
    public function examples(Province $province, array $salaries): array
    {
        $year = (int) config('tax.current_year');
        $key = 'payroll-examples:'.$year.':'.$province->value.':'.implode('-', $salaries);

        return Cache::remember($key, (int) config('tax.cache_ttl', 86400), function () use ($province, $salaries) {
            return array_map(fn (int $salary) => $this->summarize($province, $salary), $salaries);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function salaryPage(Province $province, int $salary): array
    {
        $result = $this->calculator->calculate([
            'annual_salary' => $salary,
            'province' => $province->value,
            'frequency' => PayFrequency::Annual->value,
        ]);

        $netAnnual = $result->metrics['net_annual'];

        return [
            'result' => $result,
            'frequencies' => array_map(fn (PayFrequency $frequency) => [
                'frequency' => $frequency,
                'net' => $netAnnual->divideBy($frequency->periods()),
            ], PayFrequency::supported()),
        ];
    }

    /**
     * @return array{salary: int, net_annual: Money, effective_tax_rate: string, url: string|null}
     */
    private function summarize(Province $province, int $salary): array
    {
        $result = $this->calculator->calculate([
            'annual_salary' => $salary,
            'province' => $province->value,
            'frequency' => PayFrequency::Annual->value,
        ]);

        return [
            'salary' => $salary,
            'net_annual' => $result->metrics['net_annual'],
            'effective_tax_rate' => $result->metrics['effective_tax_rate'],
            'url' => SalaryCatalog::allows($province, $salary)
                ? route('paycheck.salary', [$province->slug(), $salary])
                : null,
        ];
    }
}
