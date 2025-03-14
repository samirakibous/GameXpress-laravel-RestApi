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
            'name' => 'Test Product24',
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

        // Enregistrer les images dans le disque fictif
        foreach ($data['images'] as $image) {
            $image->store('product_images', 'public');
        }


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
            'name' => 'Test Product24',
            'slug' => 'test-product24',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
        ]);

        $product = Product::where('name', 'Test Product24')->first();
        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'is_primary' => true,
        ]);
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
    }

    public function test_deleting_a_product()
    {
        // Fake file storage
        Storage::fake('public');
        $category = Category::factory()->create();

        // Créer un produit avec des images associées
        $product = Product::factory()->create([
            'name' => 'Test Product to Delete',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
            'status' => 'available',
        ]);

        // Créer une image principale et des images supplémentaires pour ce produit
        $primaryImage = \Illuminate\Http\UploadedFile::fake()->image('primary.jpg');
        $image1 = \Illuminate\Http\UploadedFile::fake()->image('image1.jpg');
        $image2 = \Illuminate\Http\UploadedFile::fake()->image('image2.jpg');

        // Stocker les images dans le disque fictif
        $primaryImage->store('product_images', 'public');
        $image1->store('product_images', 'public');
        $image2->store('product_images', 'public');

        // Créer les enregistrements d'images pour ce produit
        $product->images()->create([
            'image_url' => 'product_images/primary.jpg',
            'is_primary' => true
        ]);
        $product->images()->create([
            'image_url' => 'product_images/image1.jpg',
            'is_primary' => false
        ]);
        $product->images()->create([
            'image_url' => 'product_images/image2.jpg',
            'is_primary' => false
        ]);

        // Make DELETE request to delete the product
        $response = $this->deleteJson(route('products.destroy', $product->id));

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product deleted successfully',
            ]);

        // Vérifier que le produit a été supprimé de la base de données
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Test Product to Delete',
        ]);

        // Vérifier que les images associées au produit ont été supprimées du disque fictif
        Storage::disk('public')->assertMissing('product_images/primary.jpg');
        Storage::disk('public')->assertMissing('product_images/image1.jpg');
        Storage::disk('public')->assertMissing('product_images/image2.jpg');

        // Vérifier que les images ont été supprimées de la table `product_images`
        $this->assertDatabaseMissing('product_images', [
            'product_id' => $product->id,
        ]);
    }
}
