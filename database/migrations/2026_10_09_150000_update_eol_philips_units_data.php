<?php

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('inventory_items')) {
            return;
        }

        $creator = User::where('email', 'mark.estoesta@globaltronics.net')->first()
            ?? User::first();
        $creatorId = $creator?->id;

        // Clear existing records for EOL PHILIPS UNITS to refresh with updated dataset
        InventoryItem::where('category', 'EOL PHILIPS UNITS')->delete();

        $items = [
            // 10"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-20',
                'model' => '10BDL4151T/00',
                'screen_size' => '10"',
                'item_description' => '10" PHILIPS TOUCH SCREEN MONITOR',
                'quantity' => 13,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 24"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-20',
                'model' => '24BDL4151T/00',
                'screen_size' => '24"',
                'item_description' => '24" PHILIPS TOUCH SCREEN MONITOR',
                'quantity' => 18,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 31.5"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2017-01-07',
                'model' => 'BDL3230QL/75',
                'screen_size' => '31.5"',
                'item_description' => '31.5" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 103,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 43"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-20',
                'model' => '43BDL3010Q/75',
                'screen_size' => '43"',
                'item_description' => '43" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 68,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-02-17',
                'model' => '43BDL3452T',
                'screen_size' => '43"',
                'item_description' => '43" PHILIPS TOUCH SCREEN MONITOR',
                'quantity' => 12,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 46"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2014-12-03',
                'model' => 'BDL4620QL/00',
                'screen_size' => '46"',
                'item_description' => '46" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 6,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2015-04-22',
                'model' => 'BDL4680VL/00',
                'screen_size' => '46"',
                'item_description' => '46" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 3,
                'location' => 'AJUAN',
                'status' => 'low_stock',
                'created_by' => $creatorId,
            ],
            // 47"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2015-12-30',
                'model' => 'BDL4777XL/00',
                'screen_size' => '47"',
                'item_description' => '47" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 2,
                'location' => 'Globaltronics',
                'status' => 'low_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2016-01-26',
                'model' => 'BDL4776XL/00',
                'screen_size' => '47"',
                'item_description' => '47" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 3,
                'location' => 'Globaltronics',
                'status' => 'low_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2017-09-06',
                'model' => 'BDL4780VH/75',
                'screen_size' => '47"',
                'item_description' => '47" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 1,
                'location' => 'AJUAN',
                'status' => 'low_stock',
                'created_by' => $creatorId,
            ],
            // 48"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2016-04-14',
                'model' => 'BDL4835QL/00',
                'screen_size' => '48"',
                'item_description' => '48" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 8,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 50"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-02-21',
                'model' => '50BDL3050Q/75',
                'screen_size' => '50"',
                'item_description' => '50" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 21,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-02-17',
                'model' => '50BDL4550D',
                'screen_size' => '50"',
                'item_description' => '50” PHILIPS FLAT WIDE MONITOR',
                'quantity' => 48,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 55"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2013-04-02',
                'model' => 'BDL5571V/00',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 3,
                'location' => 'Globaltronics',
                'status' => 'low_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2014-05-13',
                'model' => 'BDL5551EL/00',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 7,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2016-05-11',
                'model' => 'BDL5588XL/00',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 1,
                'location' => 'AJUAN',
                'status' => 'low_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-06',
                'model' => '55BDL3010Q/ 75',
                'screen_size' => '55"',
                'item_description' => '55” PHILIPS FLAT WIDE MONITOR',
                'quantity' => 102,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-06',
                'model' => 'BDL5588XH/75',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 20,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-02-17',
                'model' => '55BDL2005X',
                'screen_size' => '55"',
                'item_description' => '55” PHILIPS FLAT WIDE MONITOR',
                'quantity' => 42,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-03-07',
                'model' => '55BDL3452T',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS TOUCH SCREEN MONITOR',
                'quantity' => 6,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-11-23',
                'model' => '55BDL4007X',
                'screen_size' => '55"',
                'item_description' => '55” PHILIPS FLAT WIDE MONITOR',
                'quantity' => 89,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 65"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-06',
                'model' => '65BDL3050Q/75',
                'screen_size' => '65"',
                'item_description' => '65” PHILIPS FLAT WIDE MONITOR',
                'quantity' => 40,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-11-11',
                'model' => '65BDL4150D/75',
                'screen_size' => '65"',
                'item_description' => '65" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 25,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 84"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2017-05-17',
                'model' => 'BDL8470EU/75',
                'screen_size' => '84"',
                'item_description' => '84" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 7,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
            // 86"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-07-17',
                'model' => '86BDL3050Q/75',
                'screen_size' => '86"',
                'item_description' => '86" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 26,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creatorId,
            ],
        ];

        foreach ($items as $item) {
            $item['original_quantity'] = $item['quantity'];
            $item['forecasted_quantity'] = $item['quantity'];
            $item['acu_quantity'] = $item['quantity'];
            InventoryItem::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
