<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $units = Unit::query()
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:units|max:255',
            'short_name' => 'required|string|unique:units|max:50',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('message', 'Satuan berhasil ditambahkan');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:units,name,' . $unit->id . '|max:255',
            'short_name' => 'required|string|unique:units,short_name,' . $unit->id . '|max:50',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('message', 'Satuan berhasil diperbarui');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return redirect()->route('units.index')
                ->with('error', 'Tidak bisa menghapus satuan yang masih digunakan');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('message', 'Satuan berhasil dihapus');
    }
}
