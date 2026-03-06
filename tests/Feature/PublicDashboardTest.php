<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class PublicDashboardTest extends TestCase
{
    public function test_public_dashboard_is_accessible_without_authentication(): void
    {
        $response = $this->get(route('dashboard.public'));

        $response->assertOk();
        $response->assertSee('Public Dashboard');
        $response->assertSee('Read-only public view');
    }

    public function test_public_dashboard_shows_only_public_data(): void
    {
        $category = Category::factory()->create(['name' => 'Fiction']);
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'title' => 'Test Book',
            'author' => 'Author Name',
        ]);

        $response = $this->get(route('dashboard.public'));

        $response->assertOk();
        $response->assertSee('Available Books');
        $response->assertSee('Categories');
        $response->assertSee('Completed Orders');
        $response->assertSee('Test Book');
        $response->assertSee('Author Name');
        $response->assertDontSee('Total Orders');
        $response->assertDontSee('Account Security');
    }

    public function test_authenticated_user_sees_link_to_full_dashboard_on_public_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.public'));

        $response->assertOk();
        $response->assertSee('Go to my dashboard');
    }

    public function test_authenticated_verified_user_redirects_to_role_dashboard_from_main_dashboard_route(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('customer.dashboard'));
    }
}
