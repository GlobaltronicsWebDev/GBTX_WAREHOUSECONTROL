<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $items = InventoryItem::query()
            ->matching($request->string('search')->toString())
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.inventory.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.inventory.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canViewWarehouseOperations(), 403);

        $validated = $request->validate([
            'tag_number' => ['nullable', 'string', 'max:255'],
            'date_received' => ['nullable', 'date'],
            'po_sku_number' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model_pixel_pitch' => ['nullable', 'string', 'max:255'],
            'item_description' => ['required', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:255'],
            'on_hand' => ['required', 'integer', 'min:0'],
            'total_on_hand' => ['required', 'integer', 'min:0'],
            'per_panel_sqm' => ['required', 'numeric', 'min:0'],
            'total_available_sqm' => ['required', 'numeric', 'min:0'],
            'available_qty' => ['required', 'integer', 'min:0'],
            'available_sqm' => ['required', 'numeric', 'min:0'],
            'reservation_qty' => ['required', 'integer', 'min:0'],
            'reservation_project_details' => ['nullable', 'string', 'max:10000'],
            'history_qty' => ['required', 'integer', 'min:0'],
            'history_project' => ['nullable', 'string', 'max:10000'],
            'original_qty' => ['required', 'integer', 'min:0'],
            'status_qty' => ['required', 'integer', 'min:0'],
            'status_particular' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:10000'],
        ]);

        $validated['created_by'] = $request->user()->id;
        InventoryItem::create($validated);

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', 'Inventory item added successfully.');
    }
}
