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
        'item_description', 'location', 'on_hand', 'total_on_hand', 'per_panel_sqm', 'total_available_sqm',
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
            'LOCATION', 'INVENTORY', '', '', '', 'AVAILABLE QTY', '', 'RESERVATION', '', 'HISTORY', '',
            'ORIGINAL QTY', 'STATUS', '', 'REMARKS', 'ITEM ID', 'WAREHOUSE ID', 'SUB-LOCATION',
            'ADDITIONAL HISTORIES', 'CSV FORMAT',
        ], $rows[0]);
        $this->assertSame([
            '', '', '', '', '', '', '', 'ON-HAND', 'TOTAL ON-HAND', 'PER PANEL SQM', 'TOTAL AVAILABLE SQM',
            'QTY', 'SQM', 'QTY', 'PROJECT DETAILS', 'QTY', 'PROJECT', '', 'QTY', 'PARTICULAR', '',
            '', '', '', '', '',
        ], $rows[1]);
        $record = array_combine(self::CSV_FIELDS, $rows[2]);
        $this->assertSame((string) $item->id, $record['id']);
        $this->assertSame("'=dangerous formula", $record['item_description']);
        $this->assertSame((string) $warehouse->id, $record['warehouse_location_id']);
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

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => '=original', 'available_qty' => 8, 'created_by' => $creator->id]);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'New panel', 'sub_location' => '2Floor', 'created_by' => $admin->id]);
        $this->assertSame(['=First client', 'Second client'], $item->fresh()->histories->pluck('project')->all());
        $this->assertDatabaseMissing('inventory_item_histories', ['inventory_item_id' => $item->id, 'project' => 'Old client']);
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
