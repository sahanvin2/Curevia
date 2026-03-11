<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function productsIndex()
    {
        $products = Product::orderByDesc('created_at')->get();
        return view('admin.products.index', compact('products'));
    }

    public function productsCreate()
    {
        return view('admin.products.edit', ['product' => new Product()]);
    }

    public function productsStore(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:products,slug',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'original_price'=> 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'badge'         => 'nullable|string|max:50',
            'image'         => 'nullable|url',
            'affiliate_url' => 'nullable|url',
            'is_active'     => 'boolean',
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function productsEdit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function productsUpdate(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'original_price'=> 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'badge'         => 'nullable|string|max:50',
            'image'         => 'nullable|url',
            'affiliate_url' => 'nullable|url',
            'is_active'     => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function productsDestroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
