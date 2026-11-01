<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    public function index(Request $request): Response
    {
        $today = Carbon::today();
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));
        $productId = $request->query('product_id');

        $batches = Batch::query()
            ->with([
                'product:id,name,sku,unit_id',
                'product.unit:id,short_name',
                'supplier:id,name',
            ])
            ->withSum('stocks as total_qty', 'quantity')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($status === 'expired', fn ($query) => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today))
            ->when($status === 'near_expiry', fn ($query) => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '>=', $today)->whereDate('expiry_date', '<=', $today->copy()->addDays(30)))
            ->when($status === 'active', fn ($query) => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '>', $today->copy()->addDays(30)))
            ->when($status === 'depleted', fn ($query) => $query->whereDoesntHave('stocks', fn ($query) => $query->where('quantity', '>', 0)))
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Batch $batch) use ($today) {
                $daysLeft = $batch->expiry_date
                    ? (int) $today->diffInDays($batch->expiry_date->copy()->startOfDay(), false)
                    : null;
                $totalQty = (float) ($batch->total_qty ?? 0);

                $batchStatus = match (true) {
                    $daysLeft !== null && $daysLeft < 0 => 'expired',
                    $totalQty <= 0 => 'depleted',
                    $daysLeft === null => 'active',
                    $daysLeft <= 30 => 'near_expiry',
                    default => 'active',
                };

                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'product_id' => $batch->product_id,
                    'product' => $batch->product?->name,
                    'sku' => $batch->product?->sku,
                    'unit' => $batch->product?->unit?->short_name,
                    'supplier' => $batch->supplier?->name,
                    'manufactured_date' => $batch->manufactured_date?->format('Y-m-d'),
                    'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                    'days_left' => $daysLeft,
                    'total_qty' => $totalQty,
                    'status' => $batchStatus,
                ];
            });

        return Inertia::render('Batches/Index', [
            'batches' => $batches,
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
            'filters' => [
                'status' => $status,
                'search' => $search,
                'product_id' => $productId ?? '',
            ],
            'summary' => [
                'active' => Batch::whereNotNull('expiry_date')->whereDate('expiry_date', '>', $today->copy()->addDays(30))->count(),
                'near_expiry' => Batch::whereNotNull('expiry_date')->whereDate('expiry_date', '>=', $today)->whereDate('expiry_date', '<=', $today->copy()->addDays(30))->count(),
                'expired' => Batch::whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today)->count(),
                'depleted' => Batch::whereDoesntHave('stocks', fn ($query) => $query->where('quantity', '>', 0))->count(),
            ],
        ]);
    }
}
