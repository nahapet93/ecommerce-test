<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();

        $text = $request->query->get('text');
        $priceFrom = $request->query->get('priceFrom');
        $priceTo = $request->query->get('priceTo');
        $categoryId = $request->query->get('category_id');

        if ($text) {
            $query = $query->whereFullText(['name', 'description'], $text, ['expanded' => true]);
        }

        if ($priceFrom ) {
            $query = $query->where('price', '>=', $priceFrom);
        }

        if ($priceTo) {
            $query = $query->where('price', '<=', $priceTo);
        }

        if ($categoryId) {
            $query = $query->where('category_id', '=', $categoryId);
        }

        $products = $query->with('category')->get();
        $categories = Category::all();

        return view('products.index', compact(
            'products',
            'categories',
            'text',
            'priceFrom',
            'priceTo',
            'categoryId'
        ));
    }

    public function create(): View
    {
        $product = null;
        $mediaItems = null;
        $categories = Category::all();
        return view('products.form', compact('product', 'categories', 'mediaItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'category_id' => $request->validated('category_id')
        ]);

        if ($request->file('images')) {
            $product->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection();
                });
        }

        return redirect(route('products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $product = Product::find($id);
        $mediaItems = $product->getMedia();
        $categories = Category::all();
        return view('products.form', compact('product', 'categories', 'mediaItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, int $id): RedirectResponse
    {
        $product = Product::find($id);

        if ($request->file('images')) {
            $product->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection();
                });
        }

        $product->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'category_id' => $request->validated('category_id')
        ]);

        return redirect(route('products'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        Product::whereId($id)->delete();

        return redirect(route('products'));
    }
}
