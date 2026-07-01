<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders_with_brand_pitch_and_process(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Tracción Online')
            ->assertSee('anonimato digital')
            ->assertSee('Sientes que haces, pero no te ven')
            ->assertSee('Sesión inicial de estrategia')
            ->assertSee('Agenda tu sesión de estrategia');
    }

    public function test_primary_cta_uses_the_configured_booking_url(): void
    {
        config(['studio.booking_url' => 'https://cal.com/traccion/estrategia']);

        $this->get('/')->assertSee('https://cal.com/traccion/estrategia');
    }
}
