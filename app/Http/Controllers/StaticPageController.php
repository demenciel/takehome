<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoPage;
use Illuminate\Contracts\View\View;

class StaticPageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'seo' => new SeoPage(
                title: 'About this Canadian paycheck calculator',
                description: 'How this take-home pay calculator works, where the tax data comes from, and what it does not do.',
                canonical: route('about'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'About', 'url' => route('about')],
                ],
            ),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'seo' => new SeoPage(
                title: 'Privacy',
                description: 'This calculator does not store your salary or ask for a SIN, employer, or bank details.',
                canonical: route('privacy'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Privacy', 'url' => route('privacy')],
                ],
            ),
        ]);
    }
}
