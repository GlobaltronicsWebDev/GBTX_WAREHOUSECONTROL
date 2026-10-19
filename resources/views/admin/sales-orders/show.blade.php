@extends('layouts.admin')

@section('title', $order->aso_number . ' - ' . $order->client_name . ' | Globaltronics ICS')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner -->
    <div class="glass-panel bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-200">
                        {{ $order->aso_number }}
                    </span>
                    @if ($order->sjo_number)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-indigo-100 text-indigo-900 border border-indigo-200">
                            {{ $order->sjo_number }}
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                        STAGE: {{ $order->step_info['stage'] }}
                    </span>
                    @if ($order->isComplete())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            COMPLETED &amp; VERIFIED
                        </span>
                    @elseif ($order->isDeclined())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-200">
                            DECLINED
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            IN PROGRESS
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span>{{ $order->client_name }}</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Filed by <strong class="text-slate-800">{{ $order->bds_name }}</strong> on {{ $order->date_filed?->format('F d, Y') }} • PO: <span class="font-mono-code font-semibold">{{ $order->data['po_number'] ?? '—' }}</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('admin.sales-orders.index') }}" 
                   class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Orders</span>
                </a>

                @can('create', App\Models\SalesOrder::class)
                    <a href="{{ route('admin.sales-orders.create') }}" 
                       class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>New ASO</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Stepper Timeline Across 7 Core Workflow Stages -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest font-mono-code block mb-4">
                Sequential Workflow State Progress
            </span>

            @php
                $stages = [
                    ['label' => '1. ASO Filed', 'steps' => ['aso_draft']],
                    ['label' => '2. ASO Approvals', 'steps' => ['aso_approval_1', 'aso_approval_2', 'aso_approval_3']],
                    ['label' => '3. Job Order (SJO)', 'steps' => ['sjo_creation']],
                    ['label' => '4. SJO Approvals', 'steps' => ['sjo_approval_1', 'sjo_approval_2']],
                    ['label' => '5. Content Review', 'steps' => ['content_check', 'content_artist', 'content_client']],
                    ['label' => '6. IT Upload', 'steps' => ['it_upload']],
                    ['label' => '7. Accounting Audit', 'steps' => ['accounting_check', 'completed']],
                ];

                $stepOrder = array_keys($workflowSteps);
                $currentIndex = array_search($order->current_step, $stepOrder);
                if ($order->isComplete()) {
                    $currentIndex = count($stepOrder);
                }
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-2.5">
                @foreach ($stages as $sIdx => $stg)
                    @php
                        $isCurrentStage = in_array($order->current_step, $stg['steps']);
                        $isPassed = false;
                        if ($order->isComplete()) {
                            $isPassed = true;
                        } else {
                            $firstStepInStageIndex = array_search($stg['steps'][0], $stepOrder);
                            $isPassed = ($currentIndex > $firstStepInStageIndex && !$isCurrentStage);
                        }
                    @endphp
                    <div class="p-3 rounded-2xl border transition-all {{ $isCurrentStage ? 'bg-blue-50/80 border-blue-400 ring-2 ring-blue-500/20 shadow-xs' : ($isPassed ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200 opacity-60') }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-mono-code font-bold {{ $isCurrentStage ? 'text-blue-700' : ($isPassed ? 'text-emerald-700' : 'text-slate-400') }}">
                                STAGE {{ $sIdx + 1 }}
                            </span>
                            @if ($isPassed)
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @elseif ($isCurrentStage)
                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                            @endif
                        </div>
                        <span class="text-xs font-bold text-slate-800 block truncate" title="{{ $stg['label'] }}">
                            {{ $stg['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN WORKFLOW TERMINAL -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: Action Center & Workflow Documents (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- ACTIVE STEP ACTION CARD -->
            <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 {{ $canAct ? 'border-amber-400 ring-4 ring-amber-500/10' : '' }}">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                            CURRENT STEP
                        </span>
                        <h2 class="text-lg font-bold text-slate-900 mt-1">
                            {{ $order->step_info['title'] }}
                        </h2>
                    </div>

                    @if ($canAct)
                        <span class="px-3 py-1 rounded-xl text-xs font-bold font-mono-code bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                            Action Required by Your Role
                        </span>
                    @else
                        <span class="text-xs text-slate-400 font-mono-code">
                            Assigned: {{ implode(', ', $order->step_info['roles'] ?? ['None']) }}
                        </span>
                    @endif
                </div>

                <p class="text-xs sm:text-sm text-slate-600 mt-3 leading-relaxed">
                    {{ $order->step_info['description'] }}
                </p>

                <!-- Action Controls Form (Rendered only if order is in progress) -->
                @if ($order->isInProgress())
                    @if ($canAct)
                        <div class="mt-6 pt-6 border-t border-slate-100">

                            <!-- SUB-FORM: STEP SJO CREATION (Sales files SJO after ASO is complete) -->
                            @if ($order->current_step === App\Support\Workflow::STEP_SJO_CREATION)
                                <form method="POST" action="{{ route('admin.sales-orders.advance', $order) }}" class="space-y-4">
                                    @csrf
                                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider font-mono-code">Generate Job Order (SJO)</span>
                                            <span class="text-[10px] font-mono-code font-bold text-indigo-700 bg-white px-2 py-0.5 rounded border border-indigo-200">
                                                Auto: {{ App\Models\SalesOrder::generateNextSjoNumber() }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Client Company *</label>
                                                <input type="text" name="client_company" value="{{ old('client_company', $order->client_name) }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                            </div>

                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Spot Number / Rotation Slot *</label>
                                                <input type="text" name="spot_number" value="{{ old('spot_number', 'Spot 01 - Prime Loop') }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                            </div>

                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Block Timer *</label>
                                                <input type="text" name="block_timer" value="{{ old('block_timer', '15 sec every 10 mins') }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                            </div>

                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Content Attachment Link (URL) *</label>
                                                <input type="url" name="content_attachment_link" value="{{ old('content_attachment_link') }}" placeholder="https://drive.google.com/..." required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono-code">
                                            </div>

                                            <div class="sm:col-span-2">
                                                <label class="block font-bold text-slate-700 mb-1">Advertising Material Summary *</label>
                                                <input type="text" name="advertising_material" value="{{ old('advertising_material', $order->data['advertising_material_type'] ?? 'MP4 Video Material') }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                            </div>

                                            <div class="sm:col-span-2">
                                                <label class="block font-bold text-slate-700 mb-1">Special Instructions / Remarks</label>
                                                <textarea name="special_instructions" rows="2" placeholder="Specific guidelines, resolution, color format..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl">{{ old('special_instructions') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all">
                                        Generate SJO &amp; Submit for 1st SJO Approval (Badet / King) →
                                    </button>
                                </form>

                            <!-- SUB-FORM: STEP IT UPLOAD (IT team uploads and configures monitoring) -->
                            @elseif ($order->current_step === App\Support\Workflow::STEP_IT_UPLOAD)
                                <form method="POST" action="{{ route('admin.sales-orders.advance', $order) }}" class="space-y-4">
                                    @csrf
                                    <div class="p-4 rounded-2xl bg-cyan-50/60 border border-cyan-200 space-y-3">
                                        <span class="text-xs font-bold text-cyan-950 uppercase tracking-wider font-mono-code block">IT Upload &amp; Monitoring Configuration</span>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Assigned IT Personnel *</label>
                                                <input type="text" name="assigned_it" value="{{ old('assigned_it', Auth::user()->name) }}" required placeholder="e.g. IT Broadcast Specialist" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                            </div>

                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Broadcast Type *</label>
                                                <select name="broadcast_type" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                                    <option value="Paid" {{ old('broadcast_type') === 'Paid' ? 'selected' : '' }}>Paid Commercial</option>
                                                    <option value="JV" {{ old('broadcast_type') === 'JV' ? 'selected' : '' }}>Joint Venture (JV)</option>
                                                    <option value="Internal" {{ old('broadcast_type') === 'Internal' ? 'selected' : '' }}>Internal / House Ad</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Spots Allocated *</label>
                                                <input type="text" name="spots" value="{{ old('spots', '1 Spot / Rotation Loop') }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-semibold">
                                            </div>

                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Material Link (Final Transcoded Drive/Viber) *</label>
                                                <input type="url" name="material_link" value="{{ old('material_link', $order->sjo['content_attachment_link'] ?? '') }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono-code">
                                            </div>

                                            <div class="sm:col-span-2">
                                                <label class="block font-bold text-slate-700 mb-1">IT Monitoring Telemetry &amp; Notes</label>
                                                <textarea name="monitoring_notes" rows="2" placeholder="Player IP, test playback confirmed, scheduled runtimes..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl">{{ old('monitoring_notes') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-md transition-all">
                                        Confirm IT Upload &amp; Route to Accounting Verification Checklist →
                                    </button>
                                </form>

                            <!-- SUB-FORM: STEP ACCOUNTING CHECKLIST (Accounting verifies and completes order) -->
                            @elseif ($order->current_step === App\Support\Workflow::STEP_ACCOUNTING_CHECK)
                                <form method="POST" action="{{ route('admin.sales-orders.advance', $order) }}" class="space-y-4">
                                    @csrf
                                    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-emerald-950 uppercase tracking-wider font-mono-code">Accounting Verification Checklist</span>
                                            <span class="text-[10px] font-mono-code font-bold text-emerald-800 bg-white px-2 py-0.5 rounded border border-emerald-200">
                                                All 10 Items Required
                                            </span>
                                        </div>

                                        <div class="space-y-2 pt-2">
                                            @foreach ($accountingItems as $checkKey => $checkLabel)
                                                <label class="flex items-start gap-2.5 p-2 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 cursor-pointer transition-colors">
                                                    <input type="checkbox" name="checklist[{{ $checkKey }}]" value="1" 
                                                           {{ !empty($order->checks['items'][$checkKey]) ? 'checked' : '' }}
                                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                                    <span class="text-xs font-semibold text-slate-800">{{ $checkLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="pt-2">
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Accounting Audit Notes &amp; Ledger Posting Reference</label>
                                            <textarea name="notes" rows="2" placeholder="Journal entry voucher number, withholding tax details..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs">{{ old('notes', $order->checks['notes'] ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button type="submit" formaction="{{ route('admin.sales-orders.checks', $order) }}" class="flex-1 py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                                            Save Progress
                                        </button>
                                        <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all">
                                            Complete &amp; Finalize Order ✓
                                        </button>
                                    </div>
                                </form>

                            <!-- STANDARD APPROVAL & DECLINE BUTTONS -->
                            @else
                                <div class="space-y-4">
                                    <!-- Advance / Approve Form -->
                                    <form method="POST" action="{{ route('admin.sales-orders.advance', $order) }}" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Sign-off Remarks / Instructions</label>
                                            <textarea name="notes" rows="2" placeholder="Optional notes for this approval step..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500"></textarea>
                                        </div>

                                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all hover:scale-[1.01]">
                                            Approve &amp; Forward to Next Step ✓
                                        </button>
                                    </form>

                                    <!-- Send Back Button (If supported at this step, e.g. Content team check / client review) -->
                                    @if (!empty($order->step_info['can_send_back']))
                                        <form method="POST" action="{{ route('admin.sales-orders.send-back', $order) }}" class="p-3 bg-amber-50/60 border border-amber-200 rounded-2xl space-y-2">
                                            @csrf
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-amber-900">Creative Revisions Needed?</span>
                                                <span class="text-[10px] text-amber-700 font-mono-code">Returns to previous step</span>
                                            </div>
                                            <input type="text" name="reason" required placeholder="Reason for return / creative revision request..." class="w-full px-3 py-1.5 bg-white border border-amber-300 rounded-lg text-xs">
                                            <button type="submit" class="w-full py-2 px-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition-colors">
                                                ↩ Send Back for Revision ("No" on Creative Check)
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Decline Button with Reason -->
                                    @if (!empty($order->step_info['can_decline']))
                                        <form method="POST" action="{{ route('admin.sales-orders.decline', $order) }}" onsubmit="return confirm('Are you sure you want to DECLINE this sales order? This will stop the workflow and alert the filer.');" class="pt-2 border-t border-slate-100">
                                            @csrf
                                            <div class="flex gap-2">
                                                <input type="text" name="reason" required placeholder="Mandatory reason for declining..." class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white">
                                                <button type="submit" class="px-4 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition-colors shrink-0">
                                                    ✕ Decline Order
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @endif

                        </div>
                    @else
                        <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-xs text-slate-500">
                            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>This order is currently waiting on sign-off from: <strong class="text-slate-800">{{ implode(' or ', $order->step_info['roles'] ?? ['Designated Approver']) }}</strong>. You will be notified when it reaches your queue.</span>
                        </div>
                    @endif
                @else
                    <div class="mt-4 p-4 rounded-2xl {{ $order->isComplete() ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800' }} text-xs font-semibold">
                        @if ($order->isComplete())
                            ✓ This order was successfully completed on {{ $order->completed_at?->format('M d, Y • h:i A') }}.
                        @else
                            ✕ This order was declined on {{ $order->declined_at?->format('M d, Y • h:i A') }}.
                        @endif
                    </div>
                @endif
            </div>

            <!-- ASO FORM CONTRACT DATA DISPLAY -->
            <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">ASO Sales Order Details</h2>
                    </div>
                    <span class="text-xs font-mono-code font-bold text-blue-900">{{ $order->aso_number }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Client / Company Name</span>
                        <span class="font-bold text-slate-900 text-sm block mt-0.5">{{ $order->client_name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">LED Sites / Locations</span>
                        <span class="font-bold text-slate-900 block mt-0.5">{{ $order->data['led_sites'] ?? '—' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Rate Per Spot</span>
                        <span class="font-bold font-mono-code text-slate-900 block mt-0.5">{{ App\Support\Workflow::formatMoney($order->data['rate_per_spot'] ?? 0) }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Spot Duration</span>
                        <span class="font-bold font-mono-code text-slate-900 block mt-0.5">{{ $order->data['spot_duration'] ?? 15 }} seconds</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Flight Dates</span>
                        <span class="font-bold font-mono-code text-slate-900 block mt-0.5">
                            {{ $order->start_date?->format('M d, Y') }} to {{ $order->end_date?->format('M d, Y') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Advertising Material Type</span>
                        <span class="font-bold text-slate-900 block mt-0.5">{{ $order->data['advertising_material_type'] ?? '—' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">PO Number / Ref</span>
                        <span class="font-bold font-mono-code text-slate-900 block mt-0.5">
                            {{ $order->data['po_number'] ?? '—' }} {{ !empty($order->data['ref_number']) ? '• Ref: ' . $order->data['ref_number'] : '' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">VAT &amp; Payment Terms</span>
                        <span class="font-bold text-slate-900 block mt-0.5">
                            {{ $order->data['vat'] ?? '12% VAT' }} • {{ $order->data['payment_terms'] ?? '30 Days' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Billing Contact</span>
                        <span class="font-semibold text-slate-900 block mt-0.5">
                            {{ $order->data['billing_contact_person'] ?? '—' }} ({{ $order->data['billing_contact_number'] ?? '—' }})
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">TIN &amp; Business Style</span>
                        <span class="font-semibold font-mono-code text-slate-900 block mt-0.5">
                            {{ $order->data['tin'] ?? '—' }} {{ !empty($order->data['business_style']) ? '• ' . $order->data['business_style'] : '' }}
                        </span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block font-medium">Billing Address</span>
                        <span class="text-slate-800 block mt-0.5">{{ $order->data['billing_address'] ?? '—' }}</span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block font-medium">Client Details &amp; Contact Info</span>
                        <p class="text-slate-800 mt-0.5 leading-relaxed">{{ $order->data['client_details'] ?? '—' }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block font-medium">Ads Campaign Details &amp; Objectives</span>
                        <p class="text-slate-800 mt-0.5 leading-relaxed">{{ $order->data['ads_details'] ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- SJO JOB ORDER DETAILS (If generated) -->
            @if ($order->sjo)
                <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Job Order (SJO) Form Details</h2>
                        </div>
                        <span class="text-xs font-mono-code font-bold text-indigo-900">{{ $order->sjo_number }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Client Company</span>
                            <span class="font-bold text-slate-900 block mt-0.5">{{ $order->sjo['client_company'] ?? $order->client_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Spot Number &amp; Block Timer</span>
                            <span class="font-bold text-slate-900 block mt-0.5">{{ $order->sjo['spot_number'] ?? '—' }} • {{ $order->sjo['block_timer'] ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Advertising Material Summary</span>
                            <span class="font-semibold text-slate-900 block mt-0.5">{{ $order->sjo['advertising_material'] ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Content Attachment Link</span>
                            @if (!empty($order->sjo['content_attachment_link']))
                                <a href="{{ $order->sjo['content_attachment_link'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold font-mono-code inline-flex items-center gap-1 mt-0.5">
                                    <span>Open Attachment ↗</span>
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                        @if (!empty($order->sjo['special_instructions']))
                            <div class="sm:col-span-2">
                                <span class="text-slate-400 block font-medium">Special Instructions / Remarks</span>
                                <p class="text-slate-800 mt-0.5">{{ $order->sjo['special_instructions'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- IT TELEMETRY & UPLOAD DETAILS (If submitted) -->
            @if ($order->it)
                <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">IT Broadcast Telemetry &amp; Monitoring</h2>
                        </div>
                        <span class="text-[10px] font-mono-code font-bold bg-cyan-50 text-cyan-800 px-2 py-0.5 rounded border border-cyan-200">
                            {{ $order->it['broadcast_type'] ?? 'Paid' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Assigned IT Personnel</span>
                            <span class="font-bold text-slate-900 block mt-0.5">{{ $order->it['assigned_it'] ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Spots Scheduled</span>
                            <span class="font-bold text-slate-900 block mt-0.5">{{ $order->it['spots'] ?? '—' }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 block font-medium">Transcoded Material Link</span>
                            <a href="{{ $order->it['material_link'] ?? '#' }}" target="_blank" class="text-cyan-700 hover:text-cyan-900 font-bold font-mono-code inline-flex items-center gap-1 mt-0.5">
                                <span>{{ $order->it['material_link'] ?? '—' }} ↗</span>
                            </a>
                        </div>
                        @if (!empty($order->it['monitoring_notes']))
                            <div class="sm:col-span-2">
                                <span class="text-slate-400 block font-medium">Monitoring Notes</span>
                                <p class="text-slate-800 mt-0.5">{{ $order->it['monitoring_notes'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- RIGHT COLUMN: Financials, Milestones, Amortization & Action History (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- FINANCIAL MILESTONES CARD -->
            <div class="glass-panel bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono-code">Financial Milestones</span>
                    <span class="text-xs font-mono-code font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">
                        Accrual Basis
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-slate-500 text-xs block font-medium">Total Contract Value</span>
                            <span class="text-xs text-slate-400 font-mono-code">100% of PO Amount</span>
                        </div>
                        <span class="text-lg font-extrabold text-slate-900 font-mono-code">{{ $milestones['formatted_total'] }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-200 flex items-center justify-between">
                        <div>
                            <span class="text-blue-900 text-xs block font-bold">Progress Billing ({{ $milestones['progress_rate'] }}%)</span>
                            <span class="text-[11px] text-blue-700 font-medium">Payable per progress milestone</span>
                        </div>
                        <span class="text-base font-extrabold text-blue-950 font-mono-code">{{ $milestones['formatted_progress_billing'] }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200 flex items-center justify-between">
                        <div>
                            <span class="text-amber-900 text-xs block font-bold">Retention Held ({{ $milestones['retention_rate'] }}%)</span>
                            <span class="text-[11px] text-amber-700 font-medium">Release: {{ $milestones['retention_release_date'] }}</span>
                        </div>
                        <span class="text-base font-extrabold text-amber-950 font-mono-code">{{ $milestones['formatted_retention_amount'] }}</span>
                    </div>
                </div>
            </div>

            <!-- MONTHLY AMORTIZATION SCHEDULE -->
            <div class="glass-panel bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono-code">Monthly Amortization Schedule</span>
                    <span class="text-[10px] text-slate-400 font-mono-code">Even monthly accrual</span>
                </div>

                @if (!empty($amortizationSchedule))
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-mono-code uppercase text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3">Month</th>
                                    <th class="py-2.5 px-3 text-right">Accrued Amount</th>
                                    <th class="py-2.5 px-3 text-right">Remaining Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono-code text-[11px]">
                                @foreach ($amortizationSchedule as $entry)
                                    <tr>
                                        <td class="py-2 px-3 font-semibold text-slate-800">{{ $entry['month_label'] }}</td>
                                        <td class="py-2 px-3 text-right text-emerald-700 font-bold">{{ $entry['formatted_amount'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-500">{{ $entry['formatted_remaining'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Campaign start and end dates required to calculate monthly schedule.</p>
                @endif
            </div>

            <!-- TIMESTAMPED ACTION HISTORY LOG -->
            <div class="glass-panel bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono-code">Action History &amp; Audit Trail</span>
                    <span class="text-xs font-mono-code font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">
                        {{ $order->logs->count() }} Entries
                    </span>
                </div>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse ($order->logs as $log)
                        <div class="relative">
                            <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full border-2 border-white {{ $log->action === 'declined' ? 'bg-rose-500' : ($log->action === 'completed' ? 'bg-emerald-500' : ($log->action === 'sent_back' ? 'bg-amber-500' : 'bg-blue-500')) }}"></span>
                            
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-xs text-slate-900 capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                <span class="text-[10px] font-mono-code text-slate-400">{{ $log->created_at->format('M d, Y • h:i A') }}</span>
                            </div>

                            <span class="text-[11px] text-slate-500 block font-medium mt-0.5">
                                By <strong class="text-slate-700">{{ $log->user?->name ?? 'System' }}</strong>
                            </span>

                            @if ($log->notes)
                                <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200 mt-2 leading-relaxed">
                                    {{ $log->notes }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No action history recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
