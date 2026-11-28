<?php

namespace Tests\Feature\Admin;

use App\Models\DeliveryReceipt;
use App\Models\InventoryItem;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_drafts_use_separate_number_sequences_without_changing_stock(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem();

        foreach (['client', 'internal', 'client'] as $type) {
            $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $this->receiptData($item, $type))->assertRedirect();
        }

        $this->assertSame(
            ['DRC-000001', 'DRI-000001', 'DRC-000002'],
            DeliveryReceipt::query()->orderBy('id')->pluck('number')->all(),
        );
        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id, 'available_qty' => 10, 'on_hand' => 10, 'total_on_hand' => 10,
        ]);
        $this->assertDatabaseHas('delivery_receipt_lines', [
            'inventory_item_id' => $item->id, 'quantity' => 3, 'unit_of_measurement' => 'PCS',
        ]);
    }

    public function test_draft_can_be_edited_and_release_records_one_stock_out(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem(['available_qty' => 8, 'on_hand' => 10, 'total_on_hand' => 12]);
        $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $this->receiptData($item));
        $receipt = DeliveryReceipt::query()->firstOrFail();
        $data = $this->receiptData($item);
        $data['lines'][0]['quantity'] = 4;
        $data['recipient'] = 'Updated Client';

        $this->put(route('admin.delivery-receipts.update', $receipt), $data)
            ->assertRedirect(route('admin.delivery-receipts.show', $receipt));
        $this->assertSame(8, $item->fresh()->available_qty);
        $this->post(route('admin.delivery-receipts.release', $receipt))
            ->assertRedirect(route('admin.delivery-receipts.show', $receipt));

        $receipt->refresh();
        $this->assertSame('released', $receipt->status);
        $this->assertNotNull($receipt->released_at);
        $this->assertSame($user->id, $receipt->released_by);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id, 'available_qty' => 4, 'on_hand' => 6, 'total_on_hand' => 8,
        ]);
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $receipt->stock_transaction_id,
            'type' => 'out',
            'department_customer' => 'Updated Client',
            'released_by' => $user->name,
        ]);
        $this->assertDatabaseHas('stock_transaction_lines', [
            'stock_transaction_id' => $receipt->stock_transaction_id,
            'inventory_item_id' => $item->id, 'quantity' => 4, 'stock_before' => 8, 'stock_after' => 4,
        ]);
        $this->get(route('admin.delivery-receipts.show', $receipt))
            ->assertSee($receipt->stockTransaction->reference_number)
            ->assertSee('Released')->assertDontSee('Draft — not released');

        $this->from(route('admin.delivery-receipts.show', $receipt))
            ->post(route('admin.delivery-receipts.release', $receipt))
            ->assertSessionHasErrors('receipt');
        $this->put(route('admin.delivery-receipts.update', $receipt), $data)->assertSessionHasErrors('receipt');
        $this->get(route('admin.delivery-receipts.edit', $receipt))->assertForbidden();
        $this->assertDatabaseCount('stock_transactions', 1);
        $this->assertSame(4, $item->fresh()->available_qty);
    }

    public function test_insufficient_stock_rolls_back_the_entire_release(): void
    {
        $user = User::factory()->create();
        $first = $this->inventoryItem();
        $second = $this->inventoryItem();
        $data = $this->receiptData($first);
        $data['lines'][] = ['inventory_item_id' => $second->id, 'quantity' => 2];
        $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $data);
        $receipt = DeliveryReceipt::query()->firstOrFail();
        $second->update(['available_qty' => 1, 'on_hand' => 1, 'total_on_hand' => 1]);

        $this->from(route('admin.delivery-receipts.show', $receipt))
            ->post(route('admin.delivery-receipts.release', $receipt))
            ->assertSessionHasErrors('lines.1.quantity');

        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertSame(10, $first->fresh()->available_qty);
        $this->assertSame(1, $second->fresh()->available_qty);
        $this->assertSame('draft', $receipt->fresh()->status);
        $this->assertNull($receipt->fresh()->stock_transaction_id);
    }

    public function test_draft_validates_products_quantities_and_receipt_type(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem();
        $data = $this->receiptData($item);
        $data['type'] = 'invalid';
        $data['lines'][0]['quantity'] = 0;
        $data['lines'][] = ['inventory_item_id' => $item->id, 'quantity' => 1];

        $this->actingAs($user)->from(route('admin.delivery-receipts.create'))
            ->post(route('admin.delivery-receipts.store'), $data)
            ->assertSessionHasErrors(['type', 'lines.0.quantity', 'lines.0.inventory_item_id']);

        $this->assertDatabaseCount('delivery_receipts', 0);
        $this->assertSame(10, $item->fresh()->available_qty);
    }

    public function test_receipt_type_cannot_be_changed_and_unavailable_items_cannot_be_released(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem();
        $data = $this->receiptData($item, 'internal');
        $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $data);
        $receipt = DeliveryReceipt::query()->firstOrFail();
        $data['type'] = 'client';

        $this->from(route('admin.delivery-receipts.edit', $receipt))
            ->put(route('admin.delivery-receipts.update', $receipt), $data)
            ->assertSessionHasErrors('type');
        $item->delete();
        $this->from(route('admin.delivery-receipts.show', $receipt))
            ->post(route('admin.delivery-receipts.release', $receipt))
            ->assertSessionHasErrors('lines.0.inventory_item_id');
        $this->assertSame('draft', $receipt->fresh()->status);
        $this->assertDatabaseCount('stock_transactions', 0);
    }

    public function test_client_and_internal_receipt_views_show_their_distinct_print_fields(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem();
        $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $this->receiptData($item, 'client'));
        $this->post(route('admin.delivery-receipts.store'), $this->receiptData($item, 'internal'));
        $client = DeliveryReceipt::query()->where('type', 'client')->firstOrFail();
        $internal = DeliveryReceipt::query()->where('type', 'internal')->firstOrFail();

        $this->get(route('admin.delivery-receipts.show', $client))
            ->assertOk()->assertSee('DR to Client')->assertSee('Item Name / Description')
            ->assertSee('Contact Person')->assertSee('Draft — not released')->assertSee('SN-123');
        $this->get(route('admin.delivery-receipts.show', $internal))
            ->assertOk()->assertSee('DR Internal')->assertSee('Model / Serial No.')
            ->assertSee('SN-123')->assertSee('Site / Client');
    }

    public function test_create_screen_exposes_receipt_types_and_product_entry(): void
    {
        $item = $this->inventoryItem();

        $this->actingAs(User::factory()->create())->get(route('admin.delivery-receipts.create'))
            ->assertOk()->assertSee('DR to Client')->assertSee('DR Internal')
            ->assertSee('Add Product')->assertSee($item->item_description);
    }

    public function test_received_by_is_only_shown_on_internal_draft_edit_and_existing_client_data_is_preserved(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem();
        $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $this->receiptData($item, 'client'));
        $this->post(route('admin.delivery-receipts.store'), $this->receiptData($item, 'internal'));
        $client = DeliveryReceipt::query()->where('type', 'client')->firstOrFail();
        $internal = DeliveryReceipt::query()->where('type', 'internal')->firstOrFail();

        $this->get(route('admin.delivery-receipts.edit', $client))->assertDontSee('name="received_by"', false);
        $this->get(route('admin.delivery-receipts.edit', $internal))->assertSee('name="received_by"', false);

        $data = $this->receiptData($item, 'client');
        unset($data['received_by']);
        $this->put(route('admin.delivery-receipts.update', $client), $data)->assertRedirect();
        $this->assertSame('Receiver', $client->fresh()->received_by);
    }

    public function test_unit_changes_after_drafting_require_the_draft_to_be_updated(): void
    {
        $user = User::factory()->create();
        $item = $this->inventoryItem();
        $data = $this->receiptData($item);
        $this->actingAs($user)->post(route('admin.delivery-receipts.store'), $data);
        $receipt = DeliveryReceipt::query()->firstOrFail();
        $item->update(['unit_of_measurement' => 'BOX']);

        $this->from(route('admin.delivery-receipts.show', $receipt))
            ->post(route('admin.delivery-receipts.release', $receipt))
            ->assertSessionHasErrors('lines.0.unit_of_measurement');
        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertSame(10, $item->fresh()->available_qty);

        $this->put(route('admin.delivery-receipts.update', $receipt), $data)->assertRedirect();
        $this->post(route('admin.delivery-receipts.release', $receipt))->assertRedirect();
        $this->assertDatabaseHas('stock_transaction_lines', [
            'stock_transaction_id' => $receipt->fresh()->stock_transaction_id,
            'unit_of_measurement' => 'BOX',
        ]);
    }

    public function test_guests_cannot_create_or_release_receipts(): void
    {
        $this->get(route('admin.delivery-receipts.create'))->assertRedirect(route('login'));
        $this->post(route('admin.delivery-receipts.store'), [])->assertRedirect(route('login'));
        $this->post(route('admin.delivery-receipts.release', 1))->assertRedirect(route('login'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function inventoryItem(array $attributes = []): InventoryItem
    {
        $warehouse = WarehouseLocation::factory()->create();

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
    private function receiptData(InventoryItem $item, string $type = 'client'): array
    {
        return [
            'type' => $type,
            'receipt_date' => '2026-11-28',
            'recipient' => 'Example Client',
            'address' => 'Example address',
            'request_number' => 'SRF-001',
            'received_by' => 'Receiver',
            'lines' => [
                ['inventory_item_id' => $item->id, 'quantity' => 3, 'model_serial' => 'SN-123'],
            ],
        ];
    }
}
