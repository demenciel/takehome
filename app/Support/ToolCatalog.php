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
            ['slug' => 'take-home-pay-calculator', 'route' => 'tools.take_home', 'title' => 'Take-home pay calculator', 'nav' => 'Take-home pay calculator'],
            ['slug' => 'salary-after-tax-calculator', 'route' => 'tools.salary_after_tax', 'title' => 'Salary After Tax Calculator', 'nav' => 'Salary after tax'],
            ['slug' => 'overtime-pay-calculator', 'route' => 'tools.overtime', 'title' => 'Overtime Pay Calculator', 'nav' => 'Overtime pay calculator'],
            ['slug' => 'bonus-tax-calculator', 'route' => 'tools.bonus', 'title' => 'Bonus Tax Calculator', 'nav' => 'Bonus tax calculator'],
            ['slug' => 'raise-calculator', 'route' => 'tools.raise', 'title' => 'Raise Calculator', 'nav' => 'Raise calculator'],
            ['slug' => 'military-salary-calculator', 'route' => 'tools.military', 'title' => 'CAF Salary Calculator', 'nav' => 'CAF salary calculator'],
            ['slug' => 'hourly-to-salary-calculator', 'route' => 'tools.hourly_to_salary', 'title' => 'Hourly to Salary Calculator', 'nav' => 'Hourly to salary'],
            ['slug' => 'salary-to-hourly-calculator', 'route' => 'tools.salary_to_hourly', 'title' => 'Salary to Hourly Calculator', 'nav' => 'Salary to hourly'],
            ['slug' => 'biweekly-pay-calculator', 'route' => 'tools.biweekly', 'title' => 'Biweekly Pay Calculator', 'nav' => 'Biweekly pay'],
            ['slug' => 'weekly-pay-calculator', 'route' => 'tools.weekly', 'title' => 'Weekly Pay Calculator', 'nav' => 'Weekly pay'],
            ['slug' => 'parental-leave-calculator', 'route' => 'tools.parental', 'title' => 'Canadian Parental Leave Calculator', 'nav' => 'Parental leave calculator'],
            ['slug' => 'ei-maternity-parental-benefits', 'route' => 'tools.ei_benefits', 'title' => 'EI Maternity & Parental Benefits', 'nav' => 'EI maternity & parental benefits'],
            ['slug' => 'baby-cost-calculator', 'route' => 'tools.baby', 'title' => 'Canadian Baby Cost Calculator', 'nav' => 'Baby cost calculator'],
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function relatedLinks(?Province $province = null, string $context = 'paycheck'): array
    {
        $paycheckUrl = $province
            ? route('paycheck.province', $province->slug())
            : route('paycheck.canada');
        $paycheckLabel = $province
            ? $province->name().' paycheck calculator'
            : 'Canada paycheck calculator';

        $all = [
            'paycheck' => ['label' => $paycheckLabel, 'url' => $paycheckUrl],
            'overtime' => [
                'label' => $province ? $province->name().' overtime pay calculator' : 'Overtime pay calculator',
                'url' => $province ? route('tools.overtime.province', $province->slug()) : route('tools.overtime'),
            ],
            'bonus' => [
                'label' => $province ? $province->name().' bonus tax calculator' : 'Bonus tax calculator',
                'url' => $province ? route('tools.bonus.province', $province->slug()) : route('tools.bonus'),
            ],
            'raise' => [
                'label' => $province ? $province->name().' raise calculator' : 'Raise calculator',
                'url' => $province ? route('tools.raise.province', $province->slug()) : route('tools.raise'),
            ],
            'hourly' => ['label' => 'Hourly to salary calculator', 'url' => route('tools.hourly_to_salary')],
            'salary_after_tax' => ['label' => 'Salary after tax calculator', 'url' => route('tools.salary_after_tax')],
            'military' => ['label' => 'Canadian Armed Forces salary calculator', 'url' => route('tools.military')],
            'parental' => ['label' => 'Canadian parental leave calculator', 'url' => route('tools.parental')],
            'ei_benefits' => ['label' => 'EI maternity and parental benefits', 'url' => route('tools.ei_benefits')],
            'baby' => ['label' => 'Canadian baby cost calculator', 'url' => route('tools.baby')],
            'methodology' => ['label' => 'How the estimate is calculated', 'url' => route('methodology')],
        ];

        $order = match ($context) {
            'overtime' => ['paycheck', 'hourly', 'raise', 'methodology'],
            'bonus' => ['paycheck', 'raise', 'salary_after_tax', 'methodology'],
            'raise' => ['paycheck', 'bonus', 'hourly', 'methodology'],
            'military' => ['paycheck', 'salary_after_tax', 'methodology'],
            'parental' => ['ei_benefits', 'baby', 'paycheck', 'methodology'],
            'ei_benefits' => ['parental', 'baby', 'paycheck', 'methodology'],
            'baby' => ['parental', 'ei_benefits', 'paycheck', 'methodology'],
            default => ['overtime', 'bonus', 'raise', 'hourly', 'military', 'methodology'],
        };

        $links = array_map(fn (string $key) => $all[$key], $order);

        if ($province && $context === 'paycheck') {
            array_unshift($links, $all['paycheck']);
        }

        return $links;
    }
}
