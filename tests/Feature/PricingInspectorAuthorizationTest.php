<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PricingInspectorAuthorizationTest extends TestCase
{
    public function test_pricing_inspector_requires_authentication()
    {
        $this->get('/metrics-pricing')->assertRedirect('/login');
    }

    public function test_pricing_inspector_requires_super_role()
    {
        $user = new User();
        $user->role_super = 0;

        $this->actingAs($user)->get('/metrics-pricing')->assertForbidden();
    }

    public function test_super_user_can_open_pricing_inspector()
    {
        $user = new User();
        $user->role_super = 1;

        $this->actingAs($user)
            ->get('/metrics-pricing')
            ->assertOk()
            ->assertSee('Pricing Inspector');
    }
}
