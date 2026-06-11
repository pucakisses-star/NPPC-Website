<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_loads_without_a_server_error(): void
    {
        $this->assertLessThan(500, $this->get('/')->status());
    }
}
