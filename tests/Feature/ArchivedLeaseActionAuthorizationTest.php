<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ArchivedLeaseActionAuthorizationTest extends TestCase
{
    public function test_archived_lease_action_requires_authentication()
    {
        $this->get('/documents/archived-lease/action/1')->assertRedirect('/login');
    }

    public function test_applicant_cannot_access_archived_lease_action()
    {
        $user = new User();
        $user->userRole = 0;

        $this->actingAs($user)
            ->get('/documents/archived-lease/action/1')
            ->assertForbidden();
    }
}
