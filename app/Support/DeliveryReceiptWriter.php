<?php

namespace App\Support;

use App\Models\DeliveryReceipt;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DeliveryReceiptWriter
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, User $user): DeliveryReceipt
    {
        return DB::transaction(function () use ($validated, $user): DeliveryReceipt {
            $sequence = DB::table('delivery_receipt_sequences')
                ->where('type', $validated['type'])
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                throw new RuntimeException('The delivery receipt number sequence is missing.');
            }

            $number = $sequence->last_number + 1;
            DB::table('delivery_receipt_sequences')->where('type', $validated['type'])->update(['last_number' => $number]);

            $receipt = new DeliveryReceipt(Arr::except($validated, 'lines'));
            $receipt->number = ($validated['type'] === 'client' ? 'DRC-' : 'DRI-').str_pad((string) $number, 6, '0', STR_PAD_LEFT);
            $receipt->created_by = $user->id;
            $receipt->saveOrFail();
            $this->saveLines($receipt, $validated['lines']);

            return $receipt;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(DeliveryReceipt $receipt, array $validated): DeliveryReceipt
    {
        return DB::transaction(function () use ($receipt, $validated): DeliveryReceipt {
            $receipt = DeliveryReceipt::query()->lockForUpdate()->findOrFail($receipt->id);
            $this->ensureDraft($receipt);

            if ($validated['type'] !== $receipt->type) {
                throw ValidationException::withMessages(['type' => 'The receipt type cannot be changed after creation.']);
            }

            $receipt->fill(Arr::except($validated, 'lines'))->saveOrFail();
            $receipt->lines()->delete();
            $this->saveLines($receipt, $validated['lines']);

            return $receipt;
        });
    }

    public function release(DeliveryReceipt $receipt, User $user, StockTransactionWriter $stockWriter): DeliveryReceipt
    {
        return DB::transaction(function () use ($receipt, $user, $stockWriter): DeliveryReceipt {
            $receipt = DeliveryReceipt::query()->lockForUpdate()->findOrFail($receipt->id);
            $this->ensureDraft($receipt);
            $receipt->load('lines');

            if ($receipt->lines->isEmpty()) {
                throw ValidationException::withMessages(['lines' => 'Add at least one product before releasing the receipt.']);
            }

            $transaction = $stockWriter->create('out', [
                'transaction_date' => now()->toDateString(),
                'request_number' => $receipt->request_number,
                'department_customer' => $receipt->recipient,
                'released_by' => $user->name,
                'received_by' => $receipt->received_by,
                'remarks' => $receipt->remarks,
                'lines' => $receipt->lines->map(fn ($line) => [
                    'inventory_item_id' => $line->inventory_item_id,
                    'quantity' => $line->quantity,
                    'unit_of_measurement' => $line->unit_of_measurement,
                ])->all(),
            ], $user->id, null);

            $receipt->forceFill([
                'status' => 'released',
                'released_by' => $user->id,
                'released_at' => now(),
                'stock_transaction_id' => $transaction->id,
            ])->saveOrFail();

            return $receipt;
        });
    }

    /**
     * @param  array<int, array{inventory_item_id: int|string, quantity: int|string, model_serial?: string|null}>  $lines
     */
    private function saveLines(DeliveryReceipt $receipt, array $lines): void
    {
        $items = InventoryItem::query()
            ->whereIn('id', collect($lines)->pluck('inventory_item_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($lines as $index => $line) {
            $item = $items->get((int) $line['inventory_item_id']);
            if ($item === null || $item->warehouse_location_id === null) {
                throw ValidationException::withMessages([
                    "lines.{$index}.inventory_item_id" => 'Select an inventory item assigned to a warehouse.',
                ]);
            }

            $receipt->lines()->create([
                'inventory_item_id' => $item->id,
                'product_sku' => $item->po_sku_number ?: 'ID-'.$item->id,
                'product_name' => $item->item_description,
                'model_serial' => $line['model_serial'] ?? null,
                'quantity' => $line['quantity'],
                'unit_of_measurement' => $item->unit_of_measurement,
            ]);
        }
    }

    private function ensureDraft(DeliveryReceipt $receipt): void
    {
        if ($receipt->status !== 'draft') {
            throw ValidationException::withMessages(['receipt' => 'Only draft delivery receipts can be edited or released.']);
        }
    }
}
