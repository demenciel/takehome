<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoPage;
use App\Services\Tax\TaxFreshness;
use App\Services\Tax\TaxRuleProvider;
use App\Support\Province;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            'usesGoogleAnalytics' => filled(config('analytics.measurement_id')),
            'usesLocalAnalytics' => (bool) config('analytics.enabled'),
            'adsEnabled' => (bool) config('ads.enabled'),
            'adsProvider' => (string) config('ads.provider'),
            'updated' => config('site.privacy_updated'),
            'seo' => new SeoPage(
                title: 'Privacy policy',
                description: 'How Paycheque.app handles calculator inputs, cookies, Google Analytics, and advertising.',
                canonical: route('privacy'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Privacy', 'url' => route('privacy')],
                ],
                includeApplication: false,
            ),
        ]);
    }

    public function terms(): View
    {
        return view('pages.terms', [
            'updated' => config('site.terms_updated'),
            'seo' => new SeoPage(
                title: 'Terms of use',
                description: 'Terms for using Paycheque.app, including that results are estimates and not tax advice.',
                canonical: route('terms'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Terms', 'url' => route('terms')],
                ],
                includeApplication: false,
            ),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'contactEmail' => config('site.contact_email'),
            'seo' => new SeoPage(
                title: 'Contact',
                description: 'Contact Paycheque.app about the calculator, tax data, privacy, or a problem with the site.',
                canonical: route('contact'),
                breadcrumbs: [
                    ['name' => __('common.home'), 'url' => route('home')],
                    ['name' => 'Contact', 'url' => route('contact')],
                ],
                includeApplication: false,
            ),
        ]);
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $to = (string) config('site.contact_email');

        Mail::raw(
            "From: {$data['name']} <{$data['email']}>\n\n{$data['message']}",
            function ($message) use ($to, $data): void {
                $message
                    ->to($to)
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Paycheque.app contact form');
            },
        );

        return back()->with('status', 'Thanks. Your message was sent. Do not include a SIN, bank details, or a full tax return.');
    }
}
