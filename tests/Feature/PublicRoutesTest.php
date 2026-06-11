<?php

namespace Tests\Feature;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke coverage for public-facing routes.
 *
 * The route sweep asserts no server error (status < 500) rather than a strict
 * 200, so the suite stays green for pages that legitimately redirect or 404 on
 * sparse data while still catching fatals, missing views, and Blade
 * exceptions. The targeted tests below assert exact behavior where data is
 * controlled. Tighten the sweep to assertSuccessful() per route once confirmed.
 */
class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** @return string[] */
    private function publicRoutes(): array
    {
        return [
            '/', '/search', '/history', '/archive', '/feature-political-prisoner-cost',
            '/timeline', '/annual-report', '/topics', '/calendar', '/birthdays', '/map',
            '/faq', '/staff', '/podcast', '/store', '/events', '/volunteer',
            '/prisoner-outreach', '/petitions', '/board-of-directors', '/partners', '/about',
        ];
    }

    public function test_public_routes_have_no_server_error(): void
    {
        foreach ($this->publicRoutes() as $route) {
            $status = $this->get($route)->status();
            $this->assertLessThan(500, $status, "GET {$route} returned HTTP {$status}");
        }
    }

    public function test_prisoner_profile_page_renders(): void
    {
        $prisoner = Prisoner::create(['name' => 'Profile Render Test']);

        $this->get('/prisoner/'.$prisoner->slug)
            ->assertSuccessful()
            ->assertSee('Profile Render Test');
    }

    public function test_prisoner_id_url_redirects_to_slug(): void
    {
        $prisoner = Prisoner::create(['name' => 'Redirect Test Prisoner']);

        $this->get('/prisoner/'.$prisoner->id)
            ->assertRedirect('/prisoner/'.$prisoner->slug);
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/this-slug-does-not-exist-12345')->assertNotFound();
    }

    public function test_tracker_renders_with_data(): void
    {
        $prisoner = Prisoner::create(['name' => 'Tracker Test Prisoner', 'in_custody' => true]);
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'charges' => 'Test charge',
            'arrest_date' => date('Y').'-01-01',
            'incarceration_date' => date('Y').'-01-01',
            'imprisoned_for_days' => 100,
        ]);

        $this->get('/feature-political-prisoner-cost')->assertSuccessful();
    }
}
