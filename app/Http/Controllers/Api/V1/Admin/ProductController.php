<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['images', 'category'])->get();

        return response()->json(['status' => 'success', 'products' => $products]);
    }

    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string',
            'slug' => 'nullable|string|unique:products,slug',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // dd($request->all());

        $slug = $request->slug ?? Str::slug($request->name);

        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->status,
            'category_id' => $request->category_id,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');

                // Crée une entrée dans la table 'product_images'
                $product->images()->create([
                    'image_url' => $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Produit créé avec succès',
            'product' => $product->load('images')
        ], 201);
    }


    public function show($id)
    {
        $product = Product::with(['images', 'category'])->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'status' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only(['name', 'slug', 'price', 'stock', 'status', 'category_id']);
        $product->update($data);

        // Si nouvelles images
        if ($request->hasFile('images')) {
            // Supprimer les anciennes images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_url);
                $image->delete();
            }

            // Ajouter les nouvelles images
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_url' => $path,
                    'is_primary' => $index === 0 ? true : false,
                ]);
            }
        }

        return response()->json(['message' => 'Produit mis à jour avec succès', 'product' => $product->load('images')]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_url);
            $image->delete();
        }

        $product->delete();

        return response()->json([ 'status' => 'success',
        'message' => 'Product deleted successfully',]);
    }
}
