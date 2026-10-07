<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRF_{{ preg_replace('/[^A-Za-z0-9]/', '_', $first->srf_number) }}_Stock_Requisition_Form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.4;
            padding-bottom: 40px;
        }

        .no-print-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .no-print-bar .title {
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .no-print-bar .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            border: none;
        }

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-primary:hover {
            background: #047857;
        }

        .btn-secondary {
            background: #334155;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .pdf-container {
            max-width: 860px;
            margin: 24px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-radius: 4px;
            overflow: hidden;
        }

        .letterhead-banner {
            width: 100%;
            display: block;
        }

        .letterhead-banner img {
            width: 100%;
            height: auto;
            display: block;
        }

        .content-body {
            padding: 24px 32px 32px 32px;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0f172a;
        }

        .doc-header h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .doc-header .doc-subtitle {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 4px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Information Grid / Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .info-table td {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .info-table .label {
            background-color: #f8fafc;
            font-weight: 700;
            color: #334155;
            width: 16%;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .info-table .val {
            color: #0f172a;
            font-weight: 600;
            width: 34%;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-approved {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .badge-declined {
            background-color: #ffe4e6;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }

        .badge-on_hold {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* Items Table */
        .section-heading {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 11px;
        }

        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: 1px solid #0f172a;
            text-align: left;
            font-size: 10px;
        }

        .items-table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Signatories Box */
        .signatories-container {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-top: 18px;
            overflow: hidden;
            page-break-inside: avoid;
            background: #ffffff;
        }

        .signatories-header {
            background-color: #f8fafc;
            padding: 8px 12px;
            border-bottom: 1px solid #cbd5e1;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
        }

        .signatories-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signatories-table th {
            padding: 12px 12px 6px 12px;
            border-right: 1px solid #cbd5e1;
            text-align: left;
            vertical-align: top;
            background: #ffffff;
        }

        .signatories-table th:last-child {
            border-right: none;
        }

        .signatories-table td {
            padding: 0 12px 14px 12px;
            border-right: 1px solid #cbd5e1;
            text-align: left;
            vertical-align: top;
            background: #ffffff;
        }

        .signatories-table td:last-child {
            border-right: none;
        }

        .signatory-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .signatory-desc {
            font-size: 9px;
            color: #64748b;
            min-height: 20px;
        }

        .signatory-space {
            height: 38px;
        }

        .signatory-line {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 6px;
            width: 100%;
        }

        .signatory-name {
            font-weight: 700;
            font-size: 10.5px;
            color: #0f172a;
            text-transform: uppercase;
            line-height: 1.25;
            min-height: 22px;
        }

        .signatory-meta {
            font-size: 8.5px;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1.3;
        }

        .signatory-notes {
            font-size: 8px;
            color: #475569;
            font-style: italic;
            margin-top: 3px;
            line-height: 1.2;
        }

        /* Footer Notes */
        .doc-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .pdf-container {
                border: none;
                box-shadow: none;
                margin: 0;
                max-width: 100%;
                width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- Top Non-Printing Toolbar -->
    <div class="no-print-bar">
        <div class="title">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>Stock Requisition Form (SRF #{{ $first->srf_number }}) • Official PDF Preview</span>
        </div>
        <div class="btn-group">
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Download / Print PDF</span>
            </button>
            <button type="button" onclick="window.close()" class="btn btn-secondary">
                <span>Close Window</span>
            </button>
        </div>
    </div>

    <!-- PDF Document Sheet -->
    <div class="pdf-container">
        
        <!-- Header Banner / Letterhead Image -->
        <div class="letterhead-banner">
            @if ($letterheadBase64)
                <img src="{{ $letterheadBase64 }}" alt="Globaltronics Letterhead">
            @elseif (file_exists(public_path('images/letterhead.png')))
                <img src="{{ asset('images/letterhead.png') }}" alt="Globaltronics Letterhead">
            @else
                <div style="background: linear-gradient(135deg, #0284c7, #0f172a); color: white; padding: 24px 32px; font-weight: 800; font-size: 22px;">
                    GLOBALTRONICS • LEADING DIGITAL INNOVATION
                </div>
            @endif
        </div>

        <div class="content-body">
            
            <!-- Document Title -->
            <div class="doc-header">
                <h1>STOCK REQUISITION FORM</h1>
                <div class="doc-subtitle">
                    DOC REF: SOP-WMS-SRF-01 • FLOWCHART STEP 1 &amp; 2 HARDWARE CLEARANCE
                </div>
            </div>

            <!-- Form Data Values Table -->
            <table class="info-table">
                <tr>
                    <td class="label">CLIENT</td>
                    <td class="val">{{ $first->client ?? $first->project_name }}</td>
                    <td class="label">SRF #</td>
                    <td class="val font-mono" style="color: #0369a1; font-weight: 700;">{{ $first->srf_number }}</td>
                </tr>
                <tr>
                    <td class="label">PO #</td>
                    <td class="val font-mono">{{ $first->po_number ?? 'N/A' }}</td>
                    <td class="label">SSO NO.</td>
                    <td class="val font-mono" style="color: #1e3a8a; font-weight: 700;">{{ $first->sso_number }}</td>
                </tr>
                <tr>
                    <td class="label">PROJECT NAME</td>
                    <td class="val">{{ $first->project_name }}</td>
                    <td class="label">DATE FILED</td>
                    <td class="val font-mono">{{ $first->requisition_date?->format('F d, Y') ?? $first->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">DEPARTMENT</td>
                    <td class="val font-mono" style="color: #0284c7; font-weight: 700;">{{ $first->department ?? 'SALES' }}</td>
                    <td class="label">DATE NEEDED</td>
                    <td class="val font-mono" style="color: #b45309; font-weight: 700;">
                        {{ $first->date_needed?->format('F d, Y') ?? 'Immediate' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">APPROVAL STATUS</td>
                    <td class="val" colspan="3">
                        @php
                            $st = $first->status;
                            $stKey = in_array($st, ['approved', 'completed', 'verified']) ? 'approved' : ($st === 'declined' || $st === 'rejected' ? 'declined' : ($st === 'on_hold' ? 'on_hold' : 'pending'));
                            $stLabel = in_array($st, ['approved', 'completed', 'verified']) ? 'APPROVED' : ($st === 'declined' || $st === 'rejected' ? 'DECLINED' : ($st === 'on_hold' ? 'ON HOLD' : 'FOR APPROVAL'));
                        @endphp
                        <span class="badge-status badge-{{ $stKey }}">{{ $stLabel }}</span>
                    </td>
                </tr>
            </table>


            <!-- Requisition Items Table -->
            <div class="section-heading">
                <span>Requested Hardware &amp; Material Specifications</span>
                <span class="font-mono text-slate-500 font-normal">Total: {{ $items->count() }} item(s)</span>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 10%; text-align: center;">QTY</th>
                        <th style="width: 8%; text-align: center;">UOM</th>
                        <th style="width: 40%;">HARDWARE MODEL &amp; DESCRIPTION</th>
                        <th style="width: 22%;">REMARKS / PURPOSE</th>
                        <th style="width: 15%; text-align: center;">STOCK STAGING</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $idx => $it)
                        <tr>
                            <td style="text-align: center; font-weight: 700;" class="font-mono">{{ $idx + 1 }}</td>
                            <td style="text-align: center; font-weight: 700; color: #1d4ed8;" class="font-mono">{{ $it->quantity }}</td>
                            <td style="text-align: center; font-weight: 600;" class="font-mono">{{ $it->uom ?? 'PCS' }}</td>
                            <td>
                                <strong style="color: #0f172a; display: block;">{{ $it->inventoryItem?->model ?? 'Hardware Item' }}</strong>
                                <span style="font-size: 10px; color: #64748b;">
                                    Cat: {{ $it->inventoryItem?->category ?? 'Display Equipment' }} 
                                    @if ($it->inventoryItem?->manufacturer)
                                        • Mfr: {{ $it->inventoryItem->manufacturer }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span>{{ $it->remarks ?? 'Standard site allocation' }}</span>
                            </td>
                            <td style="text-align: center;">
                                @if ($it->stock_status === 'available_reserved')
                                    <span style="color: #065f46; font-weight: 700; font-size: 10px;">STAGE-BAY-01</span>
                                    <span style="display: block; font-size: 9px; color: #059669;">[IN STOCK]</span>
                                @else
                                    <span style="color: #92400e; font-weight: 700; font-size: 10px;">RECEIVING-HOLD</span>
                                    <span style="display: block; font-size: 9px; color: #d97706;">[PR/MRR HOLD]</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Signatories Section (PREPARED BY, NOTED BY, PRE-APPROVED BY, APPROVED BY) -->
            @php
                $dept = strtoupper($first->department ?? 'SALES');

                $prepDesc = match($dept) {
                    'SALES' => 'Sales Admin',
                    'PURCHASING' => 'Purchasing',
                    'TECHNICAL' => 'Project Team Lead',
                    'WAREHOUSE' => 'Warehouse',
                    'LOGISTICS' => 'Logistics Officer',
                    'MARKETING' => 'Marketing Specialist',
                    'IT' => 'IT Specialist',
                    default => 'Requisition Officer',
                };

                $notedDesc = match($dept) {
                    'SALES' => 'Sales Admin Manager',
                    'TECHNICAL' => 'Warehouse / Project Management',
                    'IT' => 'Senior IT Research and Development',
                    'WAREHOUSE', 'PURCHASING' => '',
                    default => '',
                };

                $preAppDesc = match($dept) {
                    'SALES', 'TECHNICAL', 'LOGISTICS', 'MARKETING' => 'PMO Technical',
                    'PURCHASING', 'IT' => 'PMO Technical Officer',
                    'WAREHOUSE' => '',
                    default => 'PMO Technical',
                };

                $appDesc = 'Chief of Services Officer';

                $prepName = $first->prepared_by ?: match($dept) {
                    'SALES' => 'Anne Libo-on',
                    'PURCHASING' => 'Darriane Imperial',
                    'TECHNICAL' => 'Ariel Moro',
                    'IT' => 'Stephanie Refe',
                    'WAREHOUSE' => 'Warehouse Team',
                    default => '',
                };

                $notedName = $first->noted_by ?? match($dept) {
                    'SALES' => 'Bernadette Federez',
                    'TECHNICAL' => 'Joshua Labios / Felix Tumambing',
                    'IT' => 'Paz Liquigan',
                    default => '',
                };

                $preAppName = $first->pre_approved_by ?? match($dept) {
                    'SALES', 'PURCHASING', 'IT' => 'Teddy Bajeta',
                    'TECHNICAL' => 'Teddy Mar Bajeta',
                    'WAREHOUSE' => '',
                    default => 'Teddy Bajeta',
                };

                $appName = $first->approved_by ?: 'Macy Guido Lee';
            @endphp

            <div class="signatories-container">
                <div class="signatories-header">
                    <span>Authorized Verification Signatories &amp; Clearance Chain ({{ $dept }} DEPARTMENT)</span>
                </div>
                <table class="signatories-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">
                                <div class="signatory-title">PREPARED BY</div>
                                <div class="signatory-desc">{{ $prepDesc }}</div>
                            </th>
                            <th style="width: 25%;">
                                <div class="signatory-title">NOTED BY</div>
                                <div class="signatory-desc">{{ $notedDesc }}</div>
                            </th>
                            <th style="width: 25%;">
                                <div class="signatory-title">PRE-APPROVED BY</div>
                                <div class="signatory-desc">{{ $preAppDesc }}</div>
                            </th>
                            <th style="width: 25%;">
                                <div class="signatory-title">APPROVED BY</div>
                                <div class="signatory-desc">{{ $appDesc }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="signatory-space"></div>
                                <div class="signatory-line"></div>
                                <div class="signatory-name">{{ $prepName }}</div>
                                <div class="signatory-meta">Filed: {{ $first->requisition_date?->format('M d, Y') ?? $first->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="signatory-space"></div>
                                <div class="signatory-line"></div>
                                <div class="signatory-name">{{ $notedName }}</div>
                                <div class="signatory-meta">{{ $notedDesc ? $notedDesc.' • Globaltronics' : '' }}</div>
                            </td>
                            <td>
                                <div class="signatory-space"></div>
                                <div class="signatory-line"></div>
                                <div class="signatory-name">{{ $preAppName }}</div>
                                <div class="signatory-meta">{{ $preAppName ? ($preAppDesc ? $preAppDesc.' • Globaltronics' : 'PMO • Globaltronics') : '' }}</div>
                            </td>
                            <td>
                                <div class="signatory-space"></div>
                                <div class="signatory-line"></div>
                                <div class="signatory-name">{{ $appName }}</div>
                                <div class="signatory-meta">
                                    @if ($first->verified_at)
                                        Cleared: {{ $first->verified_at->format('M d, Y • h:i A') }}
                                    @else
                                        Chief of Services Officer • Globaltronics
                                    @endif
                                </div>
                                @if ($first->verification_notes)
                                    <div class="signatory-notes">
                                        "{{ $first->verification_notes }}"
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Document Metadata -->
            <div class="doc-footer">
                <span>GLOBALTRONICS ICS • OFFICIAL WAREHOUSE &amp; LOGISTICS MANAGEMENT SYSTEM</span>
                <span>GENERATED: {{ now()->format('Y-m-d H:i:s') }} • PAGE 1 OF 1</span>
            </div>

        </div>
    </div>

    <script>
        // Auto-print if query param ?print=1 or ?download=1
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('print') === '1' || params.get('download') === '1') {
                setTimeout(function() {
                    window.print();
                }, 400);
            }
        });
    </script>
</body>
</html>
