<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('srf_requisitions') && Schema::hasTable('inventory_items')) {
            Schema::table('srf_requisitions', function (Blueprint $table) {
                $table->string('item_model')->nullable();
                $table->string('item_manufacturer')->nullable();
                $table->string('item_category')->nullable();
                $table->text('item_description')->nullable();
            });

            DB::table('srf_requisitions')
                ->whereNotNull('inventory_item_id')
                ->orderBy('id')
                ->chunkById(100, function ($requisitions): void {
                    $inventoryItems = DB::table('inventory_items')
                        ->whereIn('id', $requisitions->pluck('inventory_item_id')->all())
                        ->get()
                        ->keyBy('id');

                    foreach ($requisitions as $requisition) {
                        $item = $inventoryItems->get($requisition->inventory_item_id);

                        if ($item) {
                            DB::table('srf_requisitions')
                                ->where('id', $requisition->id)
                                ->update([
                                    'item_model' => $item->model,
                                    'item_manufacturer' => $item->manufacturer,
                                    'item_category' => $item->category,
                                    'item_description' => $item->item_description,
                                ]);
                        }
                    }
                });

            Schema::table('srf_requisitions', function (Blueprint $table) {
                $table->dropForeign(['inventory_item_id']);
                $table->dropColumn('inventory_item_id');
            });
        }

        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('sales_orders');

        if (Schema::hasTable('transaction_notifications')) {
            DB::table('transaction_notifications')->where('type', 'sales_order')->delete();
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('type', 'App\\Notifications\\OrderStepNotification')
                ->delete();
        }

        $salesRoleSlugs = [
            'sales',
            'sales-executive',
            'jovy',
            'fred',
            'owen',
            'macy',
            'badet',
            'king',
            'content',
            'it',
            'accounting',
            'inventory-supervisor',
            'dispatch-officer',
        ];

        if (Schema::hasTable('roles') && Schema::hasTable('role_user')) {
            $salesRoleIds = DB::table('roles')->whereIn('slug', $salesRoleSlugs)->pluck('id');
            $salesUserIds = DB::table('role_user')->whereIn('role_id', $salesRoleIds)->pluck('user_id');

            DB::table('role_user')->whereIn('role_id', $salesRoleIds)->delete();

            if ($salesUserIds->isNotEmpty()) {
                $orphanedSalesUserIds = DB::table('users')
                    ->whereIn('id', $salesUserIds)
                    ->whereNotExists(function ($query) {
                        $query->selectRaw('1')
                            ->from('role_user')
                            ->whereColumn('role_user.user_id', 'users.id');
                    })
                    ->pluck('id');

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->whereIn('user_id', $orphanedSalesUserIds)->delete();
                }

                DB::table('users')->whereIn('id', $orphanedSalesUserIds)->delete();
            }

            DB::table('roles')->whereIn('slug', $salesRoleSlugs)->delete();
        }

        $salesEmails = [
            'sales@globaltronics.net',
            'anne.liboon@globaltronics.net',
            'sheryl.quilla@globaltronics.net',
            'sherly.quilla@globaltronics.net',
            'jovy@globaltronics.net',
            'fred@globaltronics.net',
            'owen@globaltronics.net',
            'macy@globaltronics.net',
            'badet@globaltronics.net',
            'king@globaltronics.net',
            'content@globaltronics.net',
            'it@globaltronics.net',
            'accounting@globaltronics.net',
        ];

        if (Schema::hasTable('users')) {
            $salesUserIds = DB::table('users')
                ->whereIn(DB::raw('lower(email)'), $salesEmails)
                ->pluck('id');

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->whereIn('user_id', $salesUserIds)->delete();
            }

            if (Schema::hasTable('role_user')) {
                DB::table('role_user')->whereIn('user_id', $salesUserIds)->delete();
            }

            DB::table('users')->whereIn('id', $salesUserIds)->delete();

            if (Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')
                    ->whereIn(DB::raw('lower(email)'), $salesEmails)
                    ->delete();
            }
        }

        Schema::dropIfExists('inventory_items');
    }

    public function down(): void
    {
        throw new RuntimeException('This migration removes inventory and sales data and cannot be reversed.');
    }
};
