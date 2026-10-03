<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminUsersPageTest extends TestCase
{
    public function test_users_page_uses_the_blade_view_and_dashboard_links_to_its_route(): void
    {
        $this->get('/admin/users')
            ->assertOk()
            ->assertViewIs('admin.users.index');

        $this->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.users'), false);
    }
}
