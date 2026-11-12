<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyPageTest extends TestCase
{
    public function test_privacy_policy_defaults_to_the_campus_africa_notice(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Campus Africa Website Privacy Notice')
            ->assertSee('Doomscroller App');
    }

    public function test_doomscroller_policy_has_a_direct_public_url(): void
    {
        $this->get('/privacy-policy/doomscroller')
            ->assertOk()
            ->assertSee('Doomscroller - Privacy Policy')
            ->assertSee('doomscroller.support@gmail.com');
    }

    public function test_unknown_policy_is_not_found(): void
    {
        $this->get('/privacy-policy/unknown-policy')->assertNotFound();
    }
}
