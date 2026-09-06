<?php

namespace App\Http\Controllers;

use App\Content\CalculatorHubContent;
use App\Services\Analytics\Analytics;
use App\Services\Seo\AnswerSummaryBuilder;
use App\Services\Seo\SeoPage;
use App\Support\Province;
use App\Support\ToolCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function __construct(
        private CalculatorHubContent $content,
        private Analytics $analytics,
        private AnswerSummaryBuilder $answers,
    ) {}

    public function hub(Request $request, string $tool): View
    {
        $page = $this->content->hub($tool);

        $this->analytics->record('page_view', [
            'tool_key' => $page['key'],
            'path' => $request->path(),
        ]);

        $faqs = $page['faqs'];

        return view('pages.tools.hub', [
            'page' => $page,
            'provinces' => Province::all(),
            'related' => ToolCatalog::relatedLinks(),
            'faqs' => $faqs,
            'summary' => $this->answers->hub($page['h1'].' — what does this estimate?', $page['intro']),
            'seo' => new SeoPage(
                title: $page['title'],
                description: $page['description'],
                canonical: route($page['route']),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => $page['h1'], 'url' => route($page['route'])],
                ],
                faqs: $faqs,
            ),
        ]);
    }

    public function conversion(Request $request, string $tool): View
    {
        $page = $this->content->conversion($tool);

        $this->analytics->record('page_view', [
            'tool_key' => $page['key'],
            'path' => $request->path(),
        ]);

        return view('pages.tools.conversion', [
            'page' => $page,
            'related' => ToolCatalog::relatedLinks(),
            'faqs' => $page['faqs'],
            'summary' => $this->answers->conversion($page['mode']),
            'seo' => new SeoPage(
                title: $page['title'],
                description: $page['description'],
                canonical: route($page['route']),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => $page['h1'], 'url' => route($page['route'])],
                ],
                faqs: $page['faqs'],
            ),
        ]);
    }
}
