<?php

namespace App\Http\Controllers;

use App\Support\Province;
use App\Support\SalaryCatalog;
use App\Support\ToolCatalog;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            route('home'),
            route('paycheck.canada'),
            route('about'),
            route('methodology'),
            route('tax-rates'),
            route('privacy'),
        ];

        foreach (ToolCatalog::hubs() as $hub) {
            if ($hub['route'] === 'paycheck.canada') {
                continue;
            }

            $urls[] = route($hub['route']);
        }

        foreach (Province::all() as $province) {
            $urls[] = route('paycheck.province', $province->slug());

            foreach (SalaryCatalog::amounts() as $salary) {
                if (SalaryCatalog::allows($province, $salary)) {
                    $urls[] = route('paycheck.salary', [$province->slug(), $salary]);
                }
            }
        }

        return response()
            ->view('seo.sitemap', ['urls' => array_values(array_unique($urls))])
            ->header('Content-Type', 'application/xml');
    }
}
