<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use App\Models\InventoryItem;
use App\Support\DeliveryReceiptWriter;
use App\Support\StockTransactionWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeliveryReceiptController extends Controller
{
    public function index(): View
    {
        return view('admin.delivery-receipts.index', [
            'receipts' => DeliveryReceipt::query()->latest('id')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return $this->form(new DeliveryReceipt);
    }

    public function store(Request $request, DeliveryReceiptWriter $writer): RedirectResponse
    {
        $receipt = $writer->create($this->validateReceipt($request), $request->user());

        return redirect()->route('admin.delivery-receipts.show', $receipt)
            ->with('status', 'Draft delivery receipt saved. Inventory has not been changed.');
    }

    public function show(DeliveryReceipt $deliveryReceipt): View
    {
        return view('admin.delivery-receipts.show', [
            'receipt' => $deliveryReceipt->load(['lines', 'creator', 'releaser', 'stockTransaction']),
        ]);
    }

    public function edit(DeliveryReceipt $deliveryReceipt): View
    {
        abort_unless($deliveryReceipt->status === 'draft', 403);

        return $this->form($deliveryReceipt->load('lines'));
    }

    public function update(Request $request, DeliveryReceipt $deliveryReceipt, DeliveryReceiptWriter $writer): RedirectResponse
    {
        $receipt = $writer->update($deliveryReceipt, $this->validateReceipt($request));

        return redirect()->route('admin.delivery-receipts.show', $receipt)
            ->with('status', 'Draft delivery receipt updated.');
    }

    public function release(Request $request, DeliveryReceipt $deliveryReceipt, DeliveryReceiptWriter $writer, StockTransactionWriter $stockWriter): RedirectResponse
    {
        $receipt = $writer->release($deliveryReceipt, $request->user(), $stockWriter);

        return redirect()->route('admin.delivery-receipts.show', $receipt)
            ->with('status', 'Delivery receipt released and inventory updated.');
    }

    private function form(DeliveryReceipt $receipt): View
    {
        return view('admin.delivery-receipts.form', [
            'receipt' => $receipt,
            'inventoryItems' => InventoryItem::query()->with('warehouseLocation')
                ->whereNotNull('warehouse_location_id')->orderBy('item_description')->get(),
            'lines' => old('lines', $receipt->exists
                ? $receipt->lines->toArray()
                : [['inventory_item_id' => '', 'quantity' => '', 'model_serial' => '']]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateReceipt(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['client', 'internal'])],
            'receipt_date' => ['required', 'date_format:Y-m-d'],
            'recipient' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'tin' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'terms' => ['nullable', 'string', 'max:255'],
            'purchase_order_number' => ['nullable', 'string', 'max:255'],
            'sales_order_number' => ['nullable', 'string', 'max:255'],
            'request_number' => ['nullable', 'string', 'max:255'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:10000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*' => ['required', 'array:inventory_item_id,quantity,model_serial'],
            'lines.*.inventory_item_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('inventory_items', 'id')->whereNull('deleted_at'),
            ],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'lines.*.model_serial' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
