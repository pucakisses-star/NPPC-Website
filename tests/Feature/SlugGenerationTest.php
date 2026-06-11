<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks in the HasSlug fixes (#982, #983): slugs derive from title-or-name,
 * are never empty, and stay unique so duplicate titles/names don't violate
 * the UNIQUE slug constraint (which previously threw on the second record).
 */
class SlugGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_slug_is_generated_from_name(): void
    {
        // Product has `name` (not `title`) — regression for the empty-slug bug.
        $product = Product::create(['name' => 'Solidarity T-Shirt', 'price' => 20]);

        $this->assertSame('solidarity-t-shirt', $product->slug);
    }

    public function test_duplicate_product_names_get_distinct_slugs(): void
    {
        $first = Product::create(['name' => 'Movement Zine', 'price' => 5]);
        $second = Product::create(['name' => 'Movement Zine', 'price' => 5]);

        $this->assertSame('movement-zine', $first->slug);
        $this->assertSame('movement-zine-2', $second->slug);
    }

    public function test_duplicate_page_titles_do_not_collide(): void
    {
        $first = Page::create(['title' => 'About Us', 'header_image' => 'a.jpg', 'body' => 'one']);
        $second = Page::create(['title' => 'About Us', 'header_image' => 'b.jpg', 'body' => 'two']);

        $this->assertSame('about-us', $first->slug);
        $this->assertSame('about-us-2', $second->slug);
    }

    public function test_duplicate_event_titles_do_not_collide(): void
    {
        $first = Event::create(['title' => 'Annual Gala', 'event_date' => '2026-05-01']);
        $second = Event::create(['title' => 'Annual Gala', 'event_date' => '2027-05-01']);

        $this->assertSame('annual-gala', $first->slug);
        $this->assertSame('annual-gala-2', $second->slug);
    }

    public function test_category_blank_slug_autogenerates_from_title(): void
    {
        // Regression for the blank-slug save failure (#983): Category had a
        // UNIQUE, NOT NULL slug column but no auto-generation.
        $category = Category::create(['title' => 'Movement News']);

        $this->assertSame('movement-news', $category->slug);
    }

    public function test_explicitly_provided_slug_is_respected(): void
    {
        $category = Category::create(['title' => 'Movement News', 'slug' => 'custom-slug']);

        $this->assertSame('custom-slug', $category->slug);
    }
}
