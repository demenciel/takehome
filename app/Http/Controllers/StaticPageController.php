<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoPage;
use App\Services\Tax\TaxFreshness;
use App\Services\Tax\TaxRuleProvider;
use App\Support\Province;
use Illuminate\Contracts\View\View;

class StaticPageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'seo' => new SeoPage(
                title: 'About Paycheque.app',
                description: 'Paycheque.app is an independent Canadian take-home pay calculator. It is not a government service.',
                canonical: route('about'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'About', 'url' => route('about')],
                ],
                includeApplication: false,
            ),
        ]);
    }

    public function methodology(TaxFreshness $freshness): View
    {
        return view('pages.methodology', [
            'sources' => $freshness->sources(),
            'edition' => $freshness->edition(),
            'seo' => new SeoPage(
                title: 'How paycheck calculations work',
                description: 'Methodology for Paycheque.app: CRA T4127 payroll formulas, Quebec differences, pay frequencies, and why an employer paycheque can differ.',
                canonical: route('methodology'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Methodology', 'url' => route('methodology')],
                ],
                includeApplication: false,
            ),
        ]);
    }

    public function taxRates(TaxRuleProvider $provider): View
    {
        $rules = $provider->forYear();

        return view('pages.tax-rates', [
            'rules' => $rules,
            'provinces' => Province::all(),
            'seo' => new SeoPage(
                title: $rules->year.' Canadian payroll tax rates used on this site',
                description: 'Federal brackets, CPP, EI, and provincial basic amounts used by Paycheque.app for the '.$rules->year.' tax year.',
                canonical: route('tax-rates'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Tax rates', 'url' => route('tax-rates')],
                ],
                includeApplication: false,
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
                includeApplication: false,
            ),
        ]);
    }
}
