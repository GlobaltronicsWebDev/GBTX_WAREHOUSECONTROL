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
        $this->assertCount(2, $rows);
        $record = array_combine($rows[0], $rows[1]);
        $this->assertSame((string) $item->id, $record['id']);
        $this->assertSame("'=dangerous formula", $record['item_description']);
        $this->assertSame((string) $warehouse->id, $record['warehouse_location_id']);
        $this->assertSame('1st Bldg', $record['sub_location']);
        $this->assertSame([['quantity' => 4, 'project' => 'Client Alpha'], ['quantity' => 8, 'project' => 'Client Beta']], json_decode($record['histories'], true));
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
        $record = array_combine($rows[0], $rows[1]);
        $record['available_qty'] = '8';
        $record['histories'] = '[{"quantity":3,"project":"First client"},{"quantity":5,"project":"Second client"}]';
        $new = $record;
        $new['id'] = '';
        $new['item_description'] = 'New panel';
        $new['sub_location'] = '2Floor';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], array_values($record), array_values($new)])])
            ->assertRedirect(route('admin.inventory.dashboard'))
            ->assertSessionHas('status', 'Imported 2 inventory item(s).');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => '=original', 'available_qty' => 8, 'created_by' => $creator->id]);
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'New panel', 'sub_location' => '2Floor', 'created_by' => $admin->id]);
        $this->assertSame(['First client', 'Second client'], $item->fresh()->histories->pluck('project')->all());
        $this->assertDatabaseMissing('inventory_item_histories', ['inventory_item_id' => $item->id, 'project' => 'Old client']);
    }

    public function test_invalid_csv_record_rolls_back_all_changes(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id, 'available_qty' => 5]);
        $item->histories()->create(['quantity' => 2, 'project' => 'Original client']);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $valid = array_combine($rows[0], $rows[1]);
        $valid['available_qty'] = '10';
        $invalid = $valid;
        $invalid['id'] = '';
        $invalid['sub_location'] = 'Invalid floor';

        $this->from(route('admin.inventory.dashboard'))
            ->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], array_values($valid), array_values($invalid)])])
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

    public function test_csv_import_rejects_duplicate_ids_and_invalid_history(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id]);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $invalid = array_combine($rows[0], $rows[1]);
        $invalid['histories'] = '{bad json}';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], $rows[1], $rows[1]])])
            ->assertSessionHasErrors('csv_file');
        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], array_values($invalid)])])
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => 'Panel']);
    }

    public function test_csv_import_rejects_unknown_item_ids_and_invalid_headers(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Panel', 'warehouse_location_id' => $warehouse->id]);
        $rows = $this->readCsv($this->actingAs($this->admin())->get(route('admin.inventory.export'))->streamedContent());
        $unknown = array_combine($rows[0], $rows[1]);
        $unknown['id'] = '999999';

        $this->post(route('admin.inventory.import'), ['csv_file' => $this->csvFile([$rows[0], array_values($unknown)])])
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
        $rows = [];
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);
        $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);

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
