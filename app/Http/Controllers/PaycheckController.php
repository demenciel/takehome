<?php

namespace App\Http\Controllers;

use App\Content\PaycheckContent;
use App\Services\Analytics\Analytics;
use App\Services\Payroll\ExampleResultService;
use App\Services\Seo\SeoPage;
use App\Services\Tax\TaxFreshness;
use App\Support\HourlyConversion;
use App\Support\Money;
use App\Support\Province;
use App\Support\SalaryCatalog;
use App\Support\ToolCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaycheckController extends Controller
{
    public function __construct(
        private PaycheckContent $content,
        private Analytics $analytics,
        private ExampleResultService $examples,
        private TaxFreshness $freshness,
    ) {}

    public function home(Request $request): View
    {
        $this->pageView($request);

        return view('pages.home', [
            'seo' => new SeoPage(
                title: 'Canadian Paycheck Calculator — Take-home pay by province',
                description: 'Calculate your estimated Canadian take-home pay by salary, province, and pay frequency. See income tax, CPP or QPP, EI, and net pay instantly.',
                canonical: route('home'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                ],
                faqs: $this->content->nationalFaqs(),
                includeWebsite: true,
            ),
            'provinces' => Province::all(),
            'popularSalaries' => SalaryCatalog::amounts(),
            'exampleSalaries' => SalaryCatalog::examples(),
            'hubs' => ToolCatalog::hubs(),
            'faqs' => $this->content->nationalFaqs(),
        ]);
    }

    public function canada(Request $request): View
    {
        $this->pageView($request);

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
            'popularSalaries' => SalaryCatalog::amounts(),
            'related' => ToolCatalog::relatedLinks(),
            'faqs' => $this->content->nationalFaqs(),
        ]);
    }

    public function province(Request $request, string $provinceSlug): View
    {
        $province = Province::fromSlug($provinceSlug);

        abort_unless($province, 404);

        $this->pageView($request, $province);

        $content = $this->content->province($province);
        $faqs = $this->content->provinceFaqs($province);
        $examples = $this->examples->examples($province, SalaryCatalog::examples());

        return view('pages.paycheck.province', [
            'province' => $province,
            'content' => $content,
            'faqs' => $faqs,
            'examples' => $examples,
            'indexableSalaries' => array_values(array_filter(
                SalaryCatalog::amounts(),
                fn (int $amount) => SalaryCatalog::allows($province, $amount),
            )),
            'deductions' => $this->content->payrollDeductions($province),
            'related' => ToolCatalog::relatedLinks($province),
            'seo' => new SeoPage(
                title: $province->name().' Paycheck Calculator — Take-home pay '.$this->freshness->year(),
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

        abort_unless($province && SalaryCatalog::allows($province, $salary), 404);

        $this->pageView($request, $province, $salary);

        $page = $this->examples->salaryPage($province, $salary);
        $result = $page['result'];
        $content = $this->content->province($province);
        $explanation = $this->content->salaryExplanation($province, $salary, $result->metrics);
        $hourly = HourlyConversion::hourlyFromAnnual(Money::fromDollars($salary));
        $neighbors = SalaryCatalog::neighbors($salary);
        $compare = SalaryCatalog::compare($salary);

        $faqs = [
            [
                'question' => 'How much is a $'.number_format($salary).' salary after tax in '.$province->name().'?',
                'answer' => $explanation,
            ],
            ...$this->content->provinceFaqs($province),
        ];

        return view('pages.paycheck.salary', [
            'province' => $province,
            'salary' => $salary,
            'result' => $result,
            'frequencies' => $page['frequencies'],
            'hourly' => $hourly,
            'hourAssumption' => HourlyConversion::assumptionLabel(),
            'explanation' => $explanation,
            'content' => $content,
            'faqs' => $faqs,
            'neighbors' => $neighbors,
            'compare' => $compare,
            'related' => ToolCatalog::relatedLinks($province),
            'seo' => new SeoPage(
                title: '$'.number_format($salary).' Salary After Tax in '.$province->name(),
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

    private function pageView(Request $request, ?Province $province = null, ?int $salary = null): void
    {
        $this->analytics->record('page_view', [
            'tool_key' => 'paycheck',
            'province' => $province?->value,
            'annual_salary_cents' => $salary ? $salary * 100 : null,
            'path' => $request->path(),
        ]);
    }
}
