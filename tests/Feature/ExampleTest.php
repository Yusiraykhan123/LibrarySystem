<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_dashboard_route_is_registered(): void
    {
        $this->assertSame(
            '/',
            app('router')->getRoutes()->getByName('dashboard')->uri()
        );
    }
}
