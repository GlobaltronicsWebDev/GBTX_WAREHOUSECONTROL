<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessPartnerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'type' => ['nullable', Rule::in(['supplier', 'client'])],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';
        $type = $filters['type'] ?? '';

        $partners = BusinessPartner::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.settings.partners', compact('partners', 'search', 'status', 'type'));
    }

    public function store(Request $request): RedirectResponse
    {
        BusinessPartner::create($this->validatedPartnerData($request));

        return redirect()->route('admin.settings.partners')->with('status', 'Supplier/client created successfully.');
    }

    public function update(Request $request, BusinessPartner $partner): RedirectResponse
    {
        $partner->update($this->validatedPartnerData($request));

        return redirect()->route('admin.settings.partners')->with('status', 'Supplier/client updated successfully.');
    }

    public function destroy(BusinessPartner $partner): RedirectResponse
    {
        $partner->delete();

        return redirect()->route('admin.settings.partners')->with('status', 'Supplier/client deleted successfully.');
    }

    /**
     * @return array{name: string, type: string, category: ?string, address: ?string, is_active: bool}
     */
    private function validatedPartnerData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['supplier', 'client'])],
            'category' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
