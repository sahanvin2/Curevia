<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('category', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'All') {
            $query->where('category', $request->input('category'));
        }

        $products = $query->orderByDesc('rating')->paginate(6);
        $productCategories = Product::where('is_active', true)->distinct()->pluck('category');

        if ($request->ajax()) {
            $html = '';
            foreach ($products as $p) {
                $html .= view('shop._card', compact('p'))->render();
            }
            return response()->json(['html' => $html, 'next' => $products->nextPageUrl()]);
        }

        return view('shop.index', compact('products', 'productCategories'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $related = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'related'));
    }
}
