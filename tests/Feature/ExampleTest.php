<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // "/" giờ redirect về /{locale} — kiểm tra route có locale
        $defaultLocale = config('localization.default_locale', 'en');
        $response = $this->get("/{$defaultLocale}");

        $response->assertStatus(200);
    }
}
