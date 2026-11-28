<?php

namespace App\Support;

use App\Models\InventoryItem;
use App\Models\StockTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class StockTransactionWriter
{
    private const MAX_QUANTITY = 4294967295;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(string $type, array $validated, int $createdBy, ?UploadedFile $attachment): StockTransaction
    {
        $attachmentPath = null;

        try {
            return DB::transaction(function () use ($type, $validated, $createdBy, $attachment, &$attachmentPath): StockTransaction {
                $transaction = StockTransaction::query()->create([
                    'type' => $type,
                    'transaction_date' => $validated['transaction_date'],
                    'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'request_number' => $validated['request_number'] ?? null,
                    'department_customer' => $validated['department_customer'] ?? null,
                    'released_by' => $validated['released_by'] ?? null,
                    'received_by' => $validated['received_by'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => $createdBy,
                ]);

                $lineItems = collect($validated['lines'])->sortBy('inventory_item_id');
                $items = InventoryItem::query()
                    ->with('warehouseLocation')
                    ->whereIn('id', $lineItems->pluck('inventory_item_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($validated['lines'] as $index => $line) {
                    $item = $items->get((int) $line['inventory_item_id']);

                    if ($item === null || $item->warehouse_location_id === null) {
                        throw ValidationException::withMessages([
                            "lines.{$index}.inventory_item_id" => 'Select an inventory item assigned to a warehouse.',
                        ]);
                    }

                    if ($line['unit_of_measurement'] !== $item->unit_of_measurement) {
                        throw ValidationException::withMessages([
                            "lines.{$index}.unit_of_measurement" => 'Use the unit of measurement configured for this inventory item.',
                        ]);
                    }

                    $quantity = (int) $line['quantity'];
                    $balances = [
                        'available_qty' => (int) $item->available_qty,
                        'on_hand' => (int) $item->on_hand,
                        'total_on_hand' => (int) $item->total_on_hand,
                    ];
                    $stockBefore = min($balances);
                    if ($type === 'out' && $quantity > $stockBefore) {
                        throw ValidationException::withMessages([
                            "lines.{$index}.quantity" => "The requested quantity exceeds the available stock ({$stockBefore}).",
                        ]);
                    }

                    foreach ($balances as $field => $balance) {
                        if ($type === 'in' && $balance + $quantity > self::MAX_QUANTITY) {
                            throw ValidationException::withMessages([
                                "lines.{$index}.quantity" => "The received quantity would exceed the maximum supported {$field} balance.",
                            ]);
                        }

                        $balances[$field] = $type === 'in'
                            ? $balance + $quantity
                            : $balance - $quantity;
                    }

                    $item->forceFill($balances)->saveOrFail();
                    $warehouseLocationName = $item->warehouseLocation?->name;
                    if ($item->sub_location !== null && $item->sub_location !== '') {
                        $warehouseLocationName .= ' / '.$item->sub_location;
                    }

                    $transaction->lines()->create([
                        'inventory_item_id' => $item->id,
                        'warehouse_location_id' => $item->warehouse_location_id,
                        'product_sku' => $item->po_sku_number ?: 'ID-'.$item->id,
                        'product_name' => $item->item_description,
                        'warehouse_location_name' => $warehouseLocationName,
                        'quantity' => $quantity,
                        'unit_of_measurement' => $line['unit_of_measurement'],
                        'stock_before' => $stockBefore,
                        'stock_after' => min($balances),
                    ]);
                }

                $attachmentPath = $attachment?->store('stock-transactions', 'local');
                if ($attachment !== null && ! is_string($attachmentPath)) {
                    throw new RuntimeException('The transaction attachment could not be stored.');
                }

                $prefix = $type === 'in' ? 'SI' : 'SO';
                $year = substr($validated['transaction_date'], 0, 4);
                $transaction->forceFill([
                    'reference_number' => $prefix.'-'.$year.'-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
                    'attachment_path' => $attachmentPath,
                ])->saveOrFail();

                return $transaction;
            });
        } catch (Throwable $exception) {
            if (is_string($attachmentPath)) {
                Storage::disk('local')->delete($attachmentPath);
            }

            throw $exception;
        }
    }
}
