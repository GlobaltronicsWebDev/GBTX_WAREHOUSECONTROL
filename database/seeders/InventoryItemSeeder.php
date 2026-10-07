<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::where('email', 'mark.estoesta@globaltronics.net')->first()
            ?? User::first();

        // Clear existing mock records for EOL PHILIPS UNITS
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-02-17',
                'model' => '50BDL4550D',
                'screen_size' => '50"',
                'item_description' => '50" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 48,
                'location' => 'AJUAN',
                'status' => 'in_stock',
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-06',
                'model' => '55BDL3010Q/75',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 102,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-02-17',
                'model' => '55BDL2005X',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 42,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2022-11-23',
                'model' => '55BDL4007X',
                'screen_size' => '55"',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 89,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            // 65"
            [
                'category' => 'EOL PHILIPS UNITS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2019-08-06',
                'model' => '65BDL3050Q/75',
                'screen_size' => '65"',
                'item_description' => '65" PHILIPS FLAT WIDE MONITOR',
                'quantity' => 40,
                'location' => 'Globaltronics',
                'status' => 'in_stock',
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
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
                'created_by' => $creator?->id,
            ],
        ];

        foreach ($items as $item) {
            InventoryItem::create($item);
        }

        // Seed Centralized LED Inventory from parsed database list
        InventoryItem::where('category', 'CENTRALIZED LED INVENTORY')->delete();

        $ledJsonPath = storage_path('led_records.json');
        if (file_exists($ledJsonPath)) {
            $ledItems = json_decode(file_get_contents($ledJsonPath), true) ?: [];
            foreach ($ledItems as $item) {
                $item['created_by'] = $creator?->id;
                InventoryItem::create($item);
            }
        }

        // Seed LED Service Units (Events & Demo Display Inventory)
        InventoryItem::where('category', 'LED Service Units')->delete();

        $ledServiceItems = [
            [
                'category' => 'LED Service Units',
                'tag_number' => '1',
                'location' => 'GLOBALTRONICS',
                'manufacturer' => 'UNILUMIN',
                'check_in_date' => '2026-02-15',
                'model' => 'P2.5 INDOOR',
                'po_number' => 'PO-2026-0104',
                'item_description' => '500x500mm Die-Cast Aluminum Cabinet, High Refresh Rate, Front Serviceable Demo Unit',
                'quantity' => 48,
                'sqm' => 12.00,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'LED Service Units',
                'tag_number' => '2',
                'location' => 'MARIKINA',
                'manufacturer' => 'FABULUX',
                'check_in_date' => '2026-03-10',
                'model' => 'P3.91 OUTDOOR',
                'po_number' => 'PO-2026-0219',
                'item_description' => '500x1000mm Outdoor Rental Display Panel, IP65 Waterproof Event Unit',
                'quantity' => 64,
                'sqm' => 32.00,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'LED Service Units',
                'tag_number' => '3',
                'location' => 'AJUAN - 1ST FLR',
                'manufacturer' => 'GLOBALTRONICS',
                'check_in_date' => '2026-04-05',
                'model' => 'P1.875 INDOOR',
                'po_number' => 'PO-2026-0331',
                'item_description' => 'Indoor Magnetic Module Panel, SMD1515, 3840Hz Ultra HD Demo Unit',
                'quantity' => 32,
                'sqm' => 8.00,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'LED Service Units',
                'tag_number' => '4',
                'location' => 'GLOBALTRONICS',
                'manufacturer' => 'DAHUA',
                'check_in_date' => '2026-04-18',
                'model' => 'P2.976 DIE-CAST',
                'po_number' => 'PO-2026-0442',
                'item_description' => '500x500mm Curved & Straight Die-Cast Cabinet for Stage Events',
                'quantity' => 80,
                'sqm' => 20.00,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'LED Service Units',
                'tag_number' => '5',
                'location' => 'GLOBALTRONICS',
                'manufacturer' => 'ABSEN',
                'check_in_date' => '2026-05-02',
                'model' => 'P4.81 OUTDOOR',
                'po_number' => 'PO-2026-0511',
                'item_description' => '500x1000mm High Brightness Outdoor Event Display Panel',
                'quantity' => 24,
                'sqm' => 12.00,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'LED Service Units',
                'tag_number' => '6',
                'location' => 'GLOBALTRONICS',
                'manufacturer' => 'LKGT - INDOOR LED DISPLAY',
                'check_in_date' => '2026-06-12',
                'model' => 'LKGT-P2.5 INDOOR',
                'po_number' => 'PO-2026-0614',
                'item_description' => '500x500mm LKGT Ultra-Slim Indoor Die-Cast Cabinet, Front Serviceable Demo Unit',
                'quantity' => 36,
                'sqm' => 9.00,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
        ];

        foreach ($ledServiceItems as $item) {
            InventoryItem::create($item);
        }

        // Seed Philips Service Units (Events & Demo Display Inventory)
        InventoryItem::where('category', 'Philips Service Units')->delete();

        $philipsServiceItems = [
            [
                'category' => 'Philips Service Units',
                'tag_number' => 'SN-PHILIPS-0012, SN-PHILIPS-0013, SN-PHILIPS-0014',
                'location' => 'GLOBALTRONICS',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2026-03-01',
                'model' => '55BDL4050D',
                'screen_size' => '55"',
                'po_number' => 'PO-2026-0814',
                'item_description' => '55" PHILIPS FLAT WIDE MONITOR (SERVICE / DEMO UNIT)',
                'quantity' => 12,
                'forecasted_quantity' => 15,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'Philips Service Units',
                'tag_number' => 'SN-PHILIPS-0045, SN-PHILIPS-0046',
                'location' => 'AJUAN - 1ST FLR',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2026-03-15',
                'model' => '43BDL3452T',
                'screen_size' => '43"',
                'po_number' => 'PO-2026-0922',
                'item_description' => '43" PHILIPS TOUCH SCREEN MONITOR (EVENT SERVICE UNIT)',
                'quantity' => 8,
                'forecasted_quantity' => 10,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'Philips Service Units',
                'tag_number' => 'SN-PHILIPS-0089',
                'location' => '2ND FLR OCAP',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2026-04-10',
                'model' => '65BDL3550Q',
                'screen_size' => '65"',
                'po_number' => 'PO-2026-1033',
                'item_description' => '65" 4K UHD PHILIPS COMMERCIAL DISPLAY (EXHIBITION DEMO)',
                'quantity' => 5,
                'forecasted_quantity' => 8,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
            [
                'category' => 'Philips Service Units',
                'tag_number' => 'SN-PHILIPS-0102, SN-PHILIPS-0103, SN-PHILIPS-0104, SN-PHILIPS-0105',
                'location' => 'MARIKINA',
                'manufacturer' => 'PHILIPS',
                'check_in_date' => '2026-05-20',
                'model' => '32BDL3511Q',
                'screen_size' => '32"',
                'po_number' => 'PO-2026-1145',
                'item_description' => '32" PHILIPS SLIM BEZEL SIGNAGE DISPLAY',
                'quantity' => 14,
                'forecasted_quantity' => 20,
                'status' => 'in_stock',
                'created_by' => $creator?->id,
            ],
        ];

        foreach ($philipsServiceItems as $item) {
            InventoryItem::create($item);
        }
    }
}
