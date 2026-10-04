<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryCreatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_create_route_renders_the_admin_blade_page(): void
    {
        $this->get('/admin/categories/create')
            ->assertOk()
            ->assertViewIs('admin.categories.create')
            ->assertSee('دسته بندی ها / افزودن');
    }

    public function test_category_validation_errors_are_displayed_in_persian(): void
    {
        $this->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [
                'title' => '',
                'slug' => '',
            ])
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors(['title', 'slug']);

        $this->get(route('admin.categories.create'))
            ->assertSee('فیلد عنوان الزامی است.')
            ->assertSee('فیلد نامک الزامی است.');
    }

    public function test_category_form_saves_title_and_slug(): void
    {
        $this->post('/admin/categories', [
            'title' => 'Illustrations',
            'slug' => 'illustrations',
        ])->assertRedirect(route('admin.categories.create'));

        $this->assertDatabaseHas('categories', [
            'title' => 'Illustrations',
            'slug' => 'illustrations',
        ]);
    }
}
