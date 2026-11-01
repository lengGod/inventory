<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categories = Category::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories|max:255',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('message', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id . '|max:255',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('message', 'Kategori berhasil diperbarui');
    }

    public function destroy(Category $category)
    {
        // Cek jika ada produk menggunakan kategori ini
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Tidak bisa menghapus kategori yang masih digunakan');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('message', 'Kategori berhasil dihapus');
    }
}
