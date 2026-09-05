<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\PaycheckController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaycheckController::class, 'home'])->name('home');
Route::get('/canada-paycheck-calculator', [PaycheckController::class, 'canada'])->name('paycheck.canada');
Route::get('/about', [StaticPageController::class, 'about'])->name('about');
Route::get('/privacy', [StaticPageController::class, 'privacy'])->name('privacy');
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
    ->name('paycheck.province');

Route::get('/{provinceSlug}/{salary}-salary', [PaycheckController::class, 'salary'])
    ->whereNumber('salary')
    ->name('paycheck.salary');

Route::get('/{provinceSlug}/salary/{salary}', [PaycheckController::class, 'salary'])
    ->whereNumber('salary')
    ->name('paycheck.salary.alt');
