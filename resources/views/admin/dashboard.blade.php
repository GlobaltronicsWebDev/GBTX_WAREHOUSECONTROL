@extends('layouts.admin')

@section('title', 'Dashboard | Globaltronics')

@section('content')
    @php
        $user = Auth::user();
        $canReviewSrf = $user->canManageSrf();
    @endphp

    <div class="flex flex-col gap-6">
        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-mono-code text-xs font-semibold uppercase tracking-wide text-blue-600">Globaltronics Operations</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $user->isItAdmin() ? 'IT Administration' : ($user->isTechnical() ? 'Technical Operations' : 'Warehouse Operations') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">Stock requisitions, approvals, and account access.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($canReviewSrf)
                    <button type="button" onclick="openInstallationSrfModal()" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                        Create SRF
                    </button>
                @endif
                <a href="{{ route('admin.srf.approved') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Approved SRFs
                </a>
                @if ($user->canAccessAdminCredentials())
                    <a href="{{ route('admin.settings.credentials') }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-bold text-blue-700 hover:bg-blue-100">
                        Manage accounts
                    </a>
                @endif
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @if ($user->canAccessAdminCredentials())
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">User accounts</p>
                    <p class="mt-2 font-mono-code text-3xl font-extrabold text-slate-900">{{ $totalUsers }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $adminCount }} administrators · {{ $staffCount }} warehouse staff</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Security roles</p>
                    <p class="mt-2 font-mono-code text-3xl font-extrabold text-amber-700">{{ $totalRoles }}</p>
                    <p class="mt-1 text-xs text-slate-500">Configured access levels</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Technical staff</p>
                    <p class="mt-2 font-mono-code text-3xl font-extrabold text-cyan-700">{{ $technicalCount }}</p>
                    <p class="mt-1 text-xs text-slate-500">SRF requesters and technical personnel</p>
                </div>
            @endif
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total SRFs</p>
                <p class="mt-2 font-mono-code text-3xl font-extrabold text-blue-700">{{ $user->canViewWarehouseOperations() || $user->isItAdmin() ? $srfTotalCount : $userSrfCount }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $user->canViewWarehouseOperations() || $user->isItAdmin() ? 'All requisitions' : 'Your requisitions' }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-amber-700">Pending review</p>
                <p class="mt-2 font-mono-code text-3xl font-extrabold text-amber-800">{{ $srfPendingCount }}</p>
                <p class="mt-1 text-xs text-amber-700">Awaiting a warehouse decision</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Approved</p>
                <p class="mt-2 font-mono-code text-3xl font-extrabold text-emerald-800">{{ $srfCompletedCount }}</p>
                <p class="mt-1 text-xs text-emerald-700">Approved, completed, or verified</p>
            </div>
        </section>

        @if ($user->canAccessAdminCredentials())
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Recent accounts</h2>
                        <p class="text-sm text-slate-500">Latest user accounts and assigned roles.</p>
                    </div>
                    <a href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">Manage users</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[36rem] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2">Roles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentUsers as $recentUser)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-slate-800">{{ $recentUser->name }}</td>
                                    <td class="py-3 pr-4 text-slate-600">{{ $recentUser->email }}</td>
                                    <td class="py-3 text-slate-600">{{ $recentUser->roles->pluck('name')->join(', ') ?: 'No roles assigned' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-slate-500">No accounts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Stock Requisition Forms</h2>
                    <p class="text-sm text-slate-500">Item details are recorded with each request; inventory quantities are not tracked here.</p>
                </div>
                <a href="{{ route('admin.srf.approved') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">View approved SRFs</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">SRF / SSO</th>
                            <th class="px-5 py-3">Project</th>
                            <th class="px-5 py-3">Items</th>
                            <th class="px-5 py-3">Prepared by</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($groupedSrfRequisitions as $srfNumber => $items)
                            @php
                                $first = $items->first();
                                $statusClass = match ($first->status) {
                                    'approved', 'completed', 'verified' => 'bg-emerald-100 text-emerald-800',
                                    'declined', 'rejected' => 'bg-rose-100 text-rose-800',
                                    'on_hold' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-blue-100 text-blue-800',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $srfNumber }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $first->sso_number }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $first->project_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $first->client }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <ul class="space-y-1">
                                        @foreach ($items as $item)
                                            <li class="text-slate-700">
                                                <span class="font-semibold">{{ $item->quantity }} {{ $item->uom ?: 'PCS' }}</span>
                                                {{ $item->item_model }}
                                                @if ($item->item_description)
                                                    <span class="block text-xs text-slate-500">{{ $item->item_description }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $first->prepared_by }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($first->status)) }}</span>
                                    @if ($first->verified_by_name)
                                        <p class="mt-1 text-xs text-slate-500">By {{ $first->verified_by_name }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        <a href="{{ route('admin.srf.pdf', ['srfNumber' => $srfNumber]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Print PDF</a>
                                        @if ($canReviewSrf && in_array($first->status, ['pending', 'for_approval', 'on_hold'], true))
                                            <form method="POST" action="{{ route('admin.operations.installation.srf.verify', $first) }}" class="flex flex-wrap justify-end gap-1">
                                                @csrf
                                                <button name="decision" value="approved" class="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Approve</button>
                                                <button name="decision" value="on_hold" class="rounded-lg bg-amber-500 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-amber-600">Hold</button>
                                                <button name="decision" value="declined" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Decline</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No SRF requisitions have been submitted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('modals')
    @if (Auth::user()->canManageSrf())
        <div id="installationSrfModal" data-modal class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/50 p-4">
            <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Create Stock Requisition Form</h2>
                        <p class="mt-1 text-sm text-slate-500">Requests are saved independently of inventory stock.</p>
                    </div>
                    <button type="button" onclick="closeInstallationSrfModal()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Close">✕</button>
                </div>
                <form method="POST" action="{{ route('admin.operations.installation.srf') }}" class="space-y-5 p-5">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <label class="text-sm font-semibold text-slate-700">Project / client
                            <input name="project_name" value="{{ old('project_name') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Client
                            <input name="client" value="{{ old('client') }}" class="mt-1 w-full rounded-lg border-slate-300">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">PO number
                            <input name="po_number" value="{{ old('po_number') }}" class="mt-1 w-full rounded-lg border-slate-300">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Date needed
                            <input type="date" name="date_needed" value="{{ old('date_needed') }}" class="mt-1 w-full rounded-lg border-slate-300">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Department
                            <input name="department" value="{{ old('department', Auth::user()->isTechnical() ? 'TECHNICAL' : 'WAREHOUSE') }}" class="mt-1 w-full rounded-lg border-slate-300">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Prepared by
                            <input name="prepared_by" value="{{ old('prepared_by', Auth::user()->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                        </label>
                    </div>
                    <input type="hidden" name="srf_number" value="{{ $autoSrfNumber }}">
                    <input type="hidden" name="sso_number" value="{{ $autoSsoNumber }}">
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-bold text-slate-900">Requested items</h3>
                            <button type="button" onclick="addSrfItem()" class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Add item</button>
                        </div>
                        <div id="srfItems" class="space-y-3">
                            <div class="grid gap-3 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-6">
                                <input name="items[0][item_model]" placeholder="Model / item name" class="rounded-lg border-slate-300 lg:col-span-2" required>
                                <input name="items[0][item_manufacturer]" placeholder="Manufacturer" class="rounded-lg border-slate-300">
                                <input name="items[0][item_category]" placeholder="Category" class="rounded-lg border-slate-300">
                                <input name="items[0][quantity]" type="number" min="1" value="1" placeholder="Qty" class="rounded-lg border-slate-300" required>
                                <input name="items[0][uom]" value="PCS" placeholder="UOM" class="rounded-lg border-slate-300">
                                <textarea name="items[0][item_description]" placeholder="Item description" class="rounded-lg border-slate-300 sm:col-span-2 lg:col-span-3"></textarea>
                                <input name="items[0][remarks]" placeholder="Remarks" class="rounded-lg border-slate-300 sm:col-span-2 lg:col-span-3">
                            </div>
                        </div>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">Notes
                        <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('notes') }}</textarea>
                    </label>
                    <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" onclick="closeInstallationSrfModal()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Submit SRF</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            let srfItemIndex = 1;

            function openInstallationSrfModal() {
                document.getElementById('installationSrfModal')?.classList.replace('hidden', 'flex');
            }

            function closeInstallationSrfModal() {
                document.getElementById('installationSrfModal')?.classList.replace('flex', 'hidden');
            }

            function addSrfItem() {
                const index = srfItemIndex++;
                const row = document.createElement('div');
                row.className = 'grid gap-3 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-6';
                row.innerHTML = `
                    <input name="items[${index}][item_model]" placeholder="Model / item name" class="rounded-lg border-slate-300 lg:col-span-2" required>
                    <input name="items[${index}][item_manufacturer]" placeholder="Manufacturer" class="rounded-lg border-slate-300">
                    <input name="items[${index}][item_category]" placeholder="Category" class="rounded-lg border-slate-300">
                    <input name="items[${index}][quantity]" type="number" min="1" value="1" placeholder="Qty" class="rounded-lg border-slate-300" required>
                    <input name="items[${index}][uom]" value="PCS" placeholder="UOM" class="rounded-lg border-slate-300">
                    <textarea name="items[${index}][item_description]" placeholder="Item description" class="rounded-lg border-slate-300 sm:col-span-2 lg:col-span-3"></textarea>
                    <input name="items[${index}][remarks]" placeholder="Remarks" class="rounded-lg border-slate-300 sm:col-span-2 lg:col-span-3">
                    <button type="button" onclick="this.parentElement.remove()" class="text-left text-xs font-bold text-rose-600 lg:col-span-6">Remove item</button>
                `;
                document.getElementById('srfItems')?.appendChild(row);
            }
        </script>
    @endif
@endsection
