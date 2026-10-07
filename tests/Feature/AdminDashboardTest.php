<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\SrfRequisition;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        $role = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Operations Console');
        $response->assertSee('Total Accounts');
    }

    public function test_admin_can_create_user_with_roles(): void
    {
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $staffRole = Role::where('slug', 'warehouse-staff')->first();
        $admin->roles()->attach($itRole);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Carlos Mendoza',
            'email' => 'carlos.mendoza@globaltronics.net',
            'password' => 'SecurePass123!@#',
            'roles' => [$staffRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'carlos.mendoza@globaltronics.net',
            'name' => 'Carlos Mendoza',
        ]);

        $createdUser = User::where('email', 'carlos.mendoza@globaltronics.net')->first();
        $this->assertTrue($createdUser->roles->contains($staffRole->id));
        $this->assertTrue(Hash::check('SecurePass123!@#', $createdUser->password));
    }

    public function test_admin_can_update_user_credentials_and_roles(): void
    {
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
        $this->assertTrue(Hash::check('NewPassword999!', $targetUser->password));
        $this->assertTrue($targetUser->roles->contains($warehouseAdminRole->id));
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
        $role = Role::where('slug', 'it-admin')->first();
        $admin->roles()->attach($role);

        $response = $this->actingAs($admin)->get(route('admin.settings.credentials'));

        $response->assertStatus(200);
        $response->assertSee('Admin Operations Console');
        $response->assertSee('Recent User Accounts');
        $response->assertSee('Role Directory');
        $response->assertSee('Security Policy Notice');
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
        $role = Role::where('slug', 'warehouse-admin')->first();
        $warehouseAdmin->roles()->attach($role);

        $response = $this->actingAs($warehouseAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Warehouse Operations Dashboard');
        $response->assertSee('Facility Operations Command');
        $response->assertDontSee('Open Admin Credentials');
        $response->assertDontSee('Manage in Credentials →');
    }

    public function test_sidebar_contains_inventory_dropdown_with_categories(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="inventoryDropdownBtn"', false);
        $response->assertSee('id="inventorySubmenu"', false);
        $response->assertSee('Centralized LED');
        $response->assertSee('EOL Philips Units');
        $response->assertSee('All Categories');
    }

    public function test_dashboard_renders_for_warehouse_admin(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Warehouse Operations');
        $response->assertDontSee('5 OPERATIONAL WORKFLOW MODULES', false);
    }

    public function test_admin_can_dispatch_order(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create([
            'location' => 'MARIKINA',
            'quantity' => 10,
            'sqm' => 5.0,
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($user)->post('/admin/operations/dispatch', [
            'inventory_item_id' => $item->id,
            'quantity' => 4,
            'order_number' => 'SO-2026-0099',
            'recipient' => 'SM Mall of Asia',
            'vehicle_plate' => 'ABC-1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        $item->refresh();
        $this->assertEquals(6, $item->quantity);
        $this->assertEquals(3.0, (float) $item->sqm);
    }

    public function test_admin_can_perform_cycle_count(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create([
            'location' => 'MARIKINA',
            'quantity' => 20,
            'sqm' => 5.0,
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($user)->post('/admin/operations/cycle-count', [
            'inventory_item_id' => $item->id,
            'counted_quantity' => 18,
            'auditor_notes' => 'Found 2 damaged boxes in aisle 3',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        $item->refresh();
        $this->assertEquals(18, $item->quantity);
        $this->assertEquals(4.5, (float) $item->sqm);
    }

    public function test_admin_can_record_defective_return(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create([
            'model' => 'TEST MODEL RMA',
            'location' => 'MARIKINA',
            'quantity' => 5,
            'sqm' => 1.0,
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($user)->post('/admin/operations/return-item', [
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'disposition' => 'quarantine_defective',
            'serial_numbers' => 'SN-DEF-001, SN-DEF-002',
            'reason' => 'Dead pixels on upper quadrant',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        // Verify quarantined defective item was created
        $this->assertDatabaseHas('inventory_items', [
            'location' => 'DEFECTIVE',
            'status' => 'maintenance',
            'quantity' => 2,
        ]);
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
        $techUser = User::factory()->create([
            'name' => 'Ariel Moro',
            'email' => 'ariel.moro@globaltronics.net',
        ]);
        $techRole = Role::where('slug', 'technical')->first();
        $techUser->roles()->attach($techRole);

        $item = InventoryItem::factory()->create([
            'model' => 'P2.5 INDOOR LED',
            'quantity' => 20,
            'location' => 'MARIKINA',
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($techUser)->post(route('admin.operations.installation.srf'), [
            'project_name' => 'SM Mall of Asia Main Atrium',
            'sso_number' => 'SSO-2026-9021',
            'srf_number' => 'SRF-2026-0042',
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'prepared_by' => 'Ariel Moro',
            'noted_by' => 'Joshua Labios / Felix Tumambing',
            'pre_approved_by' => 'Teddymar Bajeta',
            'approved_by' => 'Macy Guido Lee',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        $item->refresh();
        $this->assertEquals(10, $item->quantity);

        // Verify reserved project item was created
        $this->assertDatabaseHas('inventory_items', [
            'po_number' => 'SRF-2026-0042',
            'location' => 'STAGE-BAY-01',
            'status' => 'reserved',
            'quantity' => 10,
        ]);
    }

    public function test_auto_generated_srf_number_starts_at_2026_0957(): void
    {
        $techUser = User::factory()->create();
        $techRole = Role::where('slug', 'technical')->first();
        $techUser->roles()->attach($techRole);

        // When visiting dashboard with 0 requisitions, auto-generated SRF # starts at 2026 - 0957
        $response = $this->actingAs($techUser)->get(route('admin.dashboard'));
        $response->assertOk();
        $expectedFirstSrf = date('Y').' - 0957';
        $response->assertSee($expectedFirstSrf);

        $item = InventoryItem::factory()->create([
            'model' => 'P2.5 INDOOR LED',
            'quantity' => 15,
            'location' => 'MARIKINA',
            'status' => 'in_stock',
        ]);

        // Submit without explicit srf_number to use auto-generated number
        $submitResponse = $this->actingAs($techUser)->post(route('admin.operations.installation.srf'), [
            'client' => 'SM Megamall Curved LED',
            'po_number' => 'PO-2026-8812',
            'date_needed' => date('Y-m-d', strtotime('+3 days')),
            'requisition_date' => date('Y-m-d'),
            'inventory_item_id' => $item->id,
            'quantity' => 5,
            'uom' => 'PCS',
            'remarks' => 'Urgent staging for event',
            'prepared_by' => 'Ariel Moro',
        ]);

        $submitResponse->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => $expectedFirstSrf,
            'client' => 'SM Megamall Curved LED',
            'po_number' => 'PO-2026-8812',
            'uom' => 'PCS',
        ]);
    }

    public function test_technical_user_can_submit_multi_item_srf_requisition(): void
    {
        $techUser = User::factory()->create();
        $techRole = Role::where('slug', 'technical')->first();
        $techUser->roles()->attach($techRole);

        $itemA = InventoryItem::factory()->create([
            'model' => 'P2.5 INDOOR LED MODULE',
            'quantity' => 50,
            'location' => 'STAGE-BAY-01',
            'status' => 'in_stock',
        ]);

        $itemB = InventoryItem::factory()->create([
            'model' => 'NOVASTAR VX4S CONTROLLER',
            'quantity' => 10,
            'location' => 'CONTROLLER-BAY',
            'status' => 'in_stock',
        ]);

        $srfNum = '2026 - 0957';
        $response = $this->actingAs($techUser)->post(route('admin.operations.installation.srf'), [
            'client' => 'Robinsons Galleria Cebu',
            'po_number' => 'PO-2026-9901',
            'date_needed' => date('Y-m-d', strtotime('+5 days')),
            'requisition_date' => date('Y-m-d'),
            'srf_number' => $srfNum,
            'prepared_by' => 'Ariel Moro',
            'items' => [
                [
                    'inventory_item_id' => $itemA->id,
                    'quantity' => 12,
                    'uom' => 'PCS',
                    'remarks' => 'Batch A modules with testing required',
                ],
                [
                    'inventory_item_id' => $itemB->id,
                    'quantity' => 2,
                    'uom' => 'UNITS',
                    'remarks' => 'Pre-configured controllers',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        // Both items exist under the same SRF number in srf_requisitions
        $srfRecords = SrfRequisition::where('srf_number', $srfNum)->get();
        $this->assertCount(2, $srfRecords);

        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => $srfNum,
            'inventory_item_id' => $itemA->id,
            'quantity' => 12,
            'uom' => 'PCS',
            'remarks' => 'Batch A modules with testing required',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => $srfNum,
            'inventory_item_id' => $itemB->id,
            'quantity' => 2,
            'uom' => 'UNITS',
            'remarks' => 'Pre-configured controllers',
            'status' => 'pending',
        ]);

        // Verify warehouse admin verifying one record verifies all items in that SRF
        $adminUser = User::factory()->create();
        $adminRole = Role::where('slug', 'warehouse-admin')->first();
        $adminUser->roles()->attach($adminRole);

        $verifyResponse = $this->actingAs($adminUser)->post(route('admin.operations.installation.srf.verify', $srfRecords->first()), [
            'verification_notes' => 'All items picked and staged at STAGE-BAY-01.',
        ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertEquals(2, SrfRequisition::where('srf_number', $srfNum)->where('status', 'approved')->count());
    }

    public function test_installation_sto_bay_transfer(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create([
            'model' => 'UMINI P1.2',
            'quantity' => 15,
            'location' => 'MARIKINA MAIN BAY A',
        ]);

        $response = $this->actingAs($user)->post(route('admin.operations.installation.sto'), [
            'project_name' => 'Clark Airport Video Wall',
            'sto_number' => 'STO-2026-0128',
            'inventory_item_id' => $item->id,
            'quantity' => 5,
            'source_bay' => 'MARIKINA MAIN BAY A',
            'destination_bay' => 'TECH QA TESTING BENCH (MARIKINA)',
            'prepared_by' => 'Arbie Hipolito',
            'checked_by' => 'Ryan Lomboy',
            'released_by' => 'Ryan Matuguina',
            'received_by' => 'WH Staff',
            'approved_by' => 'Joshua Labios',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        $item->refresh();
        $this->assertEquals('TECH QA TESTING BENCH (MARIKINA)', $item->location);
    }

    public function test_installation_dr_dispatch(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create([
            'model' => '55BDL4050D DISPLAY',
            'quantity' => 8,
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($user)->post(route('admin.operations.installation.dr'), [
            'project_name' => 'Robinson Galleria Project',
            'dr_number' => 'DR-2026-4402',
            'inventory_item_id' => $item->id,
            'quantity' => 4,
            'recipient_destination' => 'Robinsons Galleria Level 3',
            'vehicle_plate' => 'ABC-9921',
            'prepared_by' => 'Arbie Hipolito',
            'approved_by' => 'Joshua Labios',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        $item->refresh();
        $this->assertEquals(4, $item->quantity);
    }

    public function test_installation_return_and_eol_scrap(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create([
            'category' => 'CENTRALIZED LED INVENTORY',
            'model' => 'P1.9 DEFECTIVE MODULE',
            'quantity' => 10,
        ]);

        $response = $this->actingAs($user)->post(route('admin.operations.installation.return'), [
            'project_name' => 'City of Dreams Project',
            'return_slip_number' => 'RS-2026-8819',
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'disposition_flow' => 'eol_scrap',
            'findings' => 'Cracked surface mask and dead driver ICs',
            'returned_by' => 'Technical Staff',
            'received_by' => 'Francis Perez',
            'approved_by' => 'Jerico Rivera',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        // Verify item was created in EOL category
        $this->assertDatabaseHas('inventory_items', [
            'category' => 'EOL PHILIPS UNITS',
            'location' => 'EOL-SCRAP',
            'status' => 'out_of_stock',
            'quantity' => 2,
        ]);
    }

    public function test_it_admin_can_create_sales_account_with_sales_role(): void
    {
        $admin = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $salesRole = Role::where('slug', 'sales-executive')->first();
        $admin->roles()->attach($itRole);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Sales Rep',
            'email' => 'sales.rep@globaltronics.net',
            'password' => 'SecurePass123!@#',
            'roles' => [$salesRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'sales.rep@globaltronics.net',
            'name' => 'New Sales Rep',
        ]);

        $createdUser = User::where('email', 'sales.rep@globaltronics.net')->first();
        $this->assertTrue($createdUser->hasRole('sales-executive'));
    }

    public function test_technical_user_dashboard_does_not_see_warehouse_operations_workflow_engine_or_department_management(): void
    {
        $tech = User::factory()->create([
            'name' => 'Ariel Moro',
            'email' => 'ariel.moro@globaltronics.net',
        ]);
        $techRole = Role::where('slug', 'technical')->first();
        $tech->roles()->attach($techRole);

        $response = $this->actingAs($tech)->get('/admin/dashboard');

        $response->assertStatus(200);

        // Should see Technical features
        $response->assertSee('Technical Operations &amp; SRF Requisitions', false);
        $response->assertSee('Stock Requisition Form (SRF)');
        $response->assertSee('Launch Stock Requisition Form (SRF)');
        $response->assertSee('Stock Requisition Form (SRF) Summary');
        $response->assertSee('Assignators');

        // Should NOT see Warehouse Operations & Workflow Engine
        $response->assertDontSee('Warehouse Operations &amp; Workflow Engine', false);
        $response->assertDontSee('5 OPERATIONAL WORKFLOW MODULES');

        // Should NOT see Department Account Management
        $response->assertDontSee('Department Account Management');
        $response->assertDontSee('IT ADMINISTRATION CONSOLE');

        // Should NOT see Authorized System Accounts & Telemetry
        $response->assertDontSee('Authorized System Accounts');
        $response->assertDontSee('Facility Telemetry');
        $response->assertDontSee('Storage &amp; Pallet Bays', false);
    }

    public function test_technical_user_can_submit_srf_and_it_shows_in_dashboard_summary(): void
    {
        $tech = User::factory()->create([
            'name' => 'Ariel Moro',
            'email' => 'ariel.moro@globaltronics.net',
        ]);
        $techRole = Role::where('slug', 'technical')->first();
        $tech->roles()->attach($techRole);

        $item = InventoryItem::factory()->create([
            'category' => 'OUTDOOR LED PANELS',
            'model' => 'P10 OUTDOOR SCREEN',
            'quantity' => 50,
        ]);

        $response = $this->actingAs($tech)->post(route('admin.operations.installation.srf'), [
            'client' => 'SM Megamall Curved LED',
            'po_number' => 'PO-2026-9912',
            'date_needed' => '2026-10-15',
            'requisition_date' => '2026-10-06',
            'srf_number' => 'SRF-2026-9001',
            'sso_number' => 'SSO-2026-1001',
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'uom' => 'PCS',
            'remarks' => 'Urgent outdoor display staging with harnesses',
            'prepared_by' => 'Ariel Moro',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => 'SRF-2026-9001',
            'sso_number' => 'SSO-2026-1001',
            'client' => 'SM Megamall Curved LED',
            'po_number' => 'PO-2026-9912',
            'uom' => 'PCS',
            'remarks' => 'Urgent outdoor display staging with harnesses',
            'status' => 'pending',
            'stock_status' => 'available_reserved',
        ]);

        // Visit dashboard and see the requisition in the summary table
        $dashResponse = $this->actingAs($tech)->get('/admin/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('SRF-2026-9001');
        $dashResponse->assertSee('SM Megamall Curved LED');
        $dashResponse->assertSee('PO-2026-9912');
        $dashResponse->assertSee('10 PCS');
        $dashResponse->assertSee('FOR APPROVAL');

        // Warehouse Admin verifies it
        $whAdmin = User::factory()->create();
        $whAdminRole = Role::where('slug', 'warehouse-admin')->first();
        $whAdmin->roles()->attach($whAdminRole);

        $srf = SrfRequisition::where('srf_number', 'SRF-2026-9001')->first();

        $verifyResponse = $this->actingAs($whAdmin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'verification_notes' => 'Allocated from Bay 3. Ready for staging.',
        ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('srf_requisitions', [
            'id' => $srf->id,
            'status' => 'approved',
        ]);
    }

    public function test_item_search_inputs_and_containers_are_rendered_in_all_modals(): void
    {
        $tech = User::factory()->create();
        $techRole = Role::where('slug', 'technical')->first();
        $tech->roles()->attach($techRole);

        $response = $this->actingAs($tech)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('item-search-container');
        $response->assertSee('item-search-input');
        $response->assertSee('item-search-results');
        $response->assertSee('Type model, brand, bay location to search...');
    }

    public function test_srf_summary_table_combines_multiple_items_and_removes_header_launch_button(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $itemA = InventoryItem::factory()->create(['model' => 'PANEL-X1', 'quantity' => 50]);
        $itemB = InventoryItem::factory()->create(['model' => 'CONTROLLER-Y2', 'quantity' => 20]);

        SrfRequisition::create([
            'srf_number' => '2026 - 1100',
            'sso_number' => 'SSO-2026-0200',
            'project_name' => 'Ayala Mall Cinema Project',
            'client' => 'Ayala Land',
            'po_number' => 'PO-2026-5501',
            'date_needed' => now()->addDays(3),
            'requisition_date' => now(),
            'inventory_item_id' => $itemA->id,
            'quantity' => 20,
            'uom' => 'PCS',
            'stock_status' => 'available_reserved',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $user->id,
            'remarks' => 'Urgent cinema batch A',
        ]);

        SrfRequisition::create([
            'srf_number' => '2026 - 1100',
            'sso_number' => 'SSO-2026-0200',
            'project_name' => 'Ayala Mall Cinema Project',
            'client' => 'Ayala Land',
            'po_number' => 'PO-2026-5501',
            'date_needed' => now()->addDays(3),
            'requisition_date' => now(),
            'inventory_item_id' => $itemB->id,
            'quantity' => 4,
            'uom' => 'UNITS',
            'stock_status' => 'available_reserved',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $user->id,
            'remarks' => 'Processor hardware kit',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        // Both items combined in the same row
        $response->assertSee('2026 - 1100');
        $response->assertSee('2 items combined');
        $response->assertSee('PANEL-X1');
        $response->assertSee('20');
        $response->assertSee('PCS');
        $response->assertSee('CONTROLLER-Y2');
        $response->assertSee('4');
        $response->assertSee('UNITS');
        $response->assertSee('Urgent cinema batch A');
        $response->assertSee('Processor hardware kit');

        // Header button "+ Launch Stock Requisition Form (SRF)" removed
        $response->assertDontSee('Launch Stock Requisition Form (SRF)');

        // Sidebar has Approved SRF category
        $response->assertSee('Approved SRF');
    }

    public function test_approved_srf_page_lists_verified_requisitions(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($role);

        $item = InventoryItem::factory()->create(['model' => 'PANEL-APPROVED-100', 'quantity' => 30]);

        SrfRequisition::create([
            'srf_number' => '2026 - 1200',
            'sso_number' => 'SSO-2026-0300',
            'project_name' => 'BGC Taguig Tower Project',
            'client' => 'BGC Estates',
            'po_number' => 'PO-2026-7788',
            'date_needed' => now()->addDays(2),
            'requisition_date' => now(),
            'inventory_item_id' => $item->id,
            'quantity' => 15,
            'uom' => 'PCS',
            'stock_status' => 'available_reserved',
            'status' => 'completed',
            'prepared_by' => 'Ariel Moro',
            'verified_by_user_id' => $user->id,
            'verified_by_name' => 'Joshua Labios',
            'verified_at' => now(),
            'verification_notes' => 'Tested and staged at STAGE-BAY-01.',
            'user_id' => $user->id,
            'remarks' => 'Verified and ready for dispatch',
        ]);

        $response = $this->actingAs($user)->get(route('admin.srf.approved'));

        $response->assertStatus(200);
        $response->assertSee('Approved SRF Summary');
        $response->assertSee('2026 - 1200');
        $response->assertSee('SSO-2026-0300');
        $response->assertSee('BGC Estates');
        $response->assertSee('PO-2026-7788');
        $response->assertSee('PANEL-APPROVED-100');
        $response->assertSee('15');
        $response->assertSee('PCS');
        $response->assertSee('Joshua Labios');
        $response->assertSee('Tested and staged at STAGE-BAY-01.');
    }

    public function test_warehouse_admin_can_approve_srf(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $item = InventoryItem::factory()->create(['model' => 'PANEL-INVENTORY-DEDUCT', 'quantity' => 50]);

        $srf = SrfRequisition::create([
            'srf_number' => '2026 - 1300',
            'sso_number' => 'SSO-2026-0400',
            'project_name' => 'Ayala Malls Project',
            'client' => 'Ayala Land',
            'po_number' => 'PO-2026-9900',
            'date_needed' => now()->addDays(3),
            'requisition_date' => now(),
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'uom' => 'PCS',
            'stock_status' => 'insufficient_pr_hold',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'decision' => 'approved',
            'verification_notes' => 'Stock approved and released.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $item->refresh();
        $srf->refresh();

        $this->assertSame(50, $item->quantity);
        $this->assertSame('approved', $srf->status);
        $this->assertSame($admin->name, $srf->verified_by_name);
    }

    public function test_warehouse_admin_can_decline_srf(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $item = InventoryItem::factory()->create(['model' => 'PANEL-INVENTORY-RESTORE', 'quantity' => 30]);

        $srf = SrfRequisition::create([
            'srf_number' => '2026 - 1301',
            'sso_number' => 'SSO-2026-0401',
            'project_name' => 'Megamall Tower Project',
            'client' => 'SM Prime',
            'po_number' => 'PO-2026-9901',
            'date_needed' => now()->addDays(3),
            'requisition_date' => now(),
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'uom' => 'PCS',
            'stock_status' => 'available_reserved',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'decision' => 'declined',
            'verification_notes' => 'Project cancelled by client.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $item->refresh();
        $srf->refresh();

        $this->assertSame(30, $item->quantity);
        $this->assertSame('declined', $srf->status);
    }

    public function test_warehouse_admin_can_place_srf_on_hold(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $item = InventoryItem::factory()->create(['model' => 'PANEL-INVENTORY-HOLD', 'quantity' => 20]);

        $srf = SrfRequisition::create([
            'srf_number' => '2026 - 1302',
            'sso_number' => 'SSO-2026-0402',
            'project_name' => 'Robinsons Galleria Project',
            'client' => 'Robinsons Land',
            'po_number' => 'PO-2026-9902',
            'date_needed' => now()->addDays(3),
            'requisition_date' => now(),
            'inventory_item_id' => $item->id,
            'quantity' => 5,
            'uom' => 'PCS',
            'stock_status' => 'available_reserved',
            'status' => 'pending',
            'prepared_by' => 'Ariel Moro',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.operations.installation.srf.verify', $srf), [
            'decision' => 'on_hold',
            'verification_notes' => 'Awaiting confirmation on specs.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $srf->refresh();

        $this->assertSame('on_hold', $srf->status);
    }

    public function test_user_can_view_and_download_srf_pdf(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $item = InventoryItem::factory()->create(['model' => 'PANEL-PDF-VERIFY', 'quantity' => 25]);

        SrfRequisition::create([
            'srf_number' => '2026 - 1500',
            'sso_number' => 'SSO-2026-0600',
            'project_name' => 'Ayala Malls Manila Bay Project',
            'client' => 'Ayala Land Corporation',
            'po_number' => 'PO-2026-8899',
            'date_needed' => now()->addDays(5),
            'requisition_date' => now(),
            'inventory_item_id' => $item->id,
            'quantity' => 12,
            'uom' => 'PCS',
            'stock_status' => 'available_reserved',
            'status' => 'approved',
            'department' => 'TECHNICAL',
            'prepared_by' => 'Ariel Moro',
            'noted_by' => 'Joshua Labios / Felix Tumambing',
            'pre_approved_by' => 'Teddy Mar Bajeta',
            'approved_by' => 'Macy Guido Lee',
            'verified_by_user_id' => $user->id,
            'verified_by_name' => 'Felix Tumambing',
            'verified_at' => now(),
            'verification_notes' => 'Allocated and staged at STAGE-BAY-01.',
            'user_id' => $user->id,
            'remarks' => 'Urgent mall display installation',
        ]);

        $response = $this->actingAs($user)->get(route('admin.srf.pdf', urlencode('2026 - 1500')));

        $response->assertStatus(200);
        $response->assertSee('STOCK REQUISITION FORM');
        $response->assertSee('2026 - 1500');
        $response->assertSee('SSO-2026-0600');
        $response->assertSee('Ayala Land Corporation');
        $response->assertSee('PO-2026-8899');
        $response->assertSee('PANEL-PDF-VERIFY');
        $response->assertSee('12');
        $response->assertSee('TECHNICAL');
        $response->assertSee('PREPARED BY');
        $response->assertSee('Ariel Moro');
        $response->assertSee('Project Team Lead');
        $response->assertSee('NOTED BY');
        $response->assertSee('Joshua Labios / Felix Tumambing');
        $response->assertSee('PRE-APPROVED BY');
        $response->assertSee('Teddy Mar Bajeta');
        $response->assertSee('APPROVED BY');
        $response->assertSee('Macy Guido Lee');
        $response->assertSee('Chief of Services Officer');
    }

    public function test_srf_submission_and_pdf_with_sales_department(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $item = InventoryItem::factory()->create(['quantity' => 50]);

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
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity' => 5,
                    'uom' => 'PCS',
                    'remarks' => 'Sales display units',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => '2026 - 2000',
            'department' => 'SALES',
            'prepared_by' => 'Anne Libo-on',
            'noted_by' => 'Bernadette Federez',
            'pre_approved_by' => 'Teddy Bajeta',
            'approved_by' => 'Macy Guido Lee',
        ]);

        $pdfResponse = $this->actingAs($user)->get(route('admin.srf.pdf', urlencode('2026 - 2000')));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('SALES');
        $pdfResponse->assertSee('Sales Admin');
        $pdfResponse->assertSee('Anne Libo-on');
        $pdfResponse->assertSee('Sales Admin Manager');
        $pdfResponse->assertSee('Bernadette Federez');
        $pdfResponse->assertSee('Teddy Bajeta');
        $pdfResponse->assertSee('Macy Guido Lee');
    }

    public function test_srf_submission_and_pdf_with_it_department(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $item = InventoryItem::factory()->create(['quantity' => 10]);

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
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity' => 2,
                    'uom' => 'PCS',
                    'remarks' => 'R&D testing units',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('srf_requisitions', [
            'srf_number' => '2026 - 3000',
            'department' => 'IT',
            'prepared_by' => 'Stephanie Refe',
            'noted_by' => 'Paz Liquigan',
            'pre_approved_by' => 'Teddy Bajeta',
            'approved_by' => 'Macy Guido Lee',
        ]);

        $pdfResponse = $this->actingAs($user)->get(route('admin.srf.pdf', urlencode('2026 - 3000')));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('IT');
        $pdfResponse->assertSee('IT Specialist');
        $pdfResponse->assertSee('Stephanie Refe');
        $pdfResponse->assertSee('Senior IT Research and Development');
        $pdfResponse->assertSee('Paz Liquigan');
        $pdfResponse->assertSee('PMO Technical Officer');
        $pdfResponse->assertSee('Teddy Bajeta');
        $pdfResponse->assertSee('Macy Guido Lee');
    }
}
