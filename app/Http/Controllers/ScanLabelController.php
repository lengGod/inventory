<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\LabelPrintLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Services\ScanLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\Exceptions\BarcodeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ScanLabelController extends Controller
{
    public function __construct(private readonly ScanLookupService $lookupService) {}

    public function index(Request $request): Response
    {
        $initialCode = $request->query('code');
        $initialCode = is_string($initialCode) ? trim($initialCode) : '';

        $history = LabelPrintLog::query()
            ->with('printer:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (LabelPrintLog $log) => $this->presentLog($log))
            ->values();

        return Inertia::render('ScanLabels/Index', [
            'history' => $history,
            'stats' => [
                'today' => (int) LabelPrintLog::whereDate('created_at', today())->sum('copies'),
                'total' => (int) LabelPrintLog::sum('copies'),
                'logs' => LabelPrintLog::count(),
            ],
            'quickPicks' => $this->quickPicks(),
            'initialCode' => $initialCode,
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->lookupService->lookup(trim($validated['code']));

        if ($result === null) {
            return response()->json([
                'message' => 'Kode tidak dikenali. Periksa barcode, SKU, nomor batch, atau kode lokasi.',
            ], 404);
        }

        return response()->json($this->presentResult($result));
    }

    public function print(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label_type' => ['required', Rule::in(['product', 'batch', 'location'])],
            'reference_id' => ['required', 'integer', 'min:1'],
            'copies' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $reference = $this->findReference($validated['label_type'], (int) $validated['reference_id']);

        if ($reference === null) {
            return response()->json(['message' => 'Data label tidak ditemukan.'], 404);
        }

        $log = LabelPrintLog::create([
            'label_type' => $validated['label_type'],
            'reference_id' => $reference->getKey(),
            'copies' => $validated['copies'],
            'printed_by' => $request->user()->id,
        ])->load('printer:id,name');

        return response()->json([
            'message' => 'Cetak label berhasil dicatat.',
            'log' => $this->presentLog($log),
        ]);
    }

    public function barcode(string $code): HttpResponse
    {
        $code = trim($code);

        if ($code === '' || strlen($code) > 80) {
            abort(422, 'Kode barcode tidak valid.');
        }

        try {
            $generator = new BarcodeGeneratorSVG;
            $svg = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 50, 'black');
        } catch (BarcodeException) {
            abort(422, 'Kode tidak dapat dibuat menjadi barcode.');
        }

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    private function findReference(string $type, int $id): Product|Batch|Location|null
    {
        return match ($type) {
            'product' => Product::withTrashed()->find($id),
            'batch' => Batch::find($id),
            'location' => Location::find($id),
            default => null,
        };
    }

    /**
     * @param  array{type: string, product?: Product|null, batch?: Batch|null, location?: Location|null, stocks?: iterable<Stock>}  $result
     */
    private function presentResult(array $result): array
    {
        $product = $result['product'] ?? null;
        $batch = $result['batch'] ?? null;
        $location = $result['location'] ?? null;

        if ($product instanceof Product) {
            $product->loadMissing(['category:id,name', 'unit:id,name,short_name']);
        }

        $stocks = collect($result['stocks'] ?? [])
            ->map(function (Stock $stock): array {
                return [
                    'id' => $stock->id,
                    'quantity' => (float) $stock->quantity,
                    'location' => $stock->location ? [
                        'id' => $stock->location->id,
                        'code' => $stock->location->code,
                        'name' => $stock->location->name,
                    ] : null,
                    'batch' => $stock->batch ? [
                        'id' => $stock->batch->id,
                        'batch_number' => $stock->batch->batch_number,
                        'expiry_date' => $stock->batch->expiry_date?->format('Y-m-d'),
                    ] : null,
                    'product' => $stock->product ? [
                        'id' => $stock->product->id,
                        'name' => $stock->product->name,
                        'sku' => $stock->product->sku,
                    ] : null,
                ];
            })
            ->values();

        return [
            'type' => $result['type'],
            'product' => $product instanceof Product ? $this->presentProduct($product) : null,
            'batch' => $batch instanceof Batch ? $this->presentBatch($batch) : null,
            'location' => $location instanceof Location ? [
                'id' => $location->id,
                'code' => $location->code,
                'name' => $location->name,
                'barcode' => $location->barcode,
            ] : null,
            'stocks' => $stocks,
            'total_stock' => (float) $stocks->sum('quantity'),
            'total_locations' => $stocks->pluck('location.id')->filter()->unique()->count(),
        ];
    }

    private function presentProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category?->name,
            'unit' => $product->unit?->short_name ?? $product->unit?->name,
            'min_stock' => (float) $product->min_stock,
            'track_batch' => (bool) $product->track_batch,
            'track_expiry' => (bool) $product->track_expiry,
        ];
    }

    private function presentBatch(Batch $batch): array
    {
        $batch->loadMissing('supplier:id,name');

        return [
            'id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'product_id' => $batch->product_id,
            'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
            'manufactured_date' => $batch->manufactured_date?->format('Y-m-d'),
            'status' => $batch->status,
            'supplier' => $batch->supplier?->name,
        ];
    }

    private function presentLog(LabelPrintLog $log): array
    {
        $reference = $this->findReference($log->label_type, (int) $log->reference_id);

        [$label, $code] = match ($log->label_type) {
            'product' => [
                $reference instanceof Product ? $reference->name : null,
                $reference instanceof Product ? ($reference->barcode ?: $reference->sku) : null,
            ],
            'batch' => [
                $reference instanceof Batch ? $reference->product?->name : null,
                $reference instanceof Batch ? $reference->batch_number : null,
            ],
            'location' => [
                $reference instanceof Location ? $reference->name : null,
                $reference instanceof Location ? ($reference->barcode ?: $reference->code) : null,
            ],
            default => [null, null],
        };

        return [
            'id' => $log->id,
            'label_type' => $log->label_type,
            'reference_id' => $log->reference_id,
            'reference_label' => $label,
            'reference_code' => $code,
            'copies' => $log->copies,
            'printed_by' => $log->printer?->name,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array{type: string, code: string, label: string, hint: string}>
     */
    private function quickPicks(): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(4)
            ->get(['id', 'name', 'sku', 'barcode'])
            ->map(fn (Product $product) => [
                'type' => 'product',
                'code' => $product->barcode ?: $product->sku,
                'label' => $product->name,
                'hint' => 'SKU '.$product->sku,
            ]);

        $batches = Batch::query()
            ->with('product:id,name')
            ->orderByDesc('id')
            ->limit(3)
            ->get(['id', 'product_id', 'batch_number'])
            ->map(fn (Batch $batch) => [
                'type' => 'batch',
                'code' => $batch->batch_number,
                'label' => $batch->batch_number,
                'hint' => $batch->product?->name ?? 'Batch',
            ]);

        $locations = Location::query()
            ->orderBy('code')
            ->limit(3)
            ->get(['id', 'code', 'name', 'barcode'])
            ->map(fn (Location $location) => [
                'type' => 'location',
                'code' => $location->barcode ?: $location->code,
                'label' => $location->code,
                'hint' => $location->name,
            ]);

        return $products->concat($batches)->concat($locations)->values()->all();
    }
}
