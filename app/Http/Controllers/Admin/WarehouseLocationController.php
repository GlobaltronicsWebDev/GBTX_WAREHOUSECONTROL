<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseLocationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';

        $locations = WarehouseLocation::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('entity', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.settings.locations', compact('locations', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        WarehouseLocation::create($this->validatedLocationData($request));

        return redirect()->route('admin.settings.locations')->with('status', 'Warehouse/location created successfully.');
    }

    public function update(Request $request, WarehouseLocation $location): RedirectResponse
    {
        $location->update($this->validatedLocationData($request, $location));

        return redirect()->route('admin.settings.locations')->with('status', 'Warehouse/location updated successfully.');
    }

    public function destroy(WarehouseLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.settings.locations')->with('status', 'Warehouse/location deleted successfully.');
    }

    /**
     * @return array{name: string, entity: string, is_active: bool, navigation_enabled: bool}
     */
    private function validatedLocationData(Request $request, ?WarehouseLocation $location = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'entity' => trim((string) $request->input('entity', '')),
        ]);
        $uniqueNameRule = Rule::unique('warehouse_locations', 'name')
            ->where('entity', $request->input('entity'));
        if ($location !== null) {
            $uniqueNameRule->ignore($location->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueNameRule],
            'entity' => ['required', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'navigation_enabled' => ['required', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['navigation_enabled'] = $request->boolean('navigation_enabled');

        return $validated;
    }
}
