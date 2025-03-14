<?php

namespace Tests\Unit;
use Illuminate\Foundation\Testing\TestCase;



class CategoryControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_create_category(): void
    {
        $request = $this -> post('/api/v1/admin/categories',['name'=> 'Sports',  'slug' => 'sports']);
        $request->assertStatus(201);
    }
    public function test_get_all_categories(): void
    {
        $request = $this -> get('/api/v1/admin/categories');
        $request->assertStatus(200);
    }

    public function test_update_category(): void
    {
        $request = $this -> put('/api/v1/admin/categories/1',['name'=> 'Old Category',  'slug' => 'old-category']);
        $request->assertStatus(200);
    }

    public function test_delete_category(): void
    {
        $request = $this -> delete('/api/v1/admin/categories/1');
        $request->assertStatus(200);
    }
}
