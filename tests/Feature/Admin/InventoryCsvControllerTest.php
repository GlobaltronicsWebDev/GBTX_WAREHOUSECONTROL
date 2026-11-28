<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InventoryCsvControllerTest extends TestCase
{
    use RefreshDatabase;

    private const CSV_FIELDS = [
        'tag_number', 'date_received', 'po_sku_number', 'manufacturer', 'model_pixel_pitch',
        'item_description', 'unit_of_measurement', 'location', 'on_hand', 'total_on_hand', 'per_panel_sqm', 'total_available_sqm',
        'available_qty', 'available_sqm', 'reservation_qty', 'reservation_project_details', 'history_qty',
        'history_project', 'original_qty', 'status_qty', 'status_particular', 'remarks', 'id',
        'warehouse_location_id', 'sub_location', 'additional_histories', 'csv_format',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_export_includes_all_active_items_and_protects_spreadsheet_formulas(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina']);
        $item = InventoryItem::create([
            'item_description' => '=dangerous formula',
            'unit_of_measurement' => 'BOX',
            'warehouse_location_id' => $warehouse->id,
            'sub_location' => '1st Bldg',
            'available_qty' => 12,
        ]);
        $item->histories()->createMany([
            ['quantity' => 4, 'project' => 'Client Alpha'],
            ['quantity' => 8, 'project' => 'Client Beta'],
        ]);
        InventoryItem::create(['item_description' => 'Deleted item'])->delete();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.inventory.export'));

        $response->assertOk()->assertDownload('inventory.csv');
        $rows = $this->readCsv($response->streamedContent());
        $this->assertCount(3, $rows);
        $this->assertSame([
            'TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'MANUFACTURER', 'MODEL / PIXEL PITCH', 'ITEM DESCRIPTION',
            'UNIT OF MEASUREMENT', 'LOCATION', 'INVENTORY', '', '', '', 'AVAILABLE QTY', '', 'RESERVATION', '', 'HISTORY', '',
            'ORIGINAL QTY', 'STATUS', '', 'REMARKS', 'ITEM ID', 'WAREHOUSE ID', 'SUB-LOCATION',
            'ADDITIONAL HISTORIES', 'CSV FORMAT',
        ], $rows[0]);
        $this->assertSame([
            '', '', '', '', '', '', '', '', 'ON-HAND', 'TOTAL ON-HAND', 'PER PANEL SQM', 'TOTAL AVAILABLE SQM',
            'QTY', 'SQM', 'QTY', 'PROJECT DETAILS', 'QTY', 'PROJECT', '', 'QTY', 'PARTICULAR', '',
            '', '', '', '', '',
        ], $rows[1]);
        $record = array_combine(self::CSV_FIELDS, $rows[2]);
        $this->assertSame((string) $item->id, $record['id']);
        $this->assertSame("'=dangerous formula", $record['item_description']);
        $this->assertSame((string) $warehouse->id, $record['warehouse_location_id']);
        $this->assertSame('BOX', $record['unit_of_measurement']);
        $this->assertSame('1st Bldg', $record['sub_location']);
        $this->assertSame('4', $record['history_qty']);
        $this->assertSame('Client Alpha', $record['history_project']);
        $this->assertSame([['quantity' => 8, 'project' => 'Client Beta']], json_decode($record['additional_histories'], true));
    }

    public function test_admin_can_update_and_add_items_with_histories_from_exported_csv(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $creator = User::factory()->create();
        $item = InventoryItem::create([
            'item_description' => '=original',
            'warehouse_location_id' => $warehouse->id,
            'sub_location' => '1st Bldg',
            'available_qty' => 5,
            'status_qty' => -6,
            'created_by' => $creator->id,
        ]);
        $item->histories()->create(['quantity' => 2, 'project' => 'Old client']);
        $admin = $this->admin();
        $rows = $this->readCsv($this->actingAs($admin)->get(route('admin.inventory.export'))->streamedContent());
        $record = array_combine(self::CSV_FIELDS, $rows[2]);
        $record['available_qty'] = '8';
        $record['history_qty'] = '3';
        $record['history_project'] = "'=First client";
        $record['additional_histories'] = '[{"quantity":5,"project":"Second client"}]';
        $new = $record;
        $new['id'] = '';
        $new['item_description'] = 'New panel';
        $new['sub_location'] = '2Floor';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], array_values($record), array_values($new)])])
            ->assertRedirect(route('admin.inventory.dashboard'))
            ->assertSessionHas('status', 'Imported 2 inventory item(s).');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => '=original', 'available_qty' => 8, 'status_qty' => -6, 'created_by' => $creator->id]);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'New panel', 'sub_location' => '2Floor', 'created_by' => $admin->id]);
        $this->assertSame(['=First client', 'Second client'], $item->fresh()->histories->pluck('project')->all());
        $this->assertDatabaseMissing('inventory_item_histories', ['inventory_item_id' => $item->id, 'project' => 'Old client']);
    }

    public function test_mixed_warehouse_csv_places_items_in_their_designated_locations(): void
    {
        $marikina = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $ohaap = WarehouseLocation::factory()->create(['name' => 'Ohaap', 'is_active' => true, 'navigation_enabled' => true]);
        $headers = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $record = array_fill_keys(self::CSV_FIELDS, '');
        foreach (['on_hand', 'total_on_hand', 'per_panel_sqm', 'total_available_sqm', 'available_qty', 'available_sqm', 'reservation_qty', 'original_qty', 'status_qty'] as $field) {
            $record[$field] = '0';
        }
        $ohaapFirst = array_replace($record, ['item_description' => 'Ohaap first item', 'warehouse_location_id' => (string) $ohaap->id, 'location' => 'Ohaap', 'sub_location' => '1st Flr']);
        $marikinaItem = array_replace($record, ['item_description' => 'Marikina item', 'warehouse_location_id' => (string) $marikina->id, 'location' => 'Marikina', 'sub_location' => '2Floor']);
        $ohaapSecond = array_replace($record, ['item_description' => 'Ohaap second item', 'warehouse_location_id' => (string) $ohaap->id, 'location' => 'Ohaap', 'sub_location' => '5th Flr']);

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([
            $headers[0], $headers[1], array_values($ohaapFirst), array_values($marikinaItem), array_values($ohaapSecond),
        ])])->assertSessionHas('status', 'Imported 3 inventory item(s).');

        $this->assertDatabaseHas('inventory_items', ['item_description' => 'Marikina item', 'warehouse_location_id' => $marikina->id, 'sub_location' => '2Floor']);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'Ohaap first item', 'warehouse_location_id' => $ohaap->id, 'sub_location' => '1st Flr']);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'Ohaap second item', 'warehouse_location_id' => $ohaap->id, 'sub_location' => '5th Flr']);
        $this->get(route('admin.warehouses.show', $marikina))->assertSee('Marikina item')->assertDontSee('Ohaap first item')->assertDontSee('Ohaap second item');
        $this->get(route('admin.warehouses.show', $ohaap))->assertSee('Ohaap first item')->assertSee('Ohaap second item')->assertDontSee('Marikina item');
    }

    public function test_invalid_csv_record_rolls_back_all_changes(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id, 'available_qty' => 5]);
        $item->histories()->create(['quantity' => 2, 'project' => 'Original client']);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $valid = array_combine(self::CSV_FIELDS, $rows[2]);
        $valid['available_qty'] = '10';
        $invalid = $valid;
        $invalid['id'] = '';
        $invalid['sub_location'] = 'Invalid floor';

        $this->from(route('admin.inventory.dashboard'))
            ->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], array_values($valid), array_values($invalid)])])
            ->assertRedirect(route('admin.inventory.dashboard'))
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'available_qty' => 5]);
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'project' => 'Original client']);
        $this->assertDatabaseCount('inventory_items', 1);
    }

    public function test_exported_existing_items_keep_inactive_and_unassigned_locations_on_import(): void
    {
        $inactive = WarehouseLocation::factory()->create(['is_active' => false, 'navigation_enabled' => false]);
        $inactiveItem = InventoryItem::create(['item_description' => "'=literal text", 'warehouse_location_id' => $inactive->id]);
        $legacyItem = InventoryItem::create(['item_description' => 'Legacy panel', 'location' => 'Previous warehouse']);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile($rows)])
            ->assertSessionHas('status', 'Imported 2 inventory item(s).');

        $this->assertDatabaseHas('inventory_items', ['id' => $inactiveItem->id, 'item_description' => "'=literal text", 'warehouse_location_id' => $inactive->id]);
        $this->assertDatabaseHas('inventory_items', ['id' => $legacyItem->id, 'location' => 'Previous warehouse', 'warehouse_location_id' => null]);
    }

    public function test_exported_utf8_csv_can_be_imported_without_modification(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'manufacturer' => 'Mañana', 'warehouse_location_id' => $warehouse->id]);
        $content = $this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent();

        $this->post(route('admin.inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('inventory.csv', $content)])
            ->assertSessionHas('status', 'Imported 1 inventory item(s).');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'manufacturer' => 'Mañana']);
    }

    public function test_previous_csv_format_remains_importable(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id]);
        $item->histories()->createMany([
            ['quantity' => 3, 'project' => 'First client'],
            ['quantity' => 5, 'project' => 'Second client'],
        ]);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $record = array_combine(self::CSV_FIELDS, $rows[2]);
        $legacyFields = [
            'id', 'tag_number', 'date_received', 'po_sku_number', 'manufacturer', 'model_pixel_pitch',
            'item_description', 'warehouse_location_id', 'location', 'sub_location', 'on_hand', 'total_on_hand',
            'per_panel_sqm', 'total_available_sqm', 'available_qty', 'available_sqm', 'reservation_qty',
            'reservation_project_details', 'histories', 'original_qty', 'status_qty', 'status_particular',
            'remarks', 'csv_format',
        ];
        $record['histories'] = '[{"quantity":3,"project":"First client"},{"quantity":5,"project":"Second client"}]';
        $legacyRow = array_map(fn (string $field) => $record[$field], $legacyFields);

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$legacyFields, $legacyRow])])
            ->assertSessionHas('status', 'Imported 1 inventory item(s).');

        $this->assertSame(['First client', 'Second client'], $item->fresh()->histories->pluck('project')->all());
    }

    public function test_csv_rejects_a_location_that_does_not_match_its_warehouse_id(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id]);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $record = array_combine(self::CSV_FIELDS, $rows[2]);
        $record['location'] = 'Ohaap';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], array_values($record)])])
            ->assertSessionHasErrors('csv_file');
        $record['warehouse_location_id'] = 'not-an-id';
        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], array_values($record)])])
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'warehouse_location_id' => $warehouse->id]);
    }

    public function test_csv_import_rejects_duplicate_ids_and_invalid_history(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id]);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $invalid = array_combine(self::CSV_FIELDS, $rows[2]);
        $invalid['additional_histories'] = '{bad json}';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], $rows[2], $rows[2]])])
            ->assertSessionHasErrors('csv_file');
        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], array_values($invalid)])])
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => 'Panel']);
    }

    public function test_csv_import_rejects_unknown_item_ids_and_invalid_headers(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id]);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $unknown = array_combine(self::CSV_FIELDS, $rows[2]);
        $unknown['id'] = '999999';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], array_values($unknown)])])
            ->assertSessionHasErrors('csv_file');
        $this->post(route('admin.inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('inventory.csv', "incorrect,headers\nvalue,value\n")])
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => 'Panel']);
        $this->assertDatabaseCount('inventory_items', 1);
    }

    public function test_only_administrators_can_import_csv(): void
    {
        $this->post(route('admin.inventory.import'))->assertRedirect(route('login'));
        $staff = User::factory()->create();
        $staff->roles()->attach(Role::where('slug', 'warehouse-staff')->firstOrFail());

        $this->actingAs($staff)->post(route('admin.inventory.import'))->assertForbidden();
        $this->get(route('admin.inventory.dashboard'))->assertDontSee('Import CSV')->assertSee('Export CSV');
        $this->actingAs($this->admin())->get(route('admin.inventory.dashboard'))->assertSee('Import CSV');
    }

    public function test_centralized_spreadsheet_import_preserves_histories_and_assigns_warehouses(): void
    {
        $marikina = WarehouseLocation::factory()->create(['name' => 'Marikina']);
        $globaltronics = WarehouseLocation::factory()->create(['name' => 'Globaltronics']);
        $juan = WarehouseLocation::where('name', 'A Juan')->firstOrFail();
        $clark = WarehouseLocation::where('name', 'Clark Office')->firstOrFail();
        $rows = $this->spreadsheetHeaders();
        $rows[] = $this->spreadsheetRow([
            1 => '10/15/2025', 5 => 'First panel', 6 => 'A JUAN - 1ST FLR', 7 => '1,234 pcs',
            9 => '1.2345 sqm', 11 => '-5 pcs', 12 => '-4.61 sqm',
            15 => '4 pcs', 16 => 'First project', 18 => '-6',
        ]);
        $rows[] = $this->spreadsheetRow([
            2 => 'PO-123', 6 => 'DEFECTIVE', 7 => '2 pcs', 13 => '1 pcs',
            14 => 'Reserved project', 15 => '2', 16 => 'Second project', 20 => 'Damaged',
        ]);
        $rows[] = $this->spreadsheetRow([16 => 'Third project']);
        $rows[] = $this->spreadsheetRow([1 => "9-4-2026\n9-7-2026", 5 => 'Third floor panel', 6 => "A JUAN MAIN\n3RD FLOOR"]);
        $rows[] = $this->spreadsheetRow([1 => '12/15-16/25', 5 => 'Marikina panel', 6 => "MARIKINA\nWAREHOUSE"]);
        $rows[] = $this->spreadsheetRow([5 => 'Global panel', 6 => 'GLOBALTRONICS WAREHOUSE']);
        $rows[] = $this->spreadsheetRow([5 => 'Clark panel', 6 => 'CLARK OFFICE']);
        $rows[] = $this->spreadsheetRow([5 => 'TOTAL COUNT', 7 => '1234']);

        $this->actingAs($this->admin())->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile($rows)])
            ->assertSessionHas('status', 'Imported 5 inventory item(s).');

        $first = InventoryItem::where('item_description', 'First panel')->firstOrFail();
        $this->assertSame($juan->id, $first->warehouse_location_id);
        $this->assertSame('A JUAN MAIN 1ST FLOOR', $first->sub_location);
        $this->assertSame('2025-10-15', $first->date_received->format('Y-m-d'));
        $this->assertSame(1234, $first->on_hand);
        $this->assertSame('1.23', $first->per_panel_sqm);
        $this->assertSame(0, $first->available_qty);
        $this->assertSame('0.00', $first->available_sqm);
        $this->assertSame(-6, $first->status_qty);
        $this->assertSame('First project', $first->histories[0]->project);
        $this->assertSame(2, $first->histories[1]->quantity);
        $this->assertStringContainsString("Second project\nPO / SKU: PO-123\nLocation: DEFECTIVE\nOn-hand: 2 pcs", $first->histories[1]->project);
        $this->assertStringContainsString("Reservation qty: 1 pcs\nReservation project details: Reserved project\nRemarks: Damaged", $first->histories[1]->project);
        $this->assertSame(0, $first->histories[2]->quantity);
        $thirdFloor = InventoryItem::where('item_description', 'Third floor panel')->firstOrFail();
        $this->assertNull($thirdFloor->date_received);
        $this->assertSame("Date received: 9-4-2026\n9-7-2026", $thirdFloor->histories[0]->project);
        $this->assertSame('A JUAN MAIN 3RD FLOOR', $thirdFloor->sub_location);
        $range = InventoryItem::where('item_description', 'Marikina panel')->firstOrFail();
        $this->assertSame($marikina->id, $range->warehouse_location_id);
        $this->assertNull($range->date_received);
        $this->assertSame('Date received: 12/15-16/25', $range->histories[0]->project);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'Global panel', 'warehouse_location_id' => $globaltronics->id]);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'Clark panel', 'warehouse_location_id' => $clark->id]);
        $this->assertDatabaseMissing('inventory_items', ['item_description' => 'TOTAL COUNT']);
        $this->get(route('admin.warehouses.show', $juan))->assertSee('First panel')->assertDontSee('Marikina panel');
    }

    public function test_spreadsheet_import_rejects_invalid_rows_atomically(): void
    {
        WarehouseLocation::factory()->create(['name' => 'Marikina']);
        $rows = $this->spreadsheetHeaders();
        $rows[] = $this->spreadsheetRow([5 => 'Valid panel', 6 => 'MARIKINA']);
        $rows[] = $this->spreadsheetRow([5 => 'Invalid panel', 6 => 'UNKNOWN']);

        $this->actingAs($this->admin())->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile($rows)])
            ->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('inventory_items', ['item_description' => 'Valid panel']);

        $rows[6] = $this->spreadsheetRow([1 => '02/30/2025', 5 => 'Invalid date', 6 => 'MARIKINA']);
        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile($rows)])
            ->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('inventory_items', ['item_description' => 'Valid panel']);

        $rows[6] = $this->spreadsheetRow([5 => 'Invalid number', 6 => 'MARIKINA', 7 => '5 sqm']);
        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile($rows)])
            ->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('inventory_items', ['item_description' => 'Valid panel']);
    }

    public function test_spreadsheet_rejects_ambiguous_warehouse_names(): void
    {
        WarehouseLocation::factory()->create(['name' => 'Marikina', 'entity' => 'Warehouse']);
        WarehouseLocation::factory()->create(['name' => 'Marikina', 'entity' => 'Other']);
        $rows = $this->spreadsheetHeaders();
        $rows[] = $this->spreadsheetRow([5 => 'Panel', 6 => 'MARIKINA']);

        $this->actingAs($this->admin())->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile($rows)])
            ->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('inventory_items', ['item_description' => 'Panel']);
    }

    public function test_status_quantity_migration_refuses_a_lossy_rollback(): void
    {
        $item = InventoryItem::create(['item_description' => 'Panel', 'status_qty' => -6]);
        $migration = require database_path('migrations/2026_11_23_150000_extend_inventory_for_spreadsheet_import.php');

        try {
            $migration->down();
            $this->fail('A migration rollback must not discard negative quantities.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('unsigned', $exception->getMessage());
        }

        $this->assertSame(-6, $item->fresh()->status_qty);
    }

    private function spreadsheetHeaders(): array
    {
        return [
            $this->spreadsheetRow([0 => "CENTRALIZED LED INVENTORY\nLast Update: ONGOING"]),
            $this->spreadsheetRow([]),
            $this->spreadsheetRow([]),
            ['TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'MANUFACTURER', 'MODEL / PIXEL PITCH', 'ITEM DESCRIPTION', 'LOCATION', 'INVENTORY', '', '', '', 'AVAILABLE QTY', '', 'RESERVATION', '', 'HISTORY', '', 'ORIGINAL QTY', 'STATUS', '', 'REMARKS'],
            ['', '', '', '', '', '', '', 'ON-HAND', 'TOTAL ON-HAND', 'PER PANEL SQM', 'TOTAL AVAILABLE SQM', 'QTY', 'SQM', 'QTY', 'PROJECT DETAILS', 'QTY', 'PROJECT', '', 'QTY', 'PARTICULAR', ''],
        ];
    }

    private function spreadsheetRow(array $values): array
    {
        return array_replace(array_fill(0, 21, ''), $values);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());

        return $user;
    }

    /**
     * @return list<list<string|null>>
     */
    private function readCsv(string $content): array
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $content);
        rewind($stream);
        if (fread($stream, 3) !== "\xEF\xBB\xBF") {
            rewind($stream);
        }
        $rows = [];
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
    }

    /**
     * @param  list<list<string|null>>  $rows
     */
    private function csvFile(array $rows): UploadedFile
    {
        $stream = fopen('php://temp', 'w+');
        foreach ($rows as $row) {
            fputcsv($stream, $row, ',', '"', '');
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return UploadedFile::fake()->createWithContent('inventory.csv', $content);
    }
}
