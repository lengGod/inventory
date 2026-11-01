<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $locations = Location::query()
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            }))
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:locations|max:50',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:locations|max:255',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('message', 'Lokasi berhasil ditambahkan');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:locations,code,' . $location->id . '|max:50',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:locations,barcode,' . $location->id . '|max:255',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('message', 'Lokasi berhasil diperbarui');
    }

    public function destroy(Location $location)
    {
        if ($location->stocks()->exists()) {
            return redirect()->route('locations.index')
                ->with('error', 'Tidak bisa menghapus lokasi yang masih menyimpan stok');
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('message', 'Lokasi berhasil dihapus');
    }
}
