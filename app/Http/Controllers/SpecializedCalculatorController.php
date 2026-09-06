<?php

namespace App\Http\Controllers;

use App\Content\BonusPageContent;
use App\Content\OvertimePageContent;
use App\Content\RaisePageContent;
use App\Services\Analytics\Analytics;
use App\Services\Overtime\OvertimePayEstimator;
use App\Services\Overtime\OvertimeRuleProvider;
use App\Services\Payroll\PayrollComparison;
use App\Services\Seo\AnswerSummaryBuilder;
use App\Services\Seo\SeoPage;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use App\Support\ToolCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SpecializedCalculatorController extends Controller
{
    public function __construct(
        private Analytics $analytics,
        private OvertimePageContent $overtimeContent,
        private BonusPageContent $bonusContent,
        private RaisePageContent $raiseContent,
        private OvertimeRuleProvider $overtimeRules,
        private OvertimePayEstimator $overtimeEstimator,
        private PayrollComparison $comparison,
        private AnswerSummaryBuilder $answers,
    ) {}

    public function overtime(Request $request): View
    {
        $example = $this->overtimeEstimator->estimate(
            Province::Ontario,
            Money::fromDollars('30'),
            40,
            8,
            PayFrequency::Weekly,
        );

        return $this->overtimePage($request, $this->overtimeContent->national(), example: $example);
    }

    public function overtimeProvince(Request $request, string $provinceSlug): View|RedirectResponse
    {
        $province = $this->canonicalProvince($provinceSlug, 'tools.overtime.province');

        if ($province instanceof RedirectResponse) {
            return $province;
        }

        $page = $this->overtimeContent->province($province);
        $example = $this->overtimeEstimator->estimate(
            $province,
            Money::fromDollars('30'),
            40,
            8,
            PayFrequency::Weekly,
        );

        return $this->overtimePage($request, $page, $province, $example);
    }

    public function bonus(Request $request): View
    {
        $example = $this->comparison->compare(
            Province::Ontario,
            Money::fromDollars(80000),
            Money::fromDollars(90000),
        );

        return $this->bonusPage($request, $this->bonusContent->national(), example: $example);
    }

    public function bonusProvince(Request $request, string $provinceSlug): View|RedirectResponse
    {
        $province = $this->canonicalProvince($provinceSlug, 'tools.bonus.province');

        if ($province instanceof RedirectResponse) {
            return $province;
        }

        $example = $this->comparison->compare(
            $province,
            Money::fromDollars(80000),
            Money::fromDollars(90000),
        );

        return $this->bonusPage($request, $this->bonusContent->province($province), $province, $example);
    }

    public function raise(Request $request): View
    {
        $example = $this->comparison->compare(
            Province::Ontario,
            Money::fromDollars(75000),
            Money::fromDollars(85000),
        );

        return $this->raisePage($request, $this->raiseContent->national(), example: $example);
    }

    public function raiseProvince(Request $request, string $provinceSlug): View|RedirectResponse
    {
        $province = $this->canonicalProvince($provinceSlug, 'tools.raise.province');

        if ($province instanceof RedirectResponse) {
            return $province;
        }

        $example = $this->comparison->compare(
            $province,
            Money::fromDollars(75000),
            Money::fromDollars(85000),
        );

        return $this->raisePage($request, $this->raiseContent->province($province), $province, $example);
    }

    /**
     * @param  array<string, mixed>  $page
     * @param  array<string, mixed>|null  $example
     */
    private function overtimePage(Request $request, array $page, ?Province $province = null, ?array $example = null): View
    {
        $this->pageView($request, 'overtime', $province);

        $route = $province ? route('tools.overtime.province', $province->slug()) : route('tools.overtime');
        $rules = $page['rules'] ?? ($example['rules'] ?? null);
        $faqs = $example
            ? $this->answers->overtimeFaqs($page['faqs'], $example, $province, $rules)
            : $page['faqs'];

        return view('pages.tools.overtime', [
            'page' => $page,
            'province' => $province,
            'example' => $example,
            'summary' => $example ? $this->answers->overtime($province, $example, $rules) : null,
            'provinces' => Province::all(),
            'rulesProvider' => $this->overtimeRules,
            'related' => ToolCatalog::relatedLinks($province, 'overtime'),
            'faqs' => $faqs,
            'seo' => $this->seo($page, $route, $province ? $province->name().' overtime calculator' : $page['h1'], $faqs),
        ]);
    }

    /**
     * @param  array<string, mixed>  $page
     * @param  array<string, mixed>|null  $example
     */
    private function bonusPage(Request $request, array $page, ?Province $province = null, ?array $example = null): View
    {
        $this->pageView($request, 'bonus', $province);

        $route = $province ? route('tools.bonus.province', $province->slug()) : route('tools.bonus');
        $faqs = $example
            ? $this->answers->bonusFaqs($page['faqs'], $example, $province)
            : $page['faqs'];

        return view('pages.tools.bonus', [
            'page' => $page,
            'province' => $province,
            'example' => $example,
            'summary' => $example ? $this->answers->bonus($province, $example) : null,
            'provinces' => Province::all(),
            'related' => ToolCatalog::relatedLinks($province, 'bonus'),
            'faqs' => $faqs,
            'seo' => $this->seo($page, $route, $province ? $province->name().' bonus calculator' : $page['h1'], $faqs),
        ]);
    }

    /**
     * @param  array<string, mixed>  $page
     * @param  array<string, mixed>|null  $example
     */
    private function raisePage(Request $request, array $page, ?Province $province = null, ?array $example = null): View
    {
        $this->pageView($request, 'raise', $province);

        $route = $province ? route('tools.raise.province', $province->slug()) : route('tools.raise');
        $faqs = $example
            ? $this->answers->raiseFaqs($page['faqs'], $example, $province)
            : $page['faqs'];

        return view('pages.tools.raise', [
            'page' => $page,
            'province' => $province,
            'example' => $example,
            'summary' => $example ? $this->answers->raise($province, $example) : null,
            'provinces' => Province::all(),
            'related' => ToolCatalog::relatedLinks($province, 'raise'),
            'faqs' => $faqs,
            'seo' => $this->seo($page, $route, $province ? $province->name().' raise calculator' : $page['h1'], $faqs),
        ]);
    }

    /**
     * @param  array<string, mixed>  $page
     * @param  list<array{question: string, answer: string}>|null  $faqs
     */
    private function seo(array $page, string $canonical, string $crumb, ?array $faqs = null): SeoPage
    {
        return new SeoPage(
            title: $page['title'],
            description: $page['description'],
            canonical: $canonical,
            breadcrumbs: [
                ['name' => __('common.home'), 'url' => route('home')],
                ['name' => $crumb, 'url' => $canonical],
            ],
            faqs: $faqs ?? $page['faqs'],
            ogTitle: $page['title'],
            ogDescription: $page['description'],
        );
    }

    private function pageView(Request $request, string $tool, ?Province $province): void
    {
        $this->analytics->record('page_view', [
            'tool_key' => $tool,
            'province' => $province?->value,
            'path' => $request->path(),
        ]);
    }

    private function canonicalProvince(string $provinceSlug, string $route): Province|RedirectResponse
    {
        $province = Province::fromSlug($provinceSlug);

        abort_unless($province, 404);

        if ($province->slug() !== $provinceSlug) {
            return redirect()->route($route, $province->slug(), 301);
        }

        return $province;
    }
}
