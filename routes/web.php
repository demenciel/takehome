<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\PaycheckController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\ToolController;
use App\Support\Province;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaycheckController::class, 'home'])->name('home');
Route::get('/canada-paycheck-calculator', [PaycheckController::class, 'canada'])->name('paycheck.canada');
Route::get('/canada-paycheque-calculator', function () {
    return redirect()->route('paycheck.canada', status: 301);
});
Route::get('/paycheque-calculator', [ToolController::class, 'hub'])->defaults('tool', 'paycheque')->name('tools.paycheque');
Route::get('/take-home-pay-calculator', [ToolController::class, 'hub'])->defaults('tool', 'take_home')->name('tools.take_home');
Route::get('/salary-after-tax-calculator', [ToolController::class, 'hub'])->defaults('tool', 'salary_after_tax')->name('tools.salary_after_tax');
Route::get('/hourly-to-salary-calculator', [ToolController::class, 'conversion'])->defaults('tool', 'hourly_to_salary')->name('tools.hourly_to_salary');
Route::get('/salary-to-hourly-calculator', [ToolController::class, 'conversion'])->defaults('tool', 'salary_to_hourly')->name('tools.salary_to_hourly');
Route::get('/biweekly-pay-calculator', [ToolController::class, 'hub'])->defaults('tool', 'biweekly')->name('tools.biweekly');
Route::get('/weekly-pay-calculator', [ToolController::class, 'hub'])->defaults('tool', 'weekly')->name('tools.weekly');

Route::get('/about', [StaticPageController::class, 'about'])->name('about');
Route::get('/methodology', [StaticPageController::class, 'methodology'])->name('methodology');
Route::get('/tax-rates', [StaticPageController::class, 'taxRates'])->name('tax-rates');
Route::get('/privacy', [StaticPageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [StaticPageController::class, 'terms'])->name('terms');
Route::get('/contact', [StaticPageController::class, 'contact'])->name('contact');
Route::post('/contact', [StaticPageController::class, 'storeContact'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::prefix('admin')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->middleware('guest')->name('admin.login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware(['guest', 'throttle:10,1'])
        ->name('admin.login.store');
    Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('admin.logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', DashboardController::class)->name('admin.dashboard');
    });
});

Route::get('/{provinceSlug}-paycheck-calculator', [PaycheckController::class, 'province'])
    ->where('provinceSlug', Province::slugPattern())
    ->name('paycheck.province');

Route::get('/{provinceSlug}-paycheque-calculator', function (string $provinceSlug) {
    $province = Province::fromSlug($provinceSlug);

    abort_unless($province, 404);

    return redirect()->route('paycheck.province', $province->slug(), 301);
})->where('provinceSlug', Province::slugPattern());

Route::get('/{provinceSlug}/{salary}-salary', [PaycheckController::class, 'salary'])
    ->where('provinceSlug', Province::slugPattern())
    ->whereNumber('salary')
    ->name('paycheck.salary');

Route::get('/{provinceSlug}/salary/{salary}', function (string $provinceSlug, int $salary) {
    $province = Province::fromSlug($provinceSlug);

    abort_unless($province, 404);

    return redirect()->route('paycheck.salary', [$province->slug(), $salary], 301);
})->where('provinceSlug', Province::slugPattern())->whereNumber('salary')->name('paycheck.salary.alt');
