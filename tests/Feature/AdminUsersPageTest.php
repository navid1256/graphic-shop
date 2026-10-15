<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUsersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_uses_the_blade_view_and_dashboard_links_to_its_route(): void
    {
        $this->get('/admin/users')
            ->assertOk()
            ->assertViewIs('admin.users.index');

        $this->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.users.all'), false);
    }

    public function test_add_user_page_renders_its_error_message_partial(): void
    {
        $this->get('/admin/users/create')
            ->assertOk()
            ->assertViewIs('admin.users.add');
    }

    public function test_user_creation_rejects_mobile_numbers_outside_the_allowed_length(): void
    {
        $this->from('/admin/users/create')
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'mobile' => '123',
                'role' => 'user',
            ])
            ->assertRedirect('/admin/users/create')
            ->assertSessionHasErrors('mobile');
    }

    public function test_user_can_be_created_without_a_password(): void
    {
        $this->post('/admin/users', [
            'name' => 'Guest Customer',
            'email' => 'guest@example.com',
            'mobile' => '09123456789',
            'role' => 'user',
        ])->assertRedirect(route('admin.users.all'));

        $this->assertDatabaseHas('users', [
            'email' => 'guest@example.com',
            'mobile' => '09123456789',
            'role' => 'user',
            'password' => null,
        ]);
    }

    public function test_user_password_is_hashed_when_one_is_provided(): void
    {
        $this->post('/admin/users', [
            'name' => 'Registered Customer',
            'email' => 'registered@example.com',
            'mobile' => '09123456780',
            'role' => 'user',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.all'));

        $user = User::where('email', 'registered@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_users_table_renders_each_user_row_with_its_own_actions(): void
    {
        $firstUser = User::factory()->create([
            'name' => 'First Customer',
            'email' => 'first@example.com',
            'mobile' => '09123456781',
            'role' => 'user',
        ]);
        $secondUser = User::factory()->create([
            'name' => 'Second Customer',
            'email' => 'second@example.com',
            'mobile' => '09123456782',
            'role' => 'user',
        ]);

        $this->get('/admin/users')
            ->assertOk()
            ->assertSee('First Customer')
            ->assertSee('Second Customer')
            ->assertSee(route('admin.users.edit', $firstUser->id), false)
            ->assertSee(route('admin.users.edit', $secondUser->id), false)
            ->assertSee(route('admin.users.delete', $firstUser->id), false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_user_creation_success_message_is_shown_on_the_users_page(): void
    {
        $this->followingRedirects()
            ->post('/admin/users', [
                'name' => 'Flash Message User',
                'email' => 'flash@example.com',
                'mobile' => '09123456783',
                'role' => 'user',
            ])
            ->assertOk()
            ->assertSee('User created successfully.');
    }

    public function test_error_flash_message_is_shown_on_the_add_user_page(): void
    {
        $this->withSession(['error' => 'Failed to create user.'])
            ->get('/admin/users/create')
            ->assertOk()
            ->assertSee('Failed to create user.');
    }

    public function test_user_can_be_deleted_using_the_delete_route(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456784',
            'role' => 'user',
        ]);

        $this->delete(route('admin.users.delete', $user->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_edit_user_page_receives_the_selected_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Edit User',
            'mobile' => '09123456785',
            'role' => 'user',
        ]);

        $this->get(route('admin.users.edit', $user->id))
            ->assertOk()
            ->assertViewIs('admin.users.edit')
            ->assertViewHas('user', fn (User $viewUser) => $viewUser->is($user));
    }

    public function test_edit_form_uses_post_with_put_method_spoofing(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456786',
            'role' => 'user',
        ]);

        $this->get(route('admin.users.edit', $user->id))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('name="_method" value="PUT"', false);
    }

    public function test_user_can_be_updated_using_put_method_spoofing(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456787',
            'role' => 'user',
        ]);

        $this->post(route('admin.users.update', $user->id), [
            '_method' => 'PUT',
            'name' => 'Updated User',
            'email' => $user->email,
            'mobile' => $user->mobile,
            'role' => $user->role,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
        ]);
    }
}
