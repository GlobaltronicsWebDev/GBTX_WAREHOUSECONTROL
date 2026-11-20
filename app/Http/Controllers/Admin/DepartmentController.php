<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';

        $departments = Department::query()
            ->withCount('users')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.settings.departments', compact('departments', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($this->validatedDepartmentData($request));

        return redirect()->route('admin.settings.departments')->with('status', 'Department created successfully.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validatedDepartmentData($request, $department));

        return redirect()->route('admin.settings.departments')->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('admin.settings.departments')->with('status', 'Department deleted successfully.');
    }

    /**
     * @return array{name: string, is_active: bool}
     */
    private function validatedDepartmentData(Request $request, ?Department $department = null): array
    {
        $uniqueNameRule = Rule::unique('departments', 'name');
        if ($department !== null) {
            $uniqueNameRule->ignore($department->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueNameRule],
            'is_active' => ['required', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
