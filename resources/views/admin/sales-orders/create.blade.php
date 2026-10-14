@extends('layouts.admin')

@section('title', 'File Advertising Sales Order (ASO) | Globaltronics ICS')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Header Card -->
    <div class="glass-panel bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-200">
                        STEP 1 • SALES REQUISITION
                    </span>
                    <span class="text-xs text-slate-400">• Form ASO-01</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span>File Advertising Sales Order (ASO)</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Initiate an official LED advertising contract. Upon filing, the order will enter the approval pipeline (1st sign-off by Jovy or Fred).
                </p>
            </div>

            <a href="{{ route('admin.sales-orders.index') }}" 
               class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs self-start sm:self-auto shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Queue</span>
            </a>
        </div>
    </div>

    <!-- ASO Form -->
    <form method="POST" action="{{ route('admin.sales-orders.store') }}" class="space-y-6" id="asoForm">
        @csrf

        <!-- Section 1: ASO Document Reference -->
        <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">1. ASO Document Reference &amp; BDS Filer</h2>
                </div>
                <span class="text-[11px] font-mono-code text-blue-600 font-bold bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                    Auto-Generated Sequential Number
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- ASO Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ASO Number *</label>
                    <input type="text" name="aso_number" value="{{ old('aso_number', $defaultAsoNumber) }}" required 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code text-blue-900 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('aso_number') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Date Filed -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Date Filed *</label>
                    <input type="date" name="date_filed" value="{{ old('date_filed', date('Y-m-d')) }}" required 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('date_filed') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- BDS Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">BDS Name *</label>
                    <input type="text" name="bds_name" value="{{ old('bds_name', Auth::user()->name) }}" required placeholder="Account Executive name"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('bds_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- BDS Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">BDS Email *</label>
                    <input type="email" name="bds_email" value="{{ old('bds_email', Auth::user()->email) }}" required placeholder="bds@globaltronics.net"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('bds_email') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Section 2: Client & Campaign Information -->
        <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">2. Client &amp; Campaign Specification</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Client / Company Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Client / Company Name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required placeholder="e.g. San Miguel Corporation / SM Prime"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('client_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- LED Sites -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">LED Sites / Locations *</label>
                    <input type="text" name="led_sites" value="{{ old('led_sites') }}" required placeholder="e.g. EDSA Guadalupe, C5 Market Market, Robinsons Galleria"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('led_sites') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Client Details -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Client Details &amp; Contact Info *</label>
                    <textarea name="client_details" rows="3" required placeholder="Authorized representatives, designation, phone numbers, brand marketing team..." 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500">{{ old('client_details') }}</textarea>
                    @error('client_details') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Ads Campaign Details -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ads Campaign Details &amp; Objectives *</label>
                    <textarea name="ads_details" rows="3" required placeholder="Campaign title, target impressions, dayparting restrictions, seasonal promotions..." 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500">{{ old('ads_details') }}</textarea>
                    @error('ads_details') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Section 3: Commercial & Broadcast Schedule -->
        <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">3. Commercials, Broadcast Flight &amp; PO</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Rate per Spot -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate Per Spot (₱) *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-xs">₱</span>
                        <input type="number" step="0.01" min="0" name="rate_per_spot" id="rate_per_spot" value="{{ old('rate_per_spot') }}" required placeholder="0.00"
                               class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code font-bold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    @error('rate_per_spot') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Spot Duration -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Spot Duration (Seconds) *</label>
                    <input type="number" min="1" name="spot_duration" id="spot_duration" value="{{ old('spot_duration', 15) }}" required placeholder="e.g. 15 or 30"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code font-bold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('spot_duration') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Campaign Start Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Campaign Start Date *</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('start_date') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Campaign End Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Campaign End Date *</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('end_date') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Type of Advertising Material -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Advertising Material Type *</label>
                    <select name="advertising_material_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="Static Image (JPG/PNG)" {{ old('advertising_material_type') === 'Static Image (JPG/PNG)' ? 'selected' : '' }}>Static Image (JPG/PNG)</option>
                        <option value="Video (MP4)" {{ old('advertising_material_type', 'Video (MP4)') === 'Video (MP4)' ? 'selected' : '' }}>Video (MP4)</option>
                        <option value="HTML5 / Dynamic Feed" {{ old('advertising_material_type') === 'HTML5 / Dynamic Feed' ? 'selected' : '' }}>HTML5 / Dynamic Feed</option>
                        <option value="Audio-Visual Stream" {{ old('advertising_material_type') === 'Audio-Visual Stream' ? 'selected' : '' }}>Audio-Visual Stream</option>
                    </select>
                    @error('advertising_material_type') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- PO Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">PO Number *</label>
                    <input type="text" name="po_number" value="{{ old('po_number') }}" required placeholder="e.g. PO-2026-9912"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code font-bold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('po_number') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Ref Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ref Number</label>
                    <input type="text" name="ref_number" value="{{ old('ref_number') }}" placeholder="Internal / Agency Ref"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('ref_number') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Flight Duration Preview -->
                <div class="p-3 bg-cyan-50/70 border border-cyan-200 rounded-xl flex flex-col justify-center">
                    <span class="text-[11px] font-bold text-cyan-800 uppercase tracking-wider block">Flight Duration Preview</span>
                    <span id="flightDurationLabel" class="text-xs font-mono-code font-bold text-cyan-950 mt-1">Select start &amp; end dates</span>
                </div>
            </div>
        </div>

        <!-- Section 4: Billing, Compliance & Contract Amount -->
        <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">4. Billing Details, Tax &amp; Total Project Amount</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Billing Contact Person -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Billing Contact Person *</label>
                    <input type="text" name="billing_contact_person" value="{{ old('billing_contact_person') }}" required placeholder="Finance / AP Officer name"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('billing_contact_person') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Billing Contact Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Billing Contact Number *</label>
                    <input type="text" name="billing_contact_number" value="{{ old('billing_contact_number') }}" required placeholder="+63 9XX XXX XXXX / Landline"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('billing_contact_number') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- TIN -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tax Identification Number (TIN) *</label>
                    <input type="text" name="tin" value="{{ old('tin') }}" required placeholder="000-000-000-000"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code font-bold focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('tin') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Business Style -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Business Style</label>
                    <input type="text" name="business_style" value="{{ old('business_style') }}" placeholder="e.g. Retail / Advertising / Real Estate"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500">
                    @error('business_style') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- VAT Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">VAT Status *</label>
                    <select name="vat" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="12% VAT Inclusive" {{ old('vat', '12% VAT Inclusive') === '12% VAT Inclusive' ? 'selected' : '' }}>12% VAT Inclusive</option>
                        <option value="12% VAT Exclusive" {{ old('vat') === '12% VAT Exclusive' ? 'selected' : '' }}>12% VAT Exclusive</option>
                        <option value="VAT-Exempt" {{ old('vat') === 'VAT-Exempt' ? 'selected' : '' }}>VAT-Exempt</option>
                        <option value="Zero-Rated" {{ old('vat') === 'Zero-Rated' ? 'selected' : '' }}>Zero-Rated</option>
                    </select>
                    @error('vat') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Payment Terms -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Terms *</label>
                    <select name="payment_terms" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="30 Days" {{ old('payment_terms', '30 Days') === '30 Days' ? 'selected' : '' }}>30 Days</option>
                        <option value="COD / Upfront" {{ old('payment_terms') === 'COD / Upfront' ? 'selected' : '' }}>COD / Upfront</option>
                        <option value="60 Days" {{ old('payment_terms') === '60 Days' ? 'selected' : '' }}>60 Days</option>
                        <option value="90 Days" {{ old('payment_terms') === '90 Days' ? 'selected' : '' }}>90 Days</option>
                        <option value="50% Down, 50% Completion" {{ old('payment_terms') === '50% Down, 50% Completion' ? 'selected' : '' }}>50% Down, 50% Completion</option>
                        <option value="Monthly Progress Billing" {{ old('payment_terms') === 'Monthly Progress Billing' ? 'selected' : '' }}>Monthly Progress Billing</option>
                    </select>
                    @error('payment_terms') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Billing Address -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Complete Billing Address *</label>
                    <textarea name="billing_address" rows="2" required placeholder="Floor, building, street, city, province, postal code..." 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500">{{ old('billing_address') }}</textarea>
                    @error('billing_address') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Total Project Amount -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Total Project Amount (₱) *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-extrabold text-sm">₱</span>
                        <input type="number" step="0.01" min="0" name="total_project_amount" id="total_project_amount" value="{{ old('total_project_amount') }}" required placeholder="0.00"
                               class="w-full pl-8 pr-3 py-2 bg-emerald-50/70 border border-emerald-300 rounded-xl text-sm font-mono-code font-extrabold text-emerald-950 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    @error('total_project_amount') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    <span id="amountInWords" class="text-[11px] text-emerald-700 font-mono-code mt-1 block"></span>
                </div>
            </div>
        </div>

        <!-- Form Submission Bar -->
        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                <span class="font-bold text-slate-700">Workflow Note:</span> Submitting this ASO form automatically routes to <strong class="text-slate-900">Jovy or Fred</strong> for 1st ASO Approval.
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('admin.sales-orders.index') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] text-center">
                    Submit ASO Form for 1st Approval →
                </button>
            </div>
        </div>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const durationLabel = document.getElementById('flightDurationLabel');
        const totalAmountInput = document.getElementById('total_project_amount');
        const amountInWords = document.getElementById('amountInWords');

        function updateDuration() {
            if (startDateInput.value && endDateInput.value) {
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);
                const diffTime = end - start;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                if (diffDays > 0) {
                    const months = (diffDays / 30.4).toFixed(1);
                    durationLabel.textContent = `${diffDays} calendar days (~${months} months)`;
                    durationLabel.classList.remove('text-rose-600');
                    durationLabel.classList.add('text-cyan-950');
                } else {
                    durationLabel.textContent = 'End date cannot precede start date';
                    durationLabel.classList.remove('text-cyan-950');
                    durationLabel.classList.add('text-rose-600');
                }
            }
        }

        function updateAmountPreview() {
            const val = parseFloat(totalAmountInput.value);
            if (!isNaN(val) && val > 0) {
                amountInWords.textContent = 'Formatted: ₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                amountInWords.textContent = '';
            }
        }

        startDateInput.addEventListener('change', updateDuration);
        endDateInput.addEventListener('change', updateDuration);
        totalAmountInput.addEventListener('input', updateAmountPreview);

        updateDuration();
        updateAmountPreview();
    });
</script>
@endsection
