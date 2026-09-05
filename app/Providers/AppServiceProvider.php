<?php

namespace App\Providers;

use App\Services\Tax\TaxFreshness;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaxFreshness::class);
    }

    public function boot(): void
    {
        View::composer('*', function ($view): void {
            if (! $view->offsetExists('taxFreshness')) {
                $view->with('taxFreshness', app(TaxFreshness::class));
            }
        });
    }
}
