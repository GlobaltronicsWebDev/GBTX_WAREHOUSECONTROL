<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class WarehouseDashboardController extends Controller
{
    public function show(Request $request, WarehouseLocation $location): View
    {
        abort_unless($location->is_active && $location->navigation_enabled, 404);

        $selectedSubLocation = $request->query('sub_location');
        if ($selectedSubLocation !== null) {
            abort_unless(is_string($selectedSubLocation) && in_array($selectedSubLocation, $location->inventorySubLocations(), true), 404);
        }

        $items = InventoryItem::query()
            ->with('histories')
            ->whereBelongsTo($location, 'warehouseLocation')
            ->when($selectedSubLocation !== null, fn ($query) => $query->where('sub_location', $selectedSubLocation))
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.warehouses.show', compact('location', 'items', 'selectedSubLocation'));
    }

    public function destroySelected(Request $request, WarehouseLocation $location): RedirectResponse
    {
        abort_unless($request->user()->canDeleteInventoryItems(), 403);
        abort_unless($location->is_active && $location->navigation_enabled, 404);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:10'],
            'items.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        $count = DB::transaction(function () use ($validated, $location): int {
            $items = InventoryItem::query()
                ->whereBelongsTo($location, 'warehouseLocation')
                ->whereIn('id', $validated['items'])
                ->lockForUpdate()
                ->get();

            if ($items->count() !== count($validated['items'])) {
                throw ValidationException::withMessages([
                    'items' => 'Some selected items are unavailable or no longer belong to this warehouse. Nothing was deleted.',
                ]);
            }

            foreach ($items as $item) {
                if (! $item->delete()) {
                    throw new RuntimeException('An inventory item could not be deleted.');
                }
            }

            return $items->count();
        });

        return back()->with('status', "Deleted {$count} selected inventory item(s).");
    }

    public function destroyAll(Request $request, WarehouseLocation $location): RedirectResponse
    {
        abort_unless($request->user()->canDeleteInventoryItems(), 403);
        abort_unless($location->is_active && $location->navigation_enabled, 404);

        $request->validate([
            'confirmation' => ['required', 'string', Rule::in([$location->name])],
        ], [
            'confirmation.in' => 'Enter the warehouse name exactly to confirm deletion.',
        ]);

        $count = DB::transaction(function () use ($location): int {
            $count = 0;
            InventoryItem::query()
                ->whereBelongsTo($location, 'warehouseLocation')
                ->lockForUpdate()
                ->chunkById(200, function ($items) use (&$count): void {
                    foreach ($items as $item) {
                        if (! $item->delete()) {
                            throw new RuntimeException('An inventory item could not be deleted.');
                        }
                        $count++;
                    }
                });
            if ($count === 0) {
                throw ValidationException::withMessages(['confirmation' => 'This warehouse has no active inventory items to delete.']);
            }

            return $count;
        });

        return back()->with('status', "Deleted {$count} inventory item(s) from {$location->name}.");
    }
}
