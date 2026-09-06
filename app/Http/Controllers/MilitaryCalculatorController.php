<?php

namespace App\Http\Controllers;

use App\Content\MilitaryPageContent;
use App\Services\Analytics\Analytics;
use App\Services\Military\MilitaryPayRateProvider;
use App\Services\Military\MilitarySalaryCalculator;
use App\Services\Seo\AnswerSummaryBuilder;
use App\Services\Seo\SeoPage;
use App\Support\PayFrequency;
use App\Support\Province;
use App\Support\ToolCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MilitaryCalculatorController extends Controller
{
    public function __invoke(
        Request $request,
        MilitaryPageContent $content,
        MilitaryPayRateProvider $rates,
        MilitarySalaryCalculator $calculator,
        AnswerSummaryBuilder $answers,
        Analytics $analytics,
    ): View {
        $page = $content->page();
        $example = $calculator->calculate(
            'regular',
            'corporal',
            '1',
            Province::Ontario,
            PayFrequency::Monthly,
        );
        $faqs = $answers->militaryFaqs($page['faqs'], $example);

        $analytics->record('page_view', [
            'tool_key' => 'military',
            'path' => $request->path(),
        ]);

        return view('pages.tools.military', [
            'page' => $page,
            'sources' => $rates->sources(),
            'example' => $example,
            'summary' => $answers->military($example),
            'related' => ToolCatalog::relatedLinks(context: 'military'),
            'faqs' => $faqs,
            'seo' => new SeoPage(
                title: $page['title'],
                description: $page['description'],
                canonical: route('tools.military'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => $page['h1'], 'url' => route('tools.military')],
                ],
                faqs: $faqs,
                ogTitle: $page['title'],
                ogDescription: $page['description'],
            ),
        ]);
    }
}
