# ADS Sales Order System (LED Advertising Workflow)

Digitized, role-governed LED advertising sales order management system seamlessly integrated into the **Globaltronics ICS / Sales Dashboard**.

---

## 1. Architectural Overview & Design Compliance

The system digitizes the entire lifecycle of LED advertising contracts without altering existing application aesthetics, utilizing the native **Globaltronics glassmorphism UI**, Tailwind design tokens, and Laravel 11 / Breeze Blade templates.

### Workflow Engine Architecture
All steps, role mappings, form specifications, and accounting audit checks are centralized in a single configuration file:
- **`app/Support/Workflow.php`**: Single source of truth. Modify steps, assignators, or form fields here to update the workflow application-wide.

---

## 2. The 7-Step Sequential Workflow

| Stage | Step Key | Title | Assigned Roles | Workflow Rule |
| :--- | :--- | :--- | :--- | :--- |
| **Stage 1** | `aso_draft` | **ASO Form Filing** | `sales` | Sales files 21 contract fields with auto-generated `ASO-YYYY-XXXX` number. |
| **Stage 2** | `aso_approval_1` | **ASO 1st Approval** | `jovy`, `fred` | Sign-off advances to 2nd approval. Decline sets status to *Declined*. |
| | `aso_approval_2` | **ASO 2nd Approval** | `owen` | Sign-off advances to 3rd approval. Decline sets status to *Declined*. |
| | `aso_approval_3` | **ASO 3rd Approval (Final ASO)** | `macy` | Final executive approval. Completes ASO and unlocks SJO Job Order creation. |
| **Stage 3** | `sjo_creation` | **Job Order (SJO) Form** | `sales` | Sales generates and files SJO with auto-generated `SJO-YYYY-XXXX` number. |
| **Stage 4** | `sjo_approval_1` | **SJO 1st Approval** | `badet`, `king` | Sign-off advances to 2nd SJO approval. Decline supported. |
| | `sjo_approval_2` | **SJO 2nd Approval** | `jovy`, `fred` | Sign-off advances to Content review. Decline supported. |
| **Stage 5** | `content_check` | **Check Advertising Material** | `content` | Material inspection. "No" sends order back to SJO Creation. |
| | `content_artist` | **Assign Artist & Review** | `content` | Artist assigned and sent to Fred. "No" sends back to material check. |
| | `content_client` | **Client / Partner Approval** | `content`, `sales`, `fred` | Client creative proofing sign-off. "No" sends back to artist. |
| **Stage 6** | `it_upload` | **IT Upload & Telemetry** | `it` | Assign IT upload personnel, player link, spots, type (*Paid/JV/Internal*), and notes. |
| **Stage 7** | `accounting_check` | **Accounting Verification Checklist** | `accounting` | Verification of all 10 compliance items (PO match, amortization, 2307, tax). |
| **Done** | `completed` | **Complete & Verified** | — | Stores status `Complete` with timestamp and locks the campaign. |

---

## 3. Access Control & Role Matrix

- **Roles**: `jovy`, `fred`, `owen`, `macy`, `badet`, `king`, `sales`, `content`, `it`, `accounting`.
- **`is_admin` Flag**: System administrators (`is_admin = true` or `it-admin`/`admin` role) have override access to all queues, reports, and role configurations.
- **My Queue vs All Orders**:
  - **My Queue (`?tab=my_queue`)**: Displays only orders currently waiting for the authenticated user's assigned role(s).
  - **All Accessible Orders (`?tab=all`)**: Users can view orders they filed or previously acted upon in the audit trail.
  - **Admins & Accounting**: Automatically see all orders across the company.
- **Server-Side Enforcement**: Enforced by [`App\Policies\SalesOrderPolicy`](file:///c:/Users/marke/OneDrive/Desktop/GBTX_APPWHMS/app/Policies/SalesOrderPolicy.php), returning HTTP 403 if an unauthorized user attempts to act on an order.
- **Role Assignment Screen**: Admin-only web interface at [`/admin/sales-orders/roles`](file:///c:/Users/marke/OneDrive/Desktop/GBTX_APPWHMS/resources/views/admin/sales-orders/roles.blade.php).

---

## 4. Immediate Email Notifications

Whenever an order advances, an immediate (synchronous) Laravel Mail notification ([`App\Notifications\OrderStepNotification`](file:///c:/Users/marke/OneDrive/Desktop/GBTX_APPWHMS/app/Notifications/OrderStepNotification.php)) is sent to **all users holding the role of the next step**, including a direct secure button link to the order.

If an order is declined at any point, an immediate notification is sent directly to the **sales filer** detailing the timestamp, actor, and reason.

---

## 5. Financial Reports (Admins & Accounting Only)

Available at [`/admin/sales-orders/reports`](file:///c:/Users/marke/OneDrive/Desktop/GBTX_APPWHMS/resources/views/admin/sales-orders/reports.blade.php):
1. **Executive Dashboard**: Total orders, in-progress, declined, complete, approved contract value, value awaiting approval, and orders per step.
2. **Accrual Basis & Milestones**:
   - Total Cost Base on PO commitments (₱).
   - **90% Progress Billing**: Recognized across campaign delivery.
   - **10% Retention Constant**: Held until full campaign completion and final accounting reconciliation.
3. **Consolidated Monthly Amortization Schedule**: Spreads order revenue evenly across calendar flight months.
4. **Sales by BDS**: Breakdowns of order count and total sales (₱) per Account Executive.

---

## 6. Step-by-Step Installation Instructions

### Step 1: Run Database Migrations
Execute the migrations to create `sales_orders`, `order_logs`, add `is_admin`, and seed the ADS roles:
```bash
php artisan migrate
```

### Step 2: Make the First User an Administrator
Using Laravel Tinker, set the `is_admin` flag to `true` on your primary administrative user account:
```bash
php artisan tinker --execute '$u = App\Models\User::where("email", "mark.estoesta@globaltronics.net")->first() ?? App\Models\User::first(); $u->is_admin = true; $u->save(); echo "Admin privileges granted to: " . $u->email . PHP_EOL;'
```

Alternatively, to grant `is_admin` to all administrative personnel:
```bash
php artisan tinker --execute 'App\Models\User::whereIn("email", ["mark.estoesta@globaltronics.net", "admin@globaltronics.net"])->update(["is_admin" => true]);'
```

### Step 3: Assign ADS Workflow Roles to Users
1. Log in to the application and navigate to **Navigation → ADS Roles** or open:
   `https://[YOUR_DOMAIN]/admin/sales-orders/roles`
2. Assign the relevant team members to their respective workflow roles:
   - **Jovy**: Assign `jovy` role (ASO 1st Approval & SJO 2nd Approval).
   - **Fred**: Assign `fred` role (ASO 1st Approval, SJO 2nd Approval & Creative Review).
   - **Owen**: Assign `owen` role (ASO 2nd Approval).
   - **M. Macy**: Assign `macy` role (ASO 3rd Final Approval).
   - **Badet**: Assign `badet` role (SJO 1st Approval).
   - **King**: Assign `king` role (SJO 1st Approval).
   - **Sales Team**: Assign `sales` role (Can file new ASO and SJO forms).
   - **Content Team**: Assign `content` role (Material check, artist assignment, client proofing).
   - **IT Team**: Assign `it` role (LED player upload and monitoring telemetry).
   - **Accounting Team**: Assign `accounting` role (Verification checklist and financial reports).

Or assign via Tinker directly:
```bash
php artisan tinker --execute '
$salesRole = App\Models\Role::where("slug", "sales")->first();
$salesUser = App\Models\User::where("email", "sales@globaltronics.net")->first();
if ($salesRole && $salesUser) {
    $salesUser->roles()->syncWithoutDetaching([$salesRole->id]);
    echo "Sales role attached to: " . $salesUser->email . PHP_EOL;
}
'
```

### Step 4: Verify Routes & Views
Ensure view cache is fresh and ready:
```bash
php artisan view:clear
php artisan route:list --path=sales-orders
```

---

## 7. Currency & Form Validation

- **Currency**: Philippine Pesos (`₱`) standard formatting with 2 decimal places (e.g., `₱1,250,000.00`).
- **Validation**: All 21 ASO fields, SJO fields, IT monitoring inputs, and Accounting checklist items are strictly validated server-side.
- **Audit Logs**: Every transition, approval, return, and rejection is recorded in `order_logs` with a timestamp and the user ID.
