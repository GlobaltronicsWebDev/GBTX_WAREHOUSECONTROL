<?php

namespace App\Support;

class Workflow
{
    // Roles
    public const ROLE_JOVY = 'jovy';
    public const ROLE_FRED = 'fred';
    public const ROLE_OWEN = 'owen';
    public const ROLE_MACY = 'macy';
    public const ROLE_BADET = 'badet';
    public const ROLE_KING = 'king';
    public const ROLE_SALES = 'sales';
    public const ROLE_CONTENT = 'content';
    public const ROLE_IT = 'it';
    public const ROLE_ACCOUNTING = 'accounting';

    // Steps
    public const STEP_ASO_DRAFT = 'aso_draft';
    public const STEP_ASO_APPROVAL_1 = 'aso_approval_1'; // Jovy or Fred
    public const STEP_ASO_APPROVAL_2 = 'aso_approval_2'; // Owen
    public const STEP_ASO_APPROVAL_3 = 'aso_approval_3'; // M. Macy
    public const STEP_SJO_CREATION = 'sjo_creation';     // After ASO complete, filed by Sales
    public const STEP_SJO_APPROVAL_1 = 'sjo_approval_1'; // Badet or King
    public const STEP_SJO_APPROVAL_2 = 'sjo_approval_2'; // Jovy or Fred
    public const STEP_CONTENT_CHECK = 'content_check';   // Content team checks material
    public const STEP_CONTENT_ARTIST = 'content_artist'; // Artist assigned & sent to Fred
    public const STEP_CONTENT_CLIENT = 'content_client'; // Client/Partner approval
    public const STEP_IT_UPLOAD = 'it_upload';           // Assign IT to upload & monitor
    public const STEP_ACCOUNTING_CHECK = 'accounting_check'; // Accounting verification checklist
    public const STEP_COMPLETED = 'completed';
    public const STEP_DECLINED = 'declined';

    // Statuses
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_DECLINED = 'Declined';
    public const STATUS_COMPLETE = 'Complete';

    // Retention constant
    public const RETENTION_PERCENTAGE = 0.10; // 10% retention held until campaign end

    /**
     * All available roles with labels and badge styling.
     */
    public static function roles(): array
    {
        return [
            self::ROLE_SALES => [
                'name' => 'Sales / Account Executive',
                'description' => 'Files ASO and SJO requisitions',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            ],
            self::ROLE_JOVY => [
                'name' => 'Jovy (Approver)',
                'description' => 'ASO 1st Approval & SJO 2nd Approval',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
            ],
            self::ROLE_FRED => [
                'name' => 'Fred (Ads Sales Approver)',
                'description' => 'ASO 1st Approval, SJO 2nd Approval & Creative Review',
                'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            ],
            self::ROLE_OWEN => [
                'name' => 'Owen (Approver)',
                'description' => 'ASO 2nd Approval',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
            ],
            self::ROLE_MACY => [
                'name' => 'M. Macy (Chief / Executive Approver)',
                'description' => 'ASO 3rd Final Approval',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-200',
            ],
            self::ROLE_BADET => [
                'name' => 'Badet (Approver)',
                'description' => 'SJO 1st Approval',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ],
            self::ROLE_KING => [
                'name' => 'King (Approver)',
                'description' => 'SJO 1st Approval',
                'badge' => 'bg-teal-100 text-teal-800 border-teal-200',
            ],
            self::ROLE_CONTENT => [
                'name' => 'Content Team',
                'description' => 'Creative inspection, artist assignment, and client proofing',
                'badge' => 'bg-pink-100 text-pink-800 border-pink-200',
            ],
            self::ROLE_IT => [
                'name' => 'IT Operations',
                'description' => 'Upload to LED controller & monitoring inputs',
                'badge' => 'bg-cyan-100 text-cyan-900 border-cyan-200',
            ],
            self::ROLE_ACCOUNTING => [
                'name' => 'Accounting & Finance',
                'description' => 'Verification checklist, amortization, tax compliance, and billing',
                'badge' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
            ],
        ];
    }

    /**
     * Definition of every workflow step in sequential order.
     */
    public static function steps(): array
    {
        return [
            self::STEP_ASO_APPROVAL_1 => [
                'key' => self::STEP_ASO_APPROVAL_1,
                'stage' => 'ASO Approvals',
                'title' => 'ASO 1st Approval',
                'roles' => [self::ROLE_JOVY, self::ROLE_FRED],
                'description' => 'Requires sign-off from Jovy or Fred.',
                'can_decline' => true,
                'next_step' => self::STEP_ASO_APPROVAL_2,
            ],
            self::STEP_ASO_APPROVAL_2 => [
                'key' => self::STEP_ASO_APPROVAL_2,
                'stage' => 'ASO Approvals',
                'title' => 'ASO 2nd Approval',
                'roles' => [self::ROLE_OWEN],
                'description' => 'Requires sign-off from Owen.',
                'can_decline' => true,
                'next_step' => self::STEP_ASO_APPROVAL_3,
            ],
            self::STEP_ASO_APPROVAL_3 => [
                'key' => self::STEP_ASO_APPROVAL_3,
                'stage' => 'ASO Approvals',
                'title' => 'ASO 3rd Approval (Final ASO)',
                'roles' => [self::ROLE_MACY],
                'description' => 'Requires final sign-off from M. Macy. Upon approval, ASO is marked Complete.',
                'can_decline' => true,
                'next_step' => self::STEP_SJO_CREATION,
            ],
            self::STEP_SJO_CREATION => [
                'key' => self::STEP_SJO_CREATION,
                'stage' => 'Job Order',
                'title' => 'Job Order (SJO) Form Creation',
                'roles' => [self::ROLE_SALES],
                'description' => 'ASO is Complete! Sales generates and files the Job Order (SJO).',
                'can_decline' => false,
                'next_step' => self::STEP_SJO_APPROVAL_1,
            ],
            self::STEP_SJO_APPROVAL_1 => [
                'key' => self::STEP_SJO_APPROVAL_1,
                'stage' => 'SJO Approvals',
                'title' => 'SJO 1st Approval',
                'roles' => [self::ROLE_BADET, self::ROLE_KING],
                'description' => 'Requires sign-off from Badet or King.',
                'can_decline' => true,
                'next_step' => self::STEP_SJO_APPROVAL_2,
            ],
            self::STEP_SJO_APPROVAL_2 => [
                'key' => self::STEP_SJO_APPROVAL_2,
                'stage' => 'SJO Approvals',
                'title' => 'SJO 2nd Approval',
                'roles' => [self::ROLE_JOVY, self::ROLE_FRED],
                'description' => 'Requires sign-off from Jovy or Fred.',
                'can_decline' => true,
                'next_step' => self::STEP_CONTENT_CHECK,
            ],
            self::STEP_CONTENT_CHECK => [
                'key' => self::STEP_CONTENT_CHECK,
                'stage' => 'Content Team',
                'title' => 'Check Advertising Material',
                'roles' => [self::ROLE_CONTENT],
                'description' => 'Content team inspects material specs and compliance.',
                'can_send_back' => true,
                'send_back_step' => self::STEP_SJO_CREATION,
                'next_step' => self::STEP_CONTENT_ARTIST,
            ],
            self::STEP_CONTENT_ARTIST => [
                'key' => self::STEP_CONTENT_ARTIST,
                'stage' => 'Content Team',
                'title' => 'Assign Artist & Forward to Fred',
                'roles' => [self::ROLE_CONTENT],
                'description' => 'Assign creative artist and forward proofing to Fred (Ads Sales).',
                'can_send_back' => true,
                'send_back_step' => self::STEP_CONTENT_CHECK,
                'next_step' => self::STEP_CONTENT_CLIENT,
            ],
            self::STEP_CONTENT_CLIENT => [
                'key' => self::STEP_CONTENT_CLIENT,
                'stage' => 'Content Team',
                'title' => 'Client / Partner Creative Approval',
                'roles' => [self::ROLE_CONTENT, self::ROLE_SALES, self::ROLE_FRED],
                'description' => 'Final client/partner sign-off on creative proofing. "No" sends back to artist.',
                'can_send_back' => true,
                'send_back_step' => self::STEP_CONTENT_ARTIST,
                'next_step' => self::STEP_IT_UPLOAD,
            ],
            self::STEP_IT_UPLOAD => [
                'key' => self::STEP_IT_UPLOAD,
                'stage' => 'IT Operations',
                'title' => 'Assign IT to Upload & Monitor',
                'roles' => [self::ROLE_IT],
                'description' => 'Assign IT personnel (received via Viber group), upload media to LED player, and record monitoring telemetry.',
                'can_decline' => false,
                'next_step' => self::STEP_ACCOUNTING_CHECK,
            ],
            self::STEP_ACCOUNTING_CHECK => [
                'key' => self::STEP_ACCOUNTING_CHECK,
                'stage' => 'Accounting & Finance',
                'title' => 'Accounting Verification Checklist',
                'roles' => [self::ROLE_ACCOUNTING],
                'description' => 'Verify PO/contracts, billing details, photo/video proofs, amortization, and tax audit.',
                'can_decline' => false,
                'next_step' => self::STEP_COMPLETED,
            ],
            self::STEP_COMPLETED => [
                'key' => self::STEP_COMPLETED,
                'stage' => 'Final',
                'title' => 'Completed & Verified',
                'roles' => [],
                'description' => 'Order has fulfilled all approvals, execution, and accounting compliance.',
            ],
            self::STEP_DECLINED => [
                'key' => self::STEP_DECLINED,
                'stage' => 'Archived',
                'title' => 'Declined',
                'roles' => [],
                'description' => 'Order was declined during an approval stage.',
            ],
        ];
    }

    /**
     * ASO Form Fields configuration.
     */
    public static function asoFields(): array
    {
        return [
            'bds_name' => ['label' => 'BDS Name', 'type' => 'text', 'required' => true],
            'bds_email' => ['label' => 'BDS Email', 'type' => 'email', 'required' => true],
            'client_name' => ['label' => 'Client / Company Name', 'type' => 'text', 'required' => true],
            'client_details' => ['label' => 'Client Details & Contact Info', 'type' => 'textarea', 'required' => true],
            'ads_details' => ['label' => 'Ads Campaign Details', 'type' => 'textarea', 'required' => true],
            'led_sites' => ['label' => 'LED Sites / Locations', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g., EDSA Guadalupe, C5 Flyover, Bonifacio High Street'],
            'rate_per_spot' => ['label' => 'Rate Per Spot (₱)', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'spot_duration' => ['label' => 'Spot Duration (Seconds)', 'type' => 'number', 'required' => true, 'placeholder' => '15, 30, etc.'],
            'start_date' => ['label' => 'Campaign Start Date', 'type' => 'date', 'required' => true],
            'end_date' => ['label' => 'Campaign End Date', 'type' => 'date', 'required' => true],
            'advertising_material_type' => ['label' => 'Type of Advertising Material', 'type' => 'select', 'options' => ['Static Image (JPG/PNG)', 'Video (MP4)', 'HTML5 / Dynamic Feed', 'Audio-Visual Stream'], 'required' => true],
            'po_number' => ['label' => 'PO Number', 'type' => 'text', 'required' => true],
            'ref_number' => ['label' => 'Ref Number', 'type' => 'text', 'required' => false],
            'billing_contact_person' => ['label' => 'Billing Contact Person', 'type' => 'text', 'required' => true],
            'billing_contact_number' => ['label' => 'Billing Contact Number', 'type' => 'text', 'required' => true],
            'billing_address' => ['label' => 'Billing Address', 'type' => 'textarea', 'required' => true],
            'tin' => ['label' => 'Tax Identification Number (TIN)', 'type' => 'text', 'required' => true],
            'business_style' => ['label' => 'Business Style', 'type' => 'text', 'required' => false],
            'vat' => ['label' => 'VAT Status', 'type' => 'select', 'options' => ['12% VAT Inclusive', '12% VAT Exclusive', 'VAT-Exempt', 'Zero-Rated'], 'required' => true],
            'payment_terms' => ['label' => 'Payment Terms', 'type' => 'select', 'options' => ['COD / Upfront', '30 Days', '60 Days', '90 Days', '50% Down, 50% Completion', 'Monthly Progress Billing'], 'required' => true],
            'total_project_amount' => ['label' => 'Total Project Amount (₱)', 'type' => 'number', 'step' => '0.01', 'required' => true],
        ];
    }

    /**
     * SJO Form Fields configuration.
     */
    public static function sjoFields(): array
    {
        return [
            'client_company' => ['label' => 'Client / Company', 'type' => 'text', 'required' => true],
            'advertising_material' => ['label' => 'Advertising Material Summary', 'type' => 'text', 'required' => true],
            'spot_number' => ['label' => 'Spot Number / Rotation Slot', 'type' => 'text', 'required' => true],
            'block_timer' => ['label' => 'Block Timer (Seconds / Frequency)', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. 15 sec every 10 mins'],
            'content_attachment_link' => ['label' => 'Content Attachment Link (Drive/Viber/Cloud)', 'type' => 'url', 'required' => true],
            'special_instructions' => ['label' => 'Special Instructions / Remarks', 'type' => 'textarea', 'required' => false],
        ];
    }

    /**
     * Accounting Checklist Items configuration.
     */
    public static function accountingChecklistItems(): array
    {
        return [
            'po_contract_matching' => 'PO and Contract Matching Verified',
            'campaign_verification' => 'Campaign Dates & Flight Schedule Verified',
            'proof_evidence' => 'Photo Proofing / Video Evidence Confirmed',
            'adjusted_dates' => 'Adjusted Dates & Make-Good Hours Reconciled',
            'payables_debit_credit' => 'Payables (Debit / Credit) Analyzed',
            'monthly_amortization' => 'Monthly Amortization Schedule Booked',
            'journal_entries' => 'Journal Entries Posted to General Ledger',
            'reconciliation_tax_audit' => 'Reconciliation & Tax Audit Cleared',
            'billing_details' => 'Billing Details & Sales Invoice Generated',
            'tax_compliance' => 'Tax (Creditable Withholding / Form 2307) & Compliance Verified',
        ];
    }

    /**
     * Check if a given user can act on the specified step.
     */
    public static function canUserActOnStep(?\App\Models\User $user, string $stepKey): bool
    {
        if (!$user) {
            return false;
        }

        // Admin override
        if ($user->is_admin || $user->hasRole(['admin', 'it-admin'])) {
            return true;
        }

        $step = self::steps()[$stepKey] ?? null;
        if (!$step || empty($step['roles'])) {
            return false;
        }

        return $user->hasRole($step['roles']);
    }

    /**
     * Helper to format currency in Philippine Pesos (₱).
     */
    public static function formatMoney(float|int|string|null $amount): string
    {
        $num = (float) ($amount ?? 0);
        return '₱' . number_format($num, 2);
    }
}
