<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Menampilkan semua produk
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('itemsPerPage') ?? 10;
            $products = Product::filter($request->all())->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

            return ResponseHelper::success($products->items(), 'Products retrieved successfully', total: $products->total());
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve products', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'is_available' => 'boolean',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                $path = $file->store('products', 'public');
                $validated['image_path'] = $path;
            }

            $product = Product::create($validated);

            return ResponseHelper::success($product, 'Product created successfully');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to create product', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Menampilkan detail produk
    public function show(Request $request)
    {
        try {
            $product = Product::findOrFail($request->query('id'));
            return ResponseHelper::success($product, 'Product retrieved successfully');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve product', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Mengupdate produk
    public function update(Request $request)
    {
        try {
            $product = Product::findOrFail($request->query('id'));

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'sometimes|required|string|max:100',
                'price' => 'sometimes|required|numeric|min:0',
                'is_available' => 'boolean',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                $path = $file->store('products', 'public');
                $validated['image_path'] = $path;
            }

            $product->update($validated);

            return ResponseHelper::success($product, 'Product updated successfully');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update product', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Menghapus produk
    public function destroy(Request $request)
    {
        try {
            $product = Product::findOrFail($request->query('id'));
            $product->delete();

            return ResponseHelper::success($product, 'Product deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to delete product', 500, ['exception' => $e->getMessage()]);
        }
    }
}
