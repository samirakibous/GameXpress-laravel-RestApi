<?php

namespace Tests\Unit;


use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Laravel\Sanctum\Sanctum;


class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(PermissionsSeeder::class);

        // Create user and assign 'super_admin' role for testing
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Authenticate the admin for all tests
        Sanctum::actingAs($admin, ['*']);
    }

    public function test_index_returns_users_list()
    {
        // Create some users
        User::factory()->count(3)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'users' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function test_store_creates_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'product_manager',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson(route('users.store'), $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);

        // Check if user exists in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Check if role was assigned
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('product_manager'));
    }

    
}
