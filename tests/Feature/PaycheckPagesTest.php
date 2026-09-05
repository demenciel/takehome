<?php

use App\Livewire\PaycheckCalculator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the homepage with the calculator and SEO tags', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('How much of your salary do you actually take home?', false)
        ->assertSee('Calculate my take-home', false)
        ->assertSee('<title>Canadian Paycheck Calculator', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@context":"https://schema.org"', false)
        ->assertDontSee('$__contextArgs', false);
});

it('renders the national calculator page', function () {
    $this->get('/canada-paycheck-calculator')
        ->assertOk()
        ->assertSee('Canada Paycheck Calculator', false)
        ->assertSee('Province paycheck calculators', false);
});

it('renders unique Ontario province content', function () {
    $this->get('/ontario-paycheck-calculator')
        ->assertOk()
        ->assertSee('Ontario Paycheck Calculator', false)
        ->assertSee('Ontario Health Premium', false)
        ->assertSee('TD1ON', false)
        ->assertSee('$12,989', false);
});

it('renders unique Quebec province content', function () {
    $this->get('/quebec-paycheck-calculator')
        ->assertOk()
        ->assertSee('QPP', false)
        ->assertSee('QPIP', false)
        ->assertSee('Revenu Québec', false);
});

it('renders an indexable popular salary page with a server-rendered result', function () {
    $this->get('/ontario/80000-salary')
        ->assertOk()
        ->assertSee('$80,000 salary after tax in Ontario', false)
        ->assertSee('Estimated take-home', false)
        ->assertSee('rel="canonical"', false);
});

it('does not create thin salary pages outside the configured list', function () {
    $this->get('/ontario/80123-salary')->assertNotFound();
});

it('exposes sitemap and robots files', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('ontario-paycheck-calculator', false)
        ->assertSee('80000-salary', false);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Sitemap:', false);
});

it('calculates take-home pay through Livewire', function () {
    Livewire::test(PaycheckCalculator::class)
        ->set('salary', '75000')
        ->set('province', 'NB')
        ->set('frequency', 'biweekly')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSet('hasCalculated', true)
        ->assertSee('Your estimated take-home pay');
});

it('shows a friendly error for a missing salary', function () {
    Livewire::test(PaycheckCalculator::class)
        ->set('salary', '')
        ->set('province', 'ON')
        ->call('calculate')
        ->assertHasErrors(['salary'])
        ->assertSee('Enter a valid salary amount.');
});

it('protects the admin dashboard', function () {
    $this->get('/admin')->assertRedirect('/admin/login');

    $user = User::factory()->create();
    $this->actingAs($user)->get('/admin')->assertForbidden();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Admin');
});
