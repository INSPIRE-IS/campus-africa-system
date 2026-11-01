<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SupplementaryDocumentAuthorizationTest extends TestCase
{
    public function test_upload_requires_authentication()
    {
        $this->post('/my-documents/additional')->assertRedirect('/login');
    }

    public function test_document_viewer_requires_authentication()
    {
        $this->get('/documents/supplementary/1/view')->assertRedirect('/login');
    }

    public function test_file_route_requires_authentication()
    {
        $this->get('/documents/supplementary/1/file')->assertRedirect('/login');
    }

    public function test_review_requires_authentication()
    {
        $this->post('/documents/supplementary/1/review')->assertRedirect('/login');
    }

    public function test_admin_account_cannot_use_student_upload_endpoint()
    {
        $user = new User();
        $user->userRole = 1;

        $this->actingAs($user)
            ->post('/my-documents/additional')
            ->assertForbidden();
    }

    public function test_applicant_cannot_access_admin_document_viewer()
    {
        $user = new User();
        $user->userRole = 0;

        $this->actingAs($user)
            ->get('/documents/supplementary/1/view')
            ->assertForbidden();
    }

    public function test_applicant_cannot_review_supplementary_document()
    {
        $user = new User();
        $user->userRole = 0;

        $this->actingAs($user)
            ->post('/documents/supplementary/1/review')
            ->assertForbidden();
    }

    public function test_admin_without_document_permission_cannot_review_supplementary_document()
    {
        $user = new User();
        $user->userRole = 1;
        $user->role_approve_docs = 0;
        $user->role_sys_admin = 0;
        $user->role_super = 0;

        $this->actingAs($user)
            ->post('/documents/supplementary/1/review')
            ->assertForbidden();
    }

    public function test_rejection_requires_a_reason_before_document_lookup()
    {
        $user = new User();
        $user->userRole = 1;
        $user->role_approve_docs = 1;
        $user->role_sys_admin = 0;
        $user->role_super = 0;

        $this->actingAs($user)
            ->postJson('/documents/supplementary/1/review', [
                'status' => 'Rejected',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('rejection_reason');
    }

    public function test_disabled_mode_rejects_student_upload_before_validation()
    {
        config(['student_documents.additional_uploads_mode' => 'disabled']);

        $user = new User();
        $user->userRole = 0;

        $this->actingAs($user)
            ->post('/my-documents/additional')
            ->assertForbidden();
    }
}
