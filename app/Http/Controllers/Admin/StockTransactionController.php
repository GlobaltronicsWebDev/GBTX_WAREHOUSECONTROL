<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Support\StockTransactionWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $type = $this->transactionType($request);
        $filters = $this->filters($request);
        $transactions = $this->transactionQuery($type, $filters)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.stock-transactions.index', compact('type', 'filters', 'transactions'));
    }

    public function create(Request $request): View
    {
        $type = $this->transactionType($request);
        $inventoryItems = InventoryItem::query()
            ->with('warehouseLocation')
            ->whereNotNull('warehouse_location_id')
            ->orderBy('item_description')
            ->get();
        $lines = old('lines', [
            ['inventory_item_id' => '', 'quantity' => '', 'unit_of_measurement' => 'PCS'],
        ]);

        return view('admin.stock-transactions.form', [
            'type' => $type,
            'inventoryItems' => $inventoryItems,
            'lines' => $lines,
            'authName' => $request->user()->name,
        ]);
    }

    public function store(Request $request, StockTransactionWriter $writer): RedirectResponse
    {
        $type = $this->transactionType($request);
        $sharedRules = [
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*' => ['required', 'array:inventory_item_id,quantity,unit_of_measurement'],
            'lines.*.inventory_item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('inventory_items', 'id')->whereNull('deleted_at'),
            ],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'lines.*.unit_of_measurement' => ['required', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
        $typeRules = $type === 'in'
            ? [
                'purchase_order_number' => ['nullable', 'string', 'max:255'],
                'supplier_name' => ['required', 'string', 'max:255'],
                'received_by' => ['required', 'string', 'max:255'],
            ]
            : [
                'request_number' => ['nullable', 'string', 'max:255'],
                'department_customer' => ['required', 'string', 'max:255'],
                'released_by' => ['required', 'string', 'max:255'],
                'received_by' => ['required', 'string', 'max:255'],
            ];
        $validated = $request->validate($sharedRules + $typeRules);

        $transaction = $writer->create(
            $type,
            $validated,
            $request->user()->id,
            $request->file('attachment'),
        );

        return redirect()
            ->route($this->routeName($type, 'show'), $transaction)
            ->with('status', ucfirst("Stock {$type}").' transaction saved successfully.');
    }

    public function show(Request $request, StockTransaction $transaction): View
    {
        $type = $this->transactionType($request);
        abort_unless($transaction->type === $type, 404);

        return view('admin.stock-transactions.show', [
            'type' => $type,
            'transaction' => $transaction->load(['creator', 'lines']),
        ]);
    }

    public function attachment(Request $request, StockTransaction $transaction): StreamedResponse
    {
        $type = $this->transactionType($request);
        abort_unless($transaction->type === $type, 404);
        abort_unless($transaction->attachment_path !== null, 404);
        abort_unless(Storage::disk('local')->exists($transaction->attachment_path), 404);

        return Storage::disk('local')->download(
            $transaction->attachment_path,
            basename($transaction->attachment_path),
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $this->transactionType($request);
        $filters = $this->filters($request);

        return response()->streamDownload(function () use ($type, $filters): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new RuntimeException('The stock report could not be opened for export.');
            }

            fputcsv($output, [
                'Reference Number',
                'Transaction Date',
                'Purchase Order Number',
                'Request Number',
                'Supplier',
                'Department / Customer',
                'Product ID / SKU',
                'Product Name',
                'Quantity',
                'Unit of Measurement',
                'Warehouse Location',
                'Released By',
                'Received By',
                'Remarks',
                'Recorded By',
            ], ',', '"', '');

            $this->transactionQuery($type, $filters)
                ->with('creator')
                ->orderBy('id')
                ->chunkById(250, function ($transactions) use ($output): void {
                    foreach ($transactions as $transaction) {
                        foreach ($transaction->lines as $line) {
                            fputcsv($output, array_map(
                                fn (string|int|null $value): string => $this->csvCell($value),
                                [
                                    $transaction->reference_number,
                                    $transaction->transaction_date?->format('Y-m-d'),
                                    $transaction->purchase_order_number,
                                    $transaction->request_number,
                                    $transaction->supplier_name,
                                    $transaction->department_customer,
                                    $line->product_sku,
                                    $line->product_name,
                                    $line->quantity,
                                    $line->unit_of_measurement,
                                    $line->warehouse_location_name,
                                    $transaction->released_by,
                                    $transaction->received_by,
                                    $transaction->remarks,
                                    $transaction->creator?->name,
                                ],
                            ), ',', '"', '');
                        }
                    }
                });

            fclose($output);
        }, "stock-{$type}-report-".now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array{from?: string|null, to?: string|null, search?: string|null}  $filters
     * @return Builder<StockTransaction>
     */
    private function transactionQuery(string $type, array $filters): Builder
    {
        return StockTransaction::query()
            ->where('type', $type)
            ->with('lines')
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('purchase_order_number', 'like', "%{$search}%")
                        ->orWhere('request_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('department_customer', 'like', "%{$search}%")
                        ->orWhereHas('lines', function (Builder $lineQuery) use ($search): void {
                            $lineQuery
                                ->where('product_sku', 'like', "%{$search}%")
                                ->orWhere('product_name', 'like', "%{$search}%");
                        });
                });
            });
    }

    /**
     * @return array{from: string|null, to: string|null, search: string|null}
     */
    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        if (($filters['from'] ?? null) !== null
            && ($filters['to'] ?? null) !== null
            && $filters['from'] > $filters['to']) {
            throw ValidationException::withMessages([
                'to' => 'The end date must be on or after the start date.',
            ]);
        }

        return [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'search' => $filters['search'] ?? null,
        ];
    }

    private function transactionType(Request $request): string
    {
        $type = $request->route('transactionType');
        abort_unless(in_array($type, ['in', 'out'], true), 404);

        return $type;
    }

    private function routeName(string $type, string $action): string
    {
        return 'admin.stock-'.$type.'.'.$action;
    }

    private function csvCell(string|int|null $value): string
    {
        $cell = (string) ($value ?? '');

        return preg_match('/^(?:[\'\t\r\n]|[ \t\r\n]*[=+\-@])/', $cell) === 1 ? "'".$cell : $cell;
    }
}
