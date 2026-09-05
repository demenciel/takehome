<?php

namespace App\Http\Controllers;

use App\Calculators\Payroll\PayrollCalculator;
use App\Content\PaycheckContent;
use App\Services\Analytics\Analytics;
use App\Services\Seo\SeoPage;
use App\Support\Province;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaycheckController extends Controller
{
    public function __construct(
        private PaycheckContent $content,
        private Analytics $analytics,
    ) {}

    public function home(Request $request): View
    {
        $this->analytics->record('page_view', [
            'tool_key' => 'paycheck',
            'path' => $request->path(),
        ]);

        return view('pages.home', [
            'seo' => new SeoPage(
                title: 'Canadian Paycheck Calculator — Take-home pay by province',
                description: 'Calculate your estimated Canadian take-home pay by salary, province, and pay frequency. See income tax, CPP or QPP, EI, and net pay instantly.',
                canonical: route('home'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                ],
                faqs: $this->content->nationalFaqs(),
            ),
            'provinces' => Province::all(),
            'popularSalaries' => config('tools.popular_salaries'),
            'faqs' => $this->content->nationalFaqs(),
            'taxYear' => (int) config('tax.current_year'),
        ]);
    }

    public function canada(Request $request): View
    {
        $this->analytics->record('page_view', [
            'tool_key' => 'paycheck',
            'path' => $request->path(),
        ]);

        return view('pages.paycheck.canada', [
            'seo' => new SeoPage(
                title: 'Canada Paycheck Calculator — Salary after tax',
                description: 'Use the Canada paycheck calculator to estimate take-home pay after federal tax, provincial tax, CPP or QPP, and EI for the current tax year.',
                canonical: route('paycheck.canada'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Canada paycheck calculator', 'url' => route('paycheck.canada')],
                ],
                faqs: $this->content->nationalFaqs(),
            ),
            'provinces' => Province::all(),
            'popularSalaries' => config('tools.popular_salaries'),
            'faqs' => $this->content->nationalFaqs(),
            'taxYear' => (int) config('tax.current_year'),
        ]);
    }

    public function province(Request $request, string $provinceSlug): View
    {
        $province = Province::fromSlug($provinceSlug);

        abort_unless($province, 404);

        $this->analytics->record('page_view', [
            'tool_key' => 'paycheck',
            'province' => $province->value,
            'path' => $request->path(),
        ]);

        $content = $this->content->province($province);
        $faqs = $this->content->provinceFaqs($province);

        return view('pages.paycheck.province', [
            'province' => $province,
            'content' => $content,
            'faqs' => $faqs,
            'popularSalaries' => config('tools.popular_salaries'),
            'taxYear' => (int) config('tax.current_year'),
            'seo' => new SeoPage(
                title: $province->name().' Paycheck Calculator — Take-home pay',
                description: $content['intro'].' Enter a salary and pay frequency to see estimated '.$province->adjective().' take-home pay.',
                canonical: route('paycheck.province', $province->slug()),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => $province->name().' paycheck calculator', 'url' => route('paycheck.province', $province->slug())],
                ],
                faqs: $faqs,
            ),
        ]);
    }

    public function salary(Request $request, string $provinceSlug, int $salary): View
    {
        $province = Province::fromSlug($provinceSlug);
        $allowed = config('tools.popular_salaries');

        abort_unless($province && in_array($salary, $allowed, true), 404);

        $this->analytics->record('page_view', [
            'tool_key' => 'paycheck',
            'province' => $province->value,
            'annual_salary_cents' => $salary * 100,
            'path' => $request->path(),
        ]);

        $result = app(PayrollCalculator::class)->calculate([
            'annual_salary' => $salary,
            'province' => $province->value,
            'frequency' => 'biweekly',
        ]);

        $content = $this->content->province($province);
        $faqs = [
            [
                'question' => 'How much is a $'.number_format($salary).' salary after tax in '.$province->name().'?',
                'answer' => 'On this page, the estimated biweekly take-home for a $'.number_format($salary).' salary in '.$province->name().' is '.$result->headlineAmount->format().'. Annual estimated take-home is '.$result->metrics['net_annual']->format().'. Your employer’s deductions can differ.',
            ],
            ...$this->content->provinceFaqs($province),
        ];

        return view('pages.paycheck.salary', [
            'province' => $province,
            'salary' => $salary,
            'result' => $result,
            'content' => $content,
            'faqs' => $faqs,
            'popularSalaries' => $allowed,
            'taxYear' => (int) config('tax.current_year'),
            'seo' => new SeoPage(
                title: '$'.number_format($salary).' salary after tax in '.$province->name(),
                description: 'Estimated take-home pay for a $'.number_format($salary).' salary in '.$province->name().' after income tax, '.($province->usesQpp() ? 'QPP' : 'CPP').', and EI.',
                canonical: route('paycheck.salary', [$province->slug(), $salary]),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => $province->name(), 'url' => route('paycheck.province', $province->slug())],
                    ['name' => '$'.number_format($salary).' salary', 'url' => route('paycheck.salary', [$province->slug(), $salary])],
                ],
                faqs: $faqs,
            ),
        ]);
    }
}
