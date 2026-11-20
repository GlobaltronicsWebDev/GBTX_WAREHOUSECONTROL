<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';

        $companies = Company::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.settings.companies', [
            'companies' => $companies,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Company::create($this->validatedCompanyData($request));

        return redirect()->route('admin.settings.companies')
            ->with('status', 'Company created successfully.');
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update($this->validatedCompanyData($request, $company));

        return redirect()->route('admin.settings.companies')
            ->with('status', 'Company updated successfully.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.settings.companies')
            ->with('status', 'Company deleted successfully.');
    }

    /**
     * @return array{name: string, code: string, contact_phone: ?string, contact_email: ?string, is_active: bool}
     */
    private function validatedCompanyData(Request $request, ?Company $company = null): array
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code', ''))),
        ]);
        $uniqueCodeRule = Rule::unique('companies', 'code');
        if ($company !== null) {
            $uniqueCodeRule->ignore($company->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                $uniqueCodeRule,
            ],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['contact_phone'] = $validated['contact_phone'] ?? null;
        $validated['contact_email'] = $validated['contact_email'] ?? null;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
