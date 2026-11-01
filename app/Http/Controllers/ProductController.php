<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with(['category', 'unit'])
                ->orderBy('name')
                ->paginate(50),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products|max:50',
            'barcode' => 'nullable|string|unique:products|max:255',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'min_stock' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'track_batch' => 'boolean',
            'track_expiry' => 'boolean',
            'expiry_alert_days' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('message', 'Barang berhasil ditambahkan');
    }

    public function show(Product $product)
    {
        return Inertia::render('Products/Show', [
            'product' => $product->load(['category', 'unit', 'batches', 'stocks']),
        ]);
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku,' . $product->id . '|max:50',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id . '|max:255',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'min_stock' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('message', 'Barang berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        if ($product->stocks()->where('quantity', '>', 0)->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Tidak bisa menghapus barang yang masih punya stok');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Barang berhasil dihapus');
    }
}
