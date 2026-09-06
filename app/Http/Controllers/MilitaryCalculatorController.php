<?php

namespace App\Http\Controllers;

use App\Content\MilitaryPageContent;
use App\Services\Analytics\Analytics;
use App\Services\Military\MilitaryPayRateProvider;
use App\Services\Seo\SeoPage;
use App\Support\ToolCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MilitaryCalculatorController extends Controller
{
    public function __invoke(Request $request, MilitaryPageContent $content, MilitaryPayRateProvider $rates, Analytics $analytics): View
    {
        $page = $content->page();

        $analytics->record('page_view', [
            'tool_key' => 'military',
            'path' => $request->path(),
        ]);

        return view('pages.tools.military', [
            'page' => $page,
            'sources' => $rates->sources(),
            'related' => ToolCatalog::relatedLinks(context: 'military'),
            'faqs' => $page['faqs'],
            'seo' => new SeoPage(
                title: $page['title'],
                description: $page['description'],
                canonical: route('tools.military'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => $page['h1'], 'url' => route('tools.military')],
                ],
                faqs: $page['faqs'],
                ogTitle: $page['title'],
                ogDescription: $page['description'],
            ),
        ]);
    }
}
