<?php

namespace App\Http\Controllers;

use App\Support\Province;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            route('home'),
            route('paycheck.canada'),
            route('about'),
            route('privacy'),
        ];

        foreach (Province::all() as $province) {
            $urls[] = route('paycheck.province', $province->slug());

            foreach (config('tools.popular_salaries') as $salary) {
                $urls[] = route('paycheck.salary', [$province->slug(), $salary]);
            }
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
