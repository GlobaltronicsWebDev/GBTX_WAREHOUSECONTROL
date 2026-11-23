<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\SrfRequisition;
use App\Models\TransactionNotification;
use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_admin_dashboard_and_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->first());

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Operations Overview');
        $response->assertSee('My Dashboard');
        $response->assertSee($user->email);
        $response->assertSee('Notifications');
        $response->assertSee('Log out');
        $response->assertSee(route('admin.settings.companies'));
        $response->assertSee(route('admin.reports.index'));
        $response->assertSee(route('admin.dashboard').'#alertsPanel');
        $response->assertSee('Low Stock SKUs');
        $response->assertDontSee('PR-2026-089');
        $response->assertSee('Unavailable');
        $response->assertSee('Not tracked');
        $response->assertSee('Connected records only');
        $response->assertSee('data-sort-key="sortRef"', false);
        $response->assertSee('Purchase Requests');
        $response->assertSee('Receivings');
        $response->assertSee('Deliverables');
        $response->assertSee('No live SRF records are available.');
        $response->assertSee(route('admin.inventory.dashboard'));
        $response->assertDontSee('Sales Order');
        $response->assertDontSee('Sales Order');
    }

    public function test_dashboard_shows_only_shared_and_current_user_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TransactionNotification::create([
            'title' => 'Shared operational notice',
            'message' => 'This notice is visible to all signed-in users.',
            'type' => 'general',
        ]);
        TransactionNotification::create([
            'user_id' => $user->id,
            'title' => 'Personal operational notice',
            'message' => 'This notice is assigned to the signed-in user.',
            'type' => 'general',
        ]);
        TransactionNotification::create([
            'user_id' => $otherUser->id,
            'title' => 'Private operational notice',
            'message' => 'This notice belongs to another user.',
            'type' => 'general',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertSee('Shared operational notice');
        $response->assertSee('Personal operational notice');
        $response->assertDontSee('Private operational notice');
        $response->assertSee('2 unread');
        $response->assertDontSee('IMP-2026-041');
    }

    public function test_user_can_mark_their_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = TransactionNotification::create([
            'user_id' => $user->id,
            'title' => 'Personal operational notice',
            'message' => 'A notification assigned to this user.',
            'type' => 'general',
        ]);

        $response = $this->actingAs($user)->post(route('admin.notifications.read', $notification));

        $response->assertRedirect();
        $this->assertDatabaseHas('transaction_notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = TransactionNotification::create([
            'user_id' => $otherUser->id,
            'title' => 'Private operational notice',
            'message' => 'A notification assigned to another user.',
            'type' => 'general',
        ]);

        $response = $this->actingAs($user)->post(route('admin.notifications.read', $notification));

        $response->assertForbidden();
        $this->assertDatabaseHas('transaction_notifications', [
            'id' => $notification->id,
            'is_read' => false,
        ]);
    }

    public function test_it_admin_can_open_empty_company_settings(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());

        $response = $this->actingAs($admin)->get(route('admin.settings.companies'));

        $response->assertSee('Settings');
        $response->assertSee('Company');
        $response->assertSee('No companies have been added yet.');
        $response->assertSee('Add Company');
        $response->assertSee(route('admin.settings.profile'));
        $response->assertSee(route('admin.settings.security'));
    }

    public function test_non_it_user_cannot_open_company_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.settings.companies'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('error');
    }

    public function test_reports_use_live_srf_counts_and_mark_inventory_reports_unavailable(): void
    {
        $user = User::factory()->create();
        SrfRequisition::create([
            'srf_number' => '2026 - 1500',
            'sso_number' => 'SSO-2026-0500',
            'project_name' => 'Live report project',
            'client' => 'Live report client',
            'po_number' => 'PO-2026-0500',
            'requisition_date' => now(),
            'item_model' => 'Report test item',
            'quantity' => 3,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
            'prepared_by' => $user->name,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports.index'));

        $response->assertSee('data-report-count="1"', false);
        $response->assertSee('Stock Movement Reports');
        $response->assertSee('Transaction Listing Reports');
        $response->assertSee('Inventory Stock List Reports');
        $response->assertSee('Inventory records are not tracked');
    }

    public function test_transaction_report_filters_by_status_and_only_shows_the_users_srf_records(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        SrfRequisition::create([
            'srf_number' => '2026 - 1501',
            'sso_number' => 'SSO-2026-0501',
            'project_name' => 'Pending report project',
            'client' => 'Current user client',
            'po_number' => 'PO-2026-0501',
            'requisition_date' => now(),
            'item_model' => 'Current user report item',
            'quantity' => 1,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
            'prepared_by' => $user->name,
            'user_id' => $user->id,
        ]);
        SrfRequisition::create([
            'srf_number' => '2026 - 1502',
            'sso_number' => 'SSO-2026-0502',
            'project_name' => 'Other user report project',
            'client' => 'Other user client',
            'po_number' => 'PO-2026-0502',
            'requisition_date' => now(),
            'item_model' => 'Other user private item',
            'quantity' => 1,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
            'prepared_by' => $otherUser->name,
            'user_id' => $otherUser->id,
        ]);
        SrfRequisition::create([
            'srf_number' => '2026 - 1503',
            'sso_number' => 'SSO-2026-0503',
            'project_name' => 'Approved report project',
            'client' => 'Current user client',
            'po_number' => 'PO-2026-0503',
            'requisition_date' => now(),
            'item_model' => 'Approved report item',
            'quantity' => 1,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'approved',
            'prepared_by' => $user->name,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports.transactions', [
            'status' => 'pending',
        ]));

        $response->assertOk();
        $response->assertSee('2026 - 1501');
        $response->assertDontSee('2026 - 1502');
        $response->assertDontSee('2026 - 1503');
    }

    public function test_guest_cannot_open_reports(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect('/login');
    }

    public function test_dashboard_greeting_uses_account_first_name_before_noon(): void
    {
        $user = User::factory()->create(['name' => 'Paz Santos']);

        $this->travelTo(now()->setTime(9, 0), function () use ($user): void {
            $this->actingAs($user)
                ->get(route('admin.dashboard'))
                ->assertSee('Good Day Paz!');
        });
    }

    public function test_dashboard_greeting_uses_afternoon_after_noon(): void
    {
        $user = User::factory()->create(['name' => 'Paz Santos']);

        $this->travelTo(now()->setTime(15, 0), function () use ($user): void {
            $this->actingAs($user)
                ->get(route('admin.dashboard'))
                ->assertSee('Good Afternoon Paz!');
        });
    }

    public function test_guest_cannot_access_inventory_dashboard_and_is_redirected_to_login(): void
    {
        $this->get(route('admin.inventory.dashboard'))
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_inventory_dashboard_prototype(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.inventory.dashboard'));

        $response->assertOk();
        $response->assertSee('Inventory Management');
        $response->assertSee('Inventory / Dashboard');
        $response->assertDontSee('UI prototype · BETA');
        $response->assertSee('Export CSV');
        $response->assertSee('Today’s Movement');
        $response->assertSee('Stock Health');
        $response->assertSee('Recent Movement');
    }

    public function test_stock_health_shows_zero_counts_for_empty_inventory(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.inventory.dashboard'));

        $response->assertViewHas('totalItems', 0);
        $response->assertViewHas('stockHealth', [
            'In Stock' => 0,
            'Low Stock' => 0,
            'Last Stock' => 0,
            'Out of Stock' => 0,
        ]);
        $response->assertSee('0%');
        $response->assertSee('href="'.route('admin.inventory.dashboard').'#stockHealthHeading"', false);
        $response->assertDontSee('Stock figures are sample values');
    }

    public function test_stock_health_counts_available_quantities_across_warehouses_and_excludes_deleted_items(): void
    {
        $warehouses = WarehouseLocation::factory()->count(2)->create();
        foreach ([0, 1, 2, 5, 6, 6, 6, 100] as $index => $quantity) {
            InventoryItem::create([
                'item_description' => 'Inventory item '.$index,
                'available_qty' => $quantity,
                'warehouse_location_id' => $warehouses[$index % 2]->id,
            ]);
        }
        InventoryItem::create(['item_description' => 'Deleted item', 'available_qty' => 0])->delete();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.inventory.dashboard'));

        $response->assertViewHas('totalItems', 8);
        $response->assertViewHas('stockHealth', [
            'In Stock' => 4,
            'Low Stock' => 2,
            'Last Stock' => 1,
            'Out of Stock' => 1,
        ]);
        $response->assertSeeInOrder(['Total Items', 'In Stock', '4</dd>', '50%</dd>', 'Low Stock', '2</dd>', '25%</dd>', 'Last Stock', '1</dd>', '12.5%</dd>', 'Out of Stock', '1</dd>', '12.5%</dd>'], false);
    }

    public function test_stock_health_refresh_reflects_inventory_changes(): void
    {
        $item = InventoryItem::create(['item_description' => 'Panel', 'available_qty' => 1]);
        $this->actingAs(User::factory()->create())->get(route('admin.inventory.dashboard'))
            ->assertViewHas('stockHealth', ['In Stock' => 0, 'Low Stock' => 0, 'Last Stock' => 1, 'Out of Stock' => 0]);

        $item->update(['available_qty' => 0]);
        $response = $this->get(route('admin.inventory.dashboard'));

        $response->assertViewHas('stockHealth', ['In Stock' => 0, 'Low Stock' => 0, 'Last Stock' => 0, 'Out of Stock' => 1]);
    }

    public function test_admin_can_create_user_with_roles(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $staffRole = Role::where('slug', 'warehouse-staff')->first();
        $department = Department::factory()->create();
        $company = Company::factory()->create();
        $admin->roles()->attach($itRole);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Carlos Mendoza',
            'email' => 'carlos.mendoza@globaltronics.net',
            'password' => 'SecurePass123!@#',
            'roles' => [$staffRole->id],
            'department_id' => $department->id,
            'company_id' => $company->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'carlos.mendoza@globaltronics.net',
            'name' => 'Carlos Mendoza',
            'department_id' => $department->id,
            'company_id' => $company->id,
        ]);

        $createdUser = User::where('email', 'carlos.mendoza@globaltronics.net')->first();
        $this->assertTrue($createdUser->roles->contains($staffRole->id));
        $this->assertTrue(Hash::check('SecurePass123!@#', $createdUser->password));
        Notification::assertSentTo($createdUser, VerifyEmail::class);
    }

    public function test_admin_can_update_user_credentials_and_roles(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $admin->roles()->attach($itRole);

        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old.email@globaltronics.net',
        ]);

        $warehouseAdminRole = Role::where('slug', 'warehouse-admin')->first();

        $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
            'name' => 'Updated Name',
            'email' => 'updated.email@globaltronics.net',
            'password' => 'NewPassword999!',
            'roles' => [$warehouseAdminRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $targetUser->refresh();

        $this->assertSame('Updated Name', $targetUser->name);
        $this->assertSame('updated.email@globaltronics.net', $targetUser->email);
        $this->assertNull($targetUser->email_verified_at);
        $this->assertTrue(Hash::check('NewPassword999!', $targetUser->password));
        $this->assertTrue($targetUser->roles->contains($warehouseAdminRole->id));
        Notification::assertSentTo($targetUser, VerifyEmail::class);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $admin->roles()->attach($itRole);

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_delete_last_it_admin(): void
    {
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $admin->roles()->attach($itRole);

        $otherAdmin = User::factory()->create();
        $staffRole = Role::where('slug', 'warehouse-staff')->first();
        $otherAdmin->roles()->attach($staffRole);

        // otherAdmin attempts to delete the ONLY it-admin
        $response = $this->actingAs($otherAdmin)->delete("/admin/users/{$admin->id}");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $admin->roles()->attach($itRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$targetUser->id}");

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_can_create_custom_role(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());

        $response = $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'Quality Assurance Lead',
            'description' => 'Reviews incoming pallet conditions and barcode consistency.',
            'badge_color' => 'teal',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => 'Quality Assurance Lead',
            'slug' => 'quality-assurance-lead',
            'badge_color' => 'teal',
            'is_system' => false,
        ]);
    }

    public function test_admin_can_update_role(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());
        $customRole = Role::create([
            'name' => 'Old Role Name',
            'slug' => 'old-role-name',
            'badge_color' => 'blue',
            'is_system' => false,
        ]);

        $response = $this->actingAs($admin)->put("/admin/roles/{$customRole->id}", [
            'name' => 'New Role Name',
            'description' => 'Updated description for test.',
            'badge_color' => 'rose',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $customRole->refresh();

        $this->assertSame('New Role Name', $customRole->name);
        $this->assertSame('rose', $customRole->badge_color);
    }

    public function test_admin_cannot_delete_system_role(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());
        $systemRole = Role::where('slug', 'it-admin')->first();

        $response = $this->actingAs($admin)->delete("/admin/roles/{$systemRole->id}");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('roles', ['id' => $systemRole->id]);
    }

    public function test_admin_cannot_delete_role_with_assigned_users(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());
        $customRole = Role::create([
            'name' => 'Lead Picker',
            'slug' => 'lead-picker',
            'badge_color' => 'amber',
            'is_system' => false,
        ]);

        $user = User::factory()->create();
        $user->roles()->attach($customRole);

        $response = $this->actingAs($admin)->delete("/admin/roles/{$customRole->id}");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('roles', ['id' => $customRole->id]);
    }

    public function test_admin_can_delete_unassigned_custom_role(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());
        $customRole = Role::create([
            'name' => 'Temporary Seasonal Role',
            'slug' => 'temporary-seasonal-role',
            'badge_color' => 'sky',
            'is_system' => false,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/roles/{$customRole->id}");

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    }

    public function test_admin_can_update_own_credentials_profile(): void
    {
        $admin = User::factory()->create([
            'name' => 'Original Admin',
            'email' => 'admin.original@globaltronics.net',
        ]);
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());

        $response = $this->actingAs($admin)->put('/admin/settings/credentials/profile', [
            'name' => 'Updated Admin Profile',
            'email' => 'admin.updated@globaltronics.net',
        ]);

        $response->assertRedirect(route('admin.settings.credentials'));
        $admin->refresh();

        $this->assertSame('Updated Admin Profile', $admin->name);
        $this->assertSame('admin.updated@globaltronics.net', $admin->email);
    }

    public function test_admin_can_update_password_with_valid_current_password(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('CurrentPassword123!'),
        ]);
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());

        $response = $this->actingAs($admin)->put('/admin/settings/credentials/password', [
            'current_password' => 'CurrentPassword123!',
            'password' => 'NewSecurePassword456!',
            'password_confirmation' => 'NewSecurePassword456!',
        ]);

        $response->assertRedirect(route('admin.settings.credentials'));
        $admin->refresh();

        $this->assertTrue(Hash::check('NewSecurePassword456!', $admin->password));
    }

    public function test_authenticated_admin_can_view_admin_credentials_console(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->first());

        $response = $this->actingAs($admin)->get(route('admin.settings.credentials'));

        $response->assertOk();
        $response->assertSee('Recent User Accounts');
        $response->assertSee('Role Directory');
        $response->assertSee('Security Policy Notice');
        $response->assertDontSee('Sales Executive');
    }

    public function test_warehouse_admin_cannot_access_admin_credentials(): void
    {
        $warehouseAdmin = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $warehouseAdmin->roles()->attach($role);

        $response = $this->actingAs($warehouseAdmin)->get(route('admin.settings.credentials'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('error');
    }

    public function test_warehouse_admin_cannot_create_user_or_manage_roles(): void
    {
        $warehouseAdmin = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $warehouseAdmin->roles()->attach($role);

        $userResponse = $this->actingAs($warehouseAdmin)->post('/admin/users', [
            'name' => 'Intruder User',
            'email' => 'intruder@globaltronics.net',
            'password' => 'Password123!',
        ]);

        $userResponse->assertRedirect(route('admin.dashboard'));
        $userResponse->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('users', ['email' => 'intruder@globaltronics.net']);

        $roleResponse = $this->actingAs($warehouseAdmin)->post('/admin/roles', [
            'name' => 'Unauthorized Role',
            'description' => 'Should fail',
        ]);

        $roleResponse->assertRedirect(route('admin.dashboard'));
        $roleResponse->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('roles', ['name' => 'Unauthorized Role']);
    }

    public function test_warehouse_admin_dashboard_hides_admin_credentials_actions(): void
    {
        $warehouseAdmin = User::factory()->create();
        $warehouseAdmin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $response = $this->actingAs($warehouseAdmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Operations Overview');
        $response->assertDontSee('Manage accounts');
        $response->assertDontSee(route('admin.settings.credentials'));
    }

    public function test_inventory_module_is_available_and_sales_modules_are_unavailable(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->first());

        $this->actingAs($user)->get(route('admin.inventory.index'))->assertOk();
        $this->actingAs($user)->get('/admin/sales-orders')->assertNotFound();
        $this->actingAs($user)->get('/admin/sales-orders/reports')->assertNotFound();

        $this->assertTrue(Schema::hasTable('inventory_items'));
        $this->assertFalse(Schema::hasTable('sales_orders'));
        $this->assertFalse(Schema::hasTable('order_logs'));
        $this->assertFalse(Schema::hasColumn('users', 'is_admin'));
        $this->assertTrue(Schema::hasColumn('srf_requisitions', 'item_model'));
        $this->assertFalse(Schema::hasColumn('srf_requisitions', 'inventory_item_id'));
        $this->assertDatabaseMissing('roles', ['slug' => 'sales-executive']);
    }

    public function test_dashboard_renders_for_warehouse_admin(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Operations Overview');
        $response->assertDontSee('5 OPERATIONAL WORKFLOW MODULES', false);
    }

    public function test_dashboard_does_not_render_sop_workflow_engine(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Warehouse Operations &amp; Workflow Engine', false);
        $response->assertDontSee('Installation Project (Process Flow)', false);
    }

    public function test_ariel_moro_technical_role_cannot_access_admin_credentials(): void
    {
        $techUser = User::factory()->create([
            'name' => 'Ariel Moro',
            'email' => 'ariel.moro@globaltronics.net',
        ]);
        $techRole = Role::where('slug', 'technical')->first();
        $techUser->roles()->attach($techRole);

        $this->assertFalse($techUser->canAccessAdminCredentials());
        $this->assertTrue($techUser->isTechnical());

        // Attempting to access Admin Credentials redirects with error
        $response = $this->actingAs($techUser)->get(route('admin.settings.credentials'));
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('error');
    }

    public function test_technical_user_can_submit_installation_srf_requisition(): void
    {
        $techUser = User::factory()->create();
        $techUser->roles()->attach(Role::where('slug', 'technical')->first());

        $response = $this->actingAs($techUser)->post(route('admin.operations.installation.srf'), [
            'project_name' => 'SM Mall of Asia Main Atrium',
            'sso_number' => 'SSO-2026-9021',
            'srf_number' => 'SRF-2026-0042',
            'items' => [[
                'item_model' => 'P2.5 INDOOR LED',
                'item_manufacturer' => 'Globaltronics',
                'item_category' => 'LED DISPLAY',
                'item_description' => 'Indoor display panel',
                'quantity' => 10,
                'uom' => 'PCS',
            ]],
            'prepared_by' => $techUser->name,
            'noted_by' => 'Joshua Labios / Felix Tumambing',
            'pre_approved_by' => 'Teddymar Bajeta',
            'approved_by' => 'Macy Guido Lee',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => 'SRF-2026-0042',
            'item_model' => 'P2.5 INDOOR LED',
            'item_manufacturer' => 'Globaltronics',
            'item_category' => 'LED DISPLAY',
            'item_description' => 'Indoor display panel',
            'quantity' => 10,
            'stock_status' => 'not_tracked',
            'status' => 'pending',
        ]);
    }

    public function test_auto_generated_srf_number_starts_at_2026_0957(): void
    {
        $techUser = User::factory()->create();
        $techUser->roles()->attach(Role::where('slug', 'technical')->first());

        $response = $this->actingAs($techUser)->get(route('admin.dashboard'));
        $response->assertOk();
        $expectedFirstSrf = date('Y').' - 0957';
        $response->assertSee($expectedFirstSrf);

        $submitResponse = $this->actingAs($techUser)->post(route('admin.operations.installation.srf'), [
            'items' => [[
                'item_model' => 'P2.5 INDOOR LED',
                'quantity' => 5,
                'uom' => 'PCS',
            ]],
            'prepared_by' => $techUser->name,
        ]);

        $submitResponse->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => $expectedFirstSrf,
            'item_model' => 'P2.5 INDOOR LED',
            'stock_status' => 'not_tracked',
        ]);
    }

    public function test_technical_user_can_submit_multi_item_srf_requisition(): void
    {
        $techUser = User::factory()->create();
        $techUser->roles()->attach(Role::where('slug', 'technical')->first());

        $srfNumber = '2026 - 0957';
        $response = $this->actingAs($techUser)->post(route('admin.operations.installation.srf'), [
            'srf_number' => $srfNumber,
            'project_name' => 'Robinsons Galleria Cebu',
            'po_number' => 'PO-2026-9901',
            'prepared_by' => $techUser->name,
            'items' => [
                [
                    'item_model' => 'P2.5 INDOOR LED MODULE',
                    'item_manufacturer' => 'Globaltronics',
                    'quantity' => 12,
                    'uom' => 'PCS',
                    'remarks' => 'Batch A modules with testing required',
                ],
                [
                    'item_model' => 'NOVASTAR VX4S CONTROLLER',
                    'item_manufacturer' => 'NovaStar',
                    'quantity' => 2,
                    'uom' => 'UNITS',
                    'remarks' => 'Pre-configured controllers',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => $srfNumber,
            'item_model' => 'P2.5 INDOOR LED MODULE',
            'item_manufacturer' => 'Globaltronics',
            'quantity' => 12,
            'stock_status' => 'not_tracked',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => $srfNumber,
            'item_model' => 'NOVASTAR VX4S CONTROLLER',
            'quantity' => 2,
            'uom' => 'UNITS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
        ]);

        $adminUser = User::factory()->create();
        $adminUser->roles()->attach(Role::where('slug', 'warehouse-admin')->first());
        $srf = SrfRequisition::where('srf_number', $srfNumber)->firstOrFail();

        $verifyResponse = $this->actingAs($adminUser)->post(route('admin.operations.installation.srf.verify', $srf), [
            'verification_notes' => 'All items picked and staged.',
        ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertSame(2, SrfRequisition::where('srf_number', $srfNumber)->where('status', 'approved')->count());
    }

    public function test_technical_user_dashboard_does_not_see_warehouse_operations_workflow_engine_or_department_management(): void
    {
        $tech = User::factory()->create();
        $tech->roles()->attach(Role::where('slug', 'technical')->first());

        $response = $this->actingAs($tech)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Operations Overview');
        $response->assertSee('Requisitions');
        $response->assertSee('Create Stock Requisition Form');
        $response->assertSee('id="installationSrfModal"', false);
        $response->assertDontSee('Warehouse Operations &amp; Workflow Engine', false);
        $response->assertDontSee('Sales Order');
        $response->assertSee('Inventory List');
    }

    public function test_technical_user_can_submit_srf_and_it_shows_in_dashboard_summary(): void
    {
        $tech = User::factory()->create();
        $tech->roles()->attach(Role::where('slug', 'technical')->first());

        $response = $this->actingAs($tech)->post(route('admin.operations.installation.srf'), [
            'client' => 'SM Megamall Curved LED',
            'po_number' => 'PO-2026-9912',
            'date_needed' => '2026-10-15',
            'requisition_date' => '2026-10-06',
            'srf_number' => 'SRF-2026-9001',
            'sso_number' => 'SSO-2026-1001',
            'items' => [[
                'item_model' => 'P10 OUTDOOR SCREEN',
                'item_category' => 'OUTDOOR LED PANELS',
                'quantity' => 10,
                'uom' => 'PCS',
                'remarks' => 'Urgent outdoor display staging with harnesses',
            ]],
            'prepared_by' => $tech->name,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => 'SRF-2026-9001',
            'sso_number' => 'SSO-2026-1001',
            'client' => 'SM Megamall Curved LED',
            'po_number' => 'PO-2026-9912',
            'item_model' => 'P10 OUTDOOR SCREEN',
            'uom' => 'PCS',
            'remarks' => 'Urgent outdoor display staging with harnesses',
            'status' => 'pending',
            'stock_status' => 'not_tracked',
        ]);

        $dashResponse = $this->actingAs($tech)->get('/admin/dashboard');
        $dashResponse->assertOk();
        $dashResponse->assertSee('SRF-2026-9001');
        $dashResponse->assertSee('SM Megamall Curved LED');
        $dashResponse->assertSee('P10 OUTDOOR SCREEN');
        $dashResponse->assertSee('Pending');

        $warehouseAdmin = User::factory()->create();
        $warehouseAdmin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());
        $srf = SrfRequisition::where('srf_number', 'SRF-2026-9001')->firstOrFail();
        $verifyResponse = $this->actingAs($warehouseAdmin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'verification_notes' => 'Approved for staging.',
        ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', ['id' => $srf->id, 'status' => 'approved']);
    }

    public function test_srf_form_accepts_manual_item_details_without_inventory_search(): void
    {
        $tech = User::factory()->create();
        $tech->roles()->attach(Role::where('slug', 'technical')->first());

        $response = $this->actingAs($tech)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Model / item name');
        $response->assertSee('Manufacturer');
        $response->assertSee('item_model');
        $response->assertDontSee('item-search-input');
        $response->assertDontSee('Search inventory');
    }

    public function test_dashboard_summarizes_multi_item_srf_with_live_item_count(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        foreach ([['PANEL-X1', 20, 'PCS'], ['CONTROLLER-Y2', 4, 'UNITS']] as [$model, $quantity, $uom]) {
            SrfRequisition::create([
                'srf_number' => '2026 - 1100',
                'sso_number' => 'SSO-2026-0200',
                'project_name' => 'Ayala Mall Cinema Project',
                'client' => 'Ayala Land',
                'po_number' => 'PO-2026-5501',
                'date_needed' => now()->addDays(3),
                'requisition_date' => now(),
                'item_model' => $model,
                'quantity' => $quantity,
                'uom' => $uom,
                'stock_status' => 'not_tracked',
                'status' => 'pending',
                'prepared_by' => 'Ariel Moro',
                'user_id' => $user->id,
            ]);
        }

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('2026 - 1100');
        $response->assertSee('PANEL-X1');
        $response->assertSee('+1 more');
        $response->assertDontSee('Launch Stock Requisition Form (SRF)');
        $response->assertSee('Approvals');
    }

    public function test_approved_srf_page_lists_verified_requisitions(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        SrfRequisition::create([
            'srf_number' => '2026 - 1200',
            'sso_number' => 'SSO-2026-0300',
            'project_name' => 'BGC Taguig Tower Project',
            'client' => 'BGC Estates',
            'po_number' => 'PO-2026-7788',
            'date_needed' => now()->addDays(2),
            'requisition_date' => now(),
            'item_model' => 'PANEL-APPROVED-100',
            'item_manufacturer' => 'Globaltronics',
            'quantity' => 15,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'approved',
            'prepared_by' => 'Ariel Moro',
            'verified_by_user_id' => $user->id,
            'verified_by_name' => 'Joshua Labios',
            'verified_at' => now(),
            'verification_notes' => 'Tested and staged.',
            'user_id' => $user->id,
            'remarks' => 'Verified and ready for dispatch',
        ]);

        $response = $this->actingAs($user)->get(route('admin.srf.approved'));

        $response->assertOk();
        $response->assertSee('Approved SRF Summary');
        $response->assertSee('2026 - 1200');
        $response->assertSee('SSO-2026-0300');
        $response->assertSee('BGC Estates');
        $response->assertSee('PO-2026-7788');
        $response->assertSee('PANEL-APPROVED-100');
        $response->assertSee('15');
        $response->assertSee('PCS');
        $response->assertSee('Joshua Labios');
        $response->assertSee('Tested and staged.');
    }

    public function test_warehouse_admin_can_approve_srf_without_changing_item_details(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());
        $srf = SrfRequisition::create([
            'srf_number' => '2026 - 1300',
            'sso_number' => 'SSO-2026-1300',
            'project_name' => 'Ayala Malls Project',
            'client' => 'Ayala Land',
            'item_model' => 'PANEL-SNAPSHOT',
            'quantity' => 10,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'decision' => 'approved',
            'verification_notes' => 'Approved for staging.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $srf->refresh();
        $this->assertSame('approved', $srf->status);
        $this->assertSame('PANEL-SNAPSHOT', $srf->item_model);
        $this->assertSame(10, $srf->quantity);
        $this->assertSame('not_tracked', $srf->stock_status);
        $this->assertSame($admin->name, $srf->verified_by_name);
    }

    public function test_warehouse_admin_can_decline_srf_without_inventory_restoration(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());
        $srf = SrfRequisition::create([
            'srf_number' => '2026 - 1301',
            'sso_number' => 'SSO-2026-1301',
            'project_name' => 'Megamall Tower Project',
            'client' => 'SM Prime',
            'item_model' => 'PANEL-SNAPSHOT',
            'quantity' => 10,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'decision' => 'declined',
            'verification_notes' => 'Project cancelled by client.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $srf->refresh();
        $this->assertSame('declined', $srf->status);
        $this->assertSame('PANEL-SNAPSHOT', $srf->item_model);
        $this->assertSame('not_tracked', $srf->stock_status);
    }

    public function test_warehouse_admin_can_place_srf_on_hold(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());
        $srf = SrfRequisition::create([
            'srf_number' => '2026 - 1302',
            'sso_number' => 'SSO-2026-1302',
            'project_name' => 'Robinsons Galleria Project',
            'client' => 'Robinsons Land',
            'item_model' => 'PANEL-SNAPSHOT',
            'quantity' => 5,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'decision' => 'on_hold',
            'verification_notes' => 'Awaiting confirmation on specs.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertSame('on_hold', $srf->fresh()->status);
    }

    public function test_user_can_view_and_download_srf_pdf(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        SrfRequisition::create([
            'srf_number' => '2026 - 1500',
            'sso_number' => 'SSO-2026-0600',
            'project_name' => 'Ayala Malls Manila Bay Project',
            'client' => 'Ayala Land Corporation',
            'po_number' => 'PO-2026-8899',
            'date_needed' => now()->addDays(5),
            'requisition_date' => now(),
            'item_model' => 'PANEL-PDF-VERIFY',
            'item_manufacturer' => 'Globaltronics',
            'item_category' => 'LED DISPLAY',
            'item_description' => 'Outdoor LED panel',
            'quantity' => 12,
            'uom' => 'PCS',
            'stock_status' => 'not_tracked',
            'status' => 'approved',
            'department' => 'TECHNICAL',
            'prepared_by' => 'Ariel Moro',
            'noted_by' => 'Joshua Labios / Felix Tumambing',
            'pre_approved_by' => 'Teddy Mar Bajeta',
            'approved_by' => 'Macy Guido Lee',
            'verified_by_user_id' => $user->id,
            'verified_by_name' => 'Felix Tumambing',
            'verified_at' => now(),
            'verification_notes' => 'Allocated and staged.',
            'user_id' => $user->id,
            'remarks' => 'Urgent mall display installation',
        ]);

        $response = $this->actingAs($user)->get(route('admin.srf.pdf', urlencode('2026 - 1500')));

        $response->assertOk();
        $response->assertSee('STOCK REQUISITION FORM');
        $response->assertSee('2026 - 1500');
        $response->assertSee('SSO-2026-0600');
        $response->assertSee('Ayala Land Corporation');
        $response->assertSee('PO-2026-8899');
        $response->assertSee('PANEL-PDF-VERIFY');
        $response->assertSee('Globaltronics');
        $response->assertSee('Outdoor LED panel');
        $response->assertSee('12');
        $response->assertSee('Ariel Moro');
    }

    public function test_srf_submission_and_pdf_with_sales_department(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $response = $this->actingAs($user)->post(route('admin.operations.installation.srf'), [
            'srf_number' => '2026 - 2000',
            'sso_number' => 'SSO-2026-2000',
            'project_name' => 'Sales Demo Project',
            'client' => 'SM Prime Holdings',
            'department' => 'SALES',
            'prepared_by' => 'Anne Libo-on',
            'noted_by' => 'Bernadette Federez',
            'pre_approved_by' => 'Teddy Bajeta',
            'approved_by' => 'Macy Guido Lee',
            'items' => [[
                'item_model' => 'DISPLAY-DEMO-1',
                'quantity' => 5,
                'uom' => 'PCS',
                'remarks' => 'Sales display units',
            ]],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => '2026 - 2000',
            'department' => 'SALES',
            'item_model' => 'DISPLAY-DEMO-1',
            'stock_status' => 'not_tracked',
        ]);

        $pdfResponse = $this->actingAs($user)->get(route('admin.srf.pdf', urlencode('2026 - 2000')));
        $pdfResponse->assertOk();
        $pdfResponse->assertSee('SALES');
        $pdfResponse->assertSee('Anne Libo-on');
        $pdfResponse->assertSee('DISPLAY-DEMO-1');
    }

    public function test_srf_submission_and_pdf_with_it_department(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $response = $this->actingAs($user)->post(route('admin.operations.installation.srf'), [
            'srf_number' => '2026 - 3000',
            'sso_number' => 'SSO-2026-3000',
            'project_name' => 'IT Infrastructure Upgrade',
            'client' => 'Internal IT Office',
            'department' => 'IT',
            'prepared_by' => 'Stephanie Refe',
            'noted_by' => 'Paz Liquigan',
            'pre_approved_by' => 'Teddy Bajeta',
            'approved_by' => 'Macy Guido Lee',
            'items' => [[
                'item_model' => 'NETWORK-TEST-UNIT',
                'quantity' => 2,
                'uom' => 'PCS',
                'remarks' => 'R&D testing units',
            ]],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => '2026 - 3000',
            'department' => 'IT',
            'item_model' => 'NETWORK-TEST-UNIT',
            'prepared_by' => 'Stephanie Refe',
        ]);

        $pdfResponse = $this->actingAs($user)->get(route('admin.srf.pdf', urlencode('2026 - 3000')));
        $pdfResponse->assertOk();
        $pdfResponse->assertSee('IT');
        $pdfResponse->assertSee('NETWORK-TEST-UNIT');
        $pdfResponse->assertSee('Stephanie Refe');
    }
}
