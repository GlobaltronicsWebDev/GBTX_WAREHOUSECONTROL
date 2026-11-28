<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_saves_multiple_lines_and_increases_all_inventory_balances(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create();
        $panel = $this->inventoryItem($warehouse, [
            'po_sku_number' => 'SKU-PANEL',
            'item_description' => 'LED panel',
            'on_hand' => 10,
            'total_on_hand' => 12,
            'available_qty' => 8,
        ]);
        $controller = $this->inventoryItem($warehouse, [
            'po_sku_number' => 'SKU-CONTROL',
            'item_description' => 'Controller',
            'on_hand' => 3,
            'total_on_hand' => 3,
            'available_qty' => 3,
            'unit_of_measurement' => 'BOX',
        ]);

        $response = $this->actingAs($user)->post(route('admin.stock-in.store'), [
            'transaction_date' => '2026-11-28',
            'purchase_order_number' => 'PO-928',
            'supplier_name' => 'Example Supplier',
            'received_by' => 'Warehouse Clerk',
            'lines' => [
                ['inventory_item_id' => $panel->id, 'quantity' => 5, 'unit_of_measurement' => 'PCS'],
                ['inventory_item_id' => $controller->id, 'quantity' => 2, 'unit_of_measurement' => 'BOX'],
            ],
            'attachment' => UploadedFile::fake()->create('delivery.pdf', 100, 'application/pdf'),
        ]);

        $transaction = StockTransaction::query()->firstOrFail();
        $response->assertRedirect(route('admin.stock-in.show', $transaction));

        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'reference_number' => 'SI-2026-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
            'purchase_order_number' => 'PO-928',
            'supplier_name' => 'Example Supplier',
            'received_by' => 'Warehouse Clerk',
            'created_by' => $user->id,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $panel->id,
            'on_hand' => 15,
            'total_on_hand' => 17,
            'available_qty' => 13,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $controller->id,
            'on_hand' => 5,
            'total_on_hand' => 5,
            'available_qty' => 5,
        ]);
        $this->assertDatabaseHas('stock_transaction_lines', [
            'stock_transaction_id' => $transaction->id,
            'product_sku' => 'SKU-PANEL',
            'product_name' => 'LED panel',
            'quantity' => 5,
            'unit_of_measurement' => 'PCS',
            'stock_before' => 8,
            'stock_after' => 13,
        ]);
        Storage::disk('local')->assertExists($transaction->attachment_path);
    }

    public function test_stock_out_deducts_all_balances_and_records_both_handlers(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create();
        $item = $this->inventoryItem($warehouse, [
            'on_hand' => 12,
            'total_on_hand' => 14,
            'available_qty' => 10,
        ]);

        $response = $this->actingAs($user)->post(route('admin.stock-out.store'), $this->stockOutData($item, 4));

        $transaction = StockTransaction::query()->firstOrFail();
        $response->assertRedirect(route('admin.stock-out.show', $transaction));

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'on_hand' => 8,
            'total_on_hand' => 10,
            'available_qty' => 6,
        ]);
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'type' => 'out',
            'request_number' => 'REQ-100',
            'department_customer' => 'Assembly',
            'released_by' => 'Warehouse Clerk',
            'received_by' => 'Line Supervisor',
        ]);
        $this->assertDatabaseHas('stock_transaction_lines', [
            'stock_transaction_id' => $transaction->id,
            'quantity' => 4,
            'stock_before' => 10,
            'stock_after' => 6,
        ]);
    }

    public function test_stock_out_rejects_quantity_above_available_stock_without_partial_changes(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create();
        $firstItem = $this->inventoryItem($warehouse, [
            'item_description' => 'First item',
            'on_hand' => 10,
            'total_on_hand' => 10,
            'available_qty' => 10,
        ]);
        $item = $this->inventoryItem($warehouse, [
            'item_description' => 'Second item',
            'on_hand' => 5,
            'total_on_hand' => 5,
            'available_qty' => 5,
        ]);
        $data = $this->stockOutData($firstItem, 2);
        $data['lines'][] = [
            'inventory_item_id' => $item->id,
            'quantity' => 6,
            'unit_of_measurement' => $item->unit_of_measurement,
        ];

        $this->actingAs($user)
            ->from(route('admin.stock-out.create'))
            ->post(route('admin.stock-out.store'), $data)
            ->assertRedirect(route('admin.stock-out.create'))
            ->assertSessionHasErrors('lines.1.quantity');
        $this->assertSame(
            'The requested quantity exceeds the available stock (5).',
            session('errors')->getBag('default')->first('lines.1.quantity'),
        );

        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertDatabaseCount('stock_transaction_lines', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $firstItem->id,
            'on_hand' => 10,
            'total_on_hand' => 10,
            'available_qty' => 10,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'on_hand' => 5,
            'total_on_hand' => 5,
            'available_qty' => 5,
        ]);
    }

    public function test_stock_transactions_reject_zero_quantity(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create();
        $item = $this->inventoryItem($warehouse);
        $data = $this->stockOutData($item, 0);

        $this->actingAs($user)
            ->from(route('admin.stock-out.create'))
            ->post(route('admin.stock-out.store'), $data)
            ->assertRedirect(route('admin.stock-out.create'))
            ->assertSessionHasErrors('lines.0.quantity');

        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'available_qty' => 10,
        ]);
    }

    public function test_stock_transaction_rejects_a_unit_that_differs_from_the_inventory_item(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create();
        $item = $this->inventoryItem($warehouse, ['unit_of_measurement' => 'PCS']);
        $data = [
            'transaction_date' => '2026-11-28',
            'supplier_name' => 'Example Supplier',
            'received_by' => 'Warehouse Clerk',
            'lines' => [
                ['inventory_item_id' => $item->id, 'quantity' => 2, 'unit_of_measurement' => 'BOX'],
            ],
        ];

        $this->actingAs($user)
            ->from(route('admin.stock-in.create'))
            ->post(route('admin.stock-in.store'), $data)
            ->assertRedirect(route('admin.stock-in.create'))
            ->assertSessionHasErrors('lines.0.unit_of_measurement');

        $this->assertSame(
            'Use the unit of measurement configured for this inventory item.',
            session('errors')->getBag('default')->first('lines.0.unit_of_measurement'),
        );
        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'on_hand' => 10,
            'total_on_hand' => 10,
            'available_qty' => 10,
        ]);
    }

    public function test_stock_in_rejects_a_balance_that_would_exceed_the_supported_quantity_range(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create();
        $item = $this->inventoryItem($warehouse, [
            'on_hand' => 4294967295,
            'total_on_hand' => 4294967295,
            'available_qty' => 4294967295,
        ]);
        $data = [
            'transaction_date' => '2026-11-28',
            'supplier_name' => 'Example Supplier',
            'received_by' => 'Warehouse Clerk',
            'lines' => [
                ['inventory_item_id' => $item->id, 'quantity' => 1, 'unit_of_measurement' => 'PCS'],
            ],
        ];

        $this->actingAs($user)
            ->from(route('admin.stock-in.create'))
            ->post(route('admin.stock-in.store'), $data)
            ->assertRedirect(route('admin.stock-in.create'))
            ->assertSessionHasErrors('lines.0.quantity');

        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'on_hand' => 4294967295,
            'total_on_hand' => 4294967295,
            'available_qty' => 4294967295,
        ]);
    }

    public function test_stock_out_report_exposes_print_and_export_actions(): void
    {
        $user = User::factory()->create();
        $transaction = StockTransaction::query()->create([
            'reference_number' => 'SO-2026-000001',
            'type' => 'out',
            'transaction_date' => '2026-11-28',
            'department_customer' => 'Assembly',
            'released_by' => 'Warehouse Clerk',
            'received_by' => 'Line Supervisor',
            'created_by' => $user->id,
        ]);
        $transaction->lines()->create([
            'product_sku' => 'SKU-01',
            'product_name' => 'Sample inventory item',
            'warehouse_location_name' => 'Main Warehouse',
            'quantity' => 2,
            'unit_of_measurement' => 'PCS',
            'stock_before' => 10,
            'stock_after' => 8,
        ]);

        $this->actingAs($user)->get(route('admin.stock-out.index'))
            ->assertSee('Stock Out Management')
            ->assertSee('Print Report')
            ->assertSee('Export CSV')
            ->assertSee('SO-2026-')
            ->assertSee('Warehouse Clerk');
    }

    public function test_stock_in_report_export_includes_each_product_and_escapes_spreadsheet_formulas(): void
    {
        $user = User::factory()->create();
        $transaction = StockTransaction::query()->create([
            'reference_number' => 'SI-2026-000001',
            'type' => 'in',
            'transaction_date' => '2026-11-28',
            'supplier_name' => '=HYPERLINK("https://example.test")',
            'received_by' => 'Warehouse Clerk',
            'created_by' => $user->id,
        ]);
        $transaction->lines()->create([
            'product_sku' => 'SKU-42',
            'product_name' => 'Panel',
            'warehouse_location_name' => 'Main Warehouse',
            'quantity' => 3,
            'unit_of_measurement' => 'PCS',
            'stock_before' => 4,
            'stock_after' => 7,
        ]);

        $report = $this->actingAs($user)->get(route('admin.stock-in.export'));
        $report->assertOk();
        $csv = $report->streamedContent();
        $this->assertStringContainsString('Reference Number', $csv);
        $this->assertStringContainsString('SKU-42', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(',"=HYPERLINK', $csv);
    }

    public function test_guests_cannot_open_stock_transaction_form(): void
    {
        $this->get(route('admin.stock-in.create'))->assertRedirect(route('login'));
    }

    public function test_guests_cannot_submit_stock_transactions(): void
    {
        $this->post(route('admin.stock-in.store'), [])->assertRedirect(route('login'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function inventoryItem(WarehouseLocation $warehouse, array $attributes = []): InventoryItem
    {
        return InventoryItem::query()->create(array_merge([
            'tag_number' => 'TAG-01',
            'po_sku_number' => 'SKU-01',
            'item_description' => 'Sample inventory item',
            'warehouse_location_id' => $warehouse->id,
            'location' => $warehouse->name,
            'unit_of_measurement' => 'PCS',
            'on_hand' => 10,
            'total_on_hand' => 10,
            'available_qty' => 10,
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function stockOutData(InventoryItem $item, int $quantity): array
    {
        return [
            'transaction_date' => '2026-11-28',
            'request_number' => 'REQ-100',
            'department_customer' => 'Assembly',
            'released_by' => 'Warehouse Clerk',
            'received_by' => 'Line Supervisor',
            'remarks' => 'Production issue',
            'lines' => [
                ['inventory_item_id' => $item->id, 'quantity' => $quantity, 'unit_of_measurement' => $item->unit_of_measurement],
            ],
        ];
    }
}
