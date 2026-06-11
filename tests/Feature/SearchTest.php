<?php

namespace Tests\Feature;

use App\Models\Prisoner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks in the search-link fix (#981): a prisoner search result links to that
 * prisoner's own profile, not the generic /database index.
 */
class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_prisoner_search_result_links_to_profile(): void
    {
        $prisoner = Prisoner::create([
            'name' => 'Test Searchable Prisoner',
            'description' => 'A biography used by the search test.',
        ]);

        $response = $this->get('/search?q=Searchable');

        $response->assertSuccessful();
        $response->assertSee('/prisoner/'.$prisoner->slug, false);
    }

    public function test_empty_search_renders(): void
    {
        $this->get('/search')->assertSuccessful();
    }
}
