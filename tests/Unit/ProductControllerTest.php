<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class ProductControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Create user and assign 'product_manager' role
        $user = User::factory()->create();
        $user->assignRole('product_manager');

        // Authenticate the user for all tests
        Sanctum::actingAs($user, ['*']);
    }

    public function test_getting_all_products()
    {
        $response = $this->getJson(route('products.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'products' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'price',
                        'stock',
                        'category_id',
                        'status',
                        'images' => [
                            '*' => [
                                'id',
                                'image_url',
                                'is_primary',
                                'product_id',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    // test products
    public function test_can_store_a_product()
    {
        // Fake file storage
        Storage::fake('public');
        $category = Category::factory()->create();

        // Prepare product data
        $data = [
            'name' => 'Test Product22',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
            'primary_image' => \Illuminate\Http\UploadedFile::fake()->image('primary.jpg'),
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('image1.jpg'),
                \Illuminate\Http\UploadedFile::fake()->image('image2.jpg'),
            ],
            'status' => 'available',
        ];

        // Make POST request to store the product
        $response = $this->postJson(route('products.store'), $data);

        // Assert successful response and structure
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'product' => [
                    'id',
                    'name',
                    'slug',
                    'price',
                    'stock',
                    'category_id',
                    'status',
                    'images' => [
                        '*' => [
                            'id',
                            'product_id',
                            'image_url',
                            'is_primary',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'created_at',
                    'updated_at'
                ]
            ]);

        // Verify product is in the database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product22',
            'slug' => 'test-product22',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
        ]);

        $product = Product::where('name', 'Test Product22')->first();

        // // Check primary image exists using the fake disk
        // $primaryImage = $product->images()->where('is_primary', true)->first();
        // $this->assertNotNull($primaryImage, 'Primary image not found');
        // Storage::disk('public')->assertExists($primaryImage->image_path);

        // Check other images using the fake disk
        // $otherImages = $product->images()->where('is_primary', false)->get();
        // $this->assertCount(2, $otherImages, 'Expected 2 non-primary images');

        // foreach ($otherImages as $image) {
        //     Storage::disk('public')->assertExists($image->image_path);
        // }
    }

    // update test
    public function test_updating_a_product()
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Original Product',
            'category_id' => $category->id,
            'stock' => 5
        ]);

        // Create a primary image
        $product->images()->create([
            'image_path' => 'test/primary.jpg',
            'is_primary' => true
        ]);

        // Create additional images
        $product->images()->create([
            'image_path' => 'test/image1.jpg',
            'is_primary' => false
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'price' => 199.99,
            'stock' => 20,
            'category_id' => $newCategory->id,
            'primary_image' => \Illuminate\Http\UploadedFile::fake()->image('new_primary.jpg'),
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('new_image1.jpg'),
                \Illuminate\Http\UploadedFile::fake()->image('new_image2.jpg'),
                \Illuminate\Http\UploadedFile::fake()->image('new_image3.jpg'),
            ],
        ];

        $response = $this->putJson(route('products.update', $product->id), $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'product' => [
                    'id',
                    'name',
                    'slug',
                    'price',
                    'stock',
                    'category_id',
                    'status',
                    'images' => [
                        '*' => [
                            'id',
                            'image_url',
                            'is_primary',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'created_at',
                    'updated_at'
                ]
            ]);

        // Check product was updated in database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'slug' => 'updated-product',
            'price' => 199.99,
            'stock' => 20,
            'category_id' => $newCategory->id,
            'status' => 'available'
        ]);

        // Refresh product from database
        $product->refresh();

        // // Verify we have one primary image
        // $primaryImage = $product->images()->where('is_primary', true)->first();
        // $this->assertNotNull($primaryImage);
        // Storage::disk('public')->assertExists($primaryImage->image_path);

        // // Verify we have 3 non-primary images
        // $otherImages = $product->images()->where('is_primary', false)->get();
        // $this->assertCount(3, $otherImages);

        // foreach ($otherImages as $image) {
        //     Storage::disk('public')->assertExists($image->image_path);
        // }
    }
}
