<?php

namespace App\Livewire;

use App\Support\HourlyConversion;
use App\Support\Money;
use Livewire\Component;

class HourlySalaryConverter extends Component
{
    public string $mode = 'hourly_to_salary';

    public string $hourlyWage = '';

    public string $annualSalary = '';

    public string $hoursPerWeek = '40';

    public ?array $result = null;

    public function mount(string $mode = 'hourly_to_salary'): void
    {
        $this->mode = $mode;
    }

    public function convert(): void
    {
        $hours = (float) $this->hoursPerWeek;

        $this->validate([
            'hoursPerWeek' => ['required', 'numeric', 'min:1', 'max:80'],
            'hourlyWage' => [$this->mode === 'hourly_to_salary' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:10000'],
            'annualSalary' => [$this->mode === 'salary_to_hourly' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:10000000'],
        ], [
            'hourlyWage.required' => __('calculator.error_hourly'),
            'annualSalary.required' => __('calculator.error_salary'),
            'hoursPerWeek.min' => __('calculator.error_hours'),
        ]);

        if ($this->mode === 'hourly_to_salary') {
            $hourly = Money::fromDollars($this->hourlyWage);
            $annual = HourlyConversion::annualFromHourly($hourly, $hours);

            $this->result = [
                'hourly' => $hourly->format(),
                'annual' => $annual->format(),
                'hours' => $hours,
                'assumption' => HourlyConversion::assumptionLabel(),
            ];

            return;
        }

        $annual = Money::fromDollars($this->annualSalary);
        $hourly = HourlyConversion::hourlyFromAnnual($annual, $hours);

        $this->result = [
            'hourly' => $hourly->format(),
            'annual' => $annual->format(),
            'hours' => $hours,
            'assumption' => HourlyConversion::assumptionLabel(),
        ];
    }

    public function render()
    {
        return view('livewire.hourly-salary-converter');
    }
}
