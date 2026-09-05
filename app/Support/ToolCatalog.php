<?php

namespace App\Support;

final class ToolCatalog
{
    /**
     * Indexable specialized calculator pages. Each wraps a calculator module
     * without inventing a new tax engine.
     *
     * @return list<array{slug: string, route: string, title: string, nav: string}>
     */
    public static function hubs(): array
    {
        return [
            ['slug' => 'canada-paycheck-calculator', 'route' => 'paycheck.canada', 'title' => 'Canada Paycheck Calculator', 'nav' => 'Canada paycheck calculator'],
            ['slug' => 'paycheque-calculator', 'route' => 'tools.paycheque', 'title' => 'Paycheque Calculator', 'nav' => 'Paycheque calculator'],
            ['slug' => 'take-home-pay-calculator', 'route' => 'tools.take_home', 'title' => 'Take-Home Pay Calculator', 'nav' => 'Take-home pay calculator'],
            ['slug' => 'salary-after-tax-calculator', 'route' => 'tools.salary_after_tax', 'title' => 'Salary After Tax Calculator', 'nav' => 'Salary after tax'],
            ['slug' => 'hourly-to-salary-calculator', 'route' => 'tools.hourly_to_salary', 'title' => 'Hourly to Salary Calculator', 'nav' => 'Hourly to salary'],
            ['slug' => 'salary-to-hourly-calculator', 'route' => 'tools.salary_to_hourly', 'title' => 'Salary to Hourly Calculator', 'nav' => 'Salary to hourly'],
            ['slug' => 'biweekly-pay-calculator', 'route' => 'tools.biweekly', 'title' => 'Biweekly Pay Calculator', 'nav' => 'Biweekly pay'],
            ['slug' => 'weekly-pay-calculator', 'route' => 'tools.weekly', 'title' => 'Weekly Pay Calculator', 'nav' => 'Weekly pay'],
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function relatedLinks(?Province $province = null): array
    {
        $links = [
            ['label' => 'Canada paycheck calculator', 'url' => route('paycheck.canada')],
            ['label' => 'Take-home pay calculator', 'url' => route('tools.take_home')],
            ['label' => 'Hourly-to-salary calculator', 'url' => route('tools.hourly_to_salary')],
            ['label' => 'Salary-to-hourly calculator', 'url' => route('tools.salary_to_hourly')],
            ['label' => 'Biweekly pay calculator', 'url' => route('tools.biweekly')],
            ['label' => 'How the estimate is calculated', 'url' => route('methodology')],
        ];

        if ($province) {
            array_unshift($links, [
                'label' => $province->name().' paycheck calculator',
                'url' => route('paycheck.province', $province->slug()),
            ]);
        }

        return $links;
    }
}
