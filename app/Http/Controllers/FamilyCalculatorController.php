<?php

namespace App\Http\Controllers;

use App\Content\BabyCostPageContent;
use App\Content\EiBenefitsPageContent;
use App\Content\ParentalLeavePageContent;
use App\Services\Analytics\Analytics;
use App\Services\Family\BabyBudgetService;
use App\Services\Family\ParentalBenefitsService;
use App\Services\Family\ParentalLeaveProjectionService;
use App\Services\Seo\AnswerSummaryBuilder;
use App\Services\Seo\SeoPage;
use App\Support\Money;
use App\Support\Province;
use App\Support\ToolCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FamilyCalculatorController extends Controller
{
    public function __construct(
        private Analytics $analytics,
        private ParentalLeavePageContent $parentalContent,
        private EiBenefitsPageContent $eiContent,
        private BabyCostPageContent $babyContent,
        private ParentalLeaveProjectionService $projection,
        private ParentalBenefitsService $benefits,
        private BabyBudgetService $babyBudget,
        private AnswerSummaryBuilder $answers,
    ) {}

    public function parental(Request $request): View
    {
        $page = $this->parentalContent->page();
        $example = $this->projection->project([
            'province' => Province::Ontario,
            'salary' => Money::fromDollars(80000),
            'frequency' => 'biweekly',
            'who' => 'birth_parent',
            'leave_type' => 'maternity_standard',
            'planned_weeks' => 50,
        ]);
        $faqs = $this->answers->parentalFaqs($page['faqs'], $example);

        $this->pageView($request, 'parental');

        return view('pages.tools.parental-leave', [
            'page' => $page,
            'example' => $example,
            'summary' => $this->answers->parental($example),
            'sources' => $this->benefits->sources(),
            'reviewed' => $this->benefits->lastReviewedLabel(),
            'related' => ToolCatalog::relatedLinks(context: 'parental'),
            'faqs' => $faqs,
            'seo' => $this->seo($page, route('tools.parental'), $page['h1'], $faqs),
        ]);
    }

    public function eiBenefits(Request $request): View
    {
        $page = $this->eiContent->page();
        $example = $this->projection->eiOnly(Money::fromDollars(80000), 'maternity', 15, false);
        $faqs = $this->answers->eiBenefitsFaqs($page['faqs'], $example);

        $this->pageView($request, 'ei_benefits');

        return view('pages.tools.ei-benefits', [
            'page' => $page,
            'example' => $example,
            'summary' => $this->answers->eiBenefits($example),
            'sources' => $this->benefits->sources(),
            'reviewed' => $this->benefits->lastReviewedLabel(),
            'related' => ToolCatalog::relatedLinks(context: 'ei_benefits'),
            'faqs' => $faqs,
            'seo' => $this->seo($page, route('tools.ei_benefits'), $page['h1'], $faqs),
        ]);
    }

    public function baby(Request $request): View
    {
        $page = $this->babyContent->page();
        $example = $this->babyBudget->summarize(
            $this->babyBudget->defaultStartupState(),
            $this->babyBudget->defaultRecurringState(),
        );
        $faqs = $this->answers->babyFaqs($page['faqs'], $example);

        $this->pageView($request, 'baby');

        return view('pages.tools.baby-cost', [
            'page' => $page,
            'example' => $example,
            'summary' => $this->answers->baby($example),
            'ccb' => $this->babyBudget->ccbReference(),
            'reviewed' => $this->babyBudget->lastReviewedLabel(),
            'related' => ToolCatalog::relatedLinks(context: 'baby'),
            'faqs' => $faqs,
            'seo' => $this->seo($page, route('tools.baby'), $page['h1'], $faqs),
        ]);
    }

    /**
     * @param  array<string, mixed>  $page
     * @param  list<array{question: string, answer: string}>  $faqs
     */
    private function seo(array $page, string $canonical, string $crumb, array $faqs): SeoPage
    {
        return new SeoPage(
            title: $page['title'],
            description: $page['description'],
            canonical: $canonical,
            breadcrumbs: [
                ['name' => __('common.home'), 'url' => route('home')],
                ['name' => $crumb, 'url' => $canonical],
            ],
            faqs: $faqs,
            ogTitle: $page['title'],
            ogDescription: $page['description'],
        );
    }

    private function pageView(Request $request, string $tool): void
    {
        $this->analytics->record('page_view', [
            'tool_key' => $tool,
            'path' => $request->path(),
        ]);
    }
}
