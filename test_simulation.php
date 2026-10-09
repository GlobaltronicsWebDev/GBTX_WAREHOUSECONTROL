<?php

$csvPath = 'C:\\Users\\marke\\.gemini\\antigravity-ide\\brain\\ff63eb90-28ed-4fcf-8de1-82955ba9a024\\.user_uploaded\\media_1791531955406.csv';

$rows = [];
if (($handle = fopen($csvPath, 'r')) !== false) {
    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }
    fclose($handle);
}

// Find header
$headerRow1Idx = 3;
$headerRow2Idx = 4;
$startDataIdx = 5;

$cleanStr = function ($val) {
    if ($val === null) return '';
    $val = preg_replace('/[\x{FEFF}\x{200B}]/u', '', (string)$val);
    return trim(preg_replace('/\s+/', ' ', $val));
};

$parseNum = function ($val, $isFloat = false) {
    if ($val === null || $val === '') return null;
    $val = trim((string)$val);
    if ($val === '' || $val === '—' || $val === '-') return null;
    $cleaned = preg_replace('/[^\d\.\-]/', '', $val);
    if ($cleaned === '' || $cleaned === '-') return null;
    return $isFloat ? (float)$cleaned : (int)$cleaned;
};

// Column Map
$columnMap = [
    0 => 'tag_number',
    1 => 'check_in_date',
    2 => 'po_number',
    3 => 'manufacturer',
    4 => 'model',
    5 => 'item_description',
    6 => 'location',
    7 => 'quantity',
    8 => 'acu_quantity',
    9 => 'per_panel_sqm',
    10 => 'sqm',
    11 => 'forecasted_quantity',
    12 => 'available_sqm',
    13 => 'reservation_qty',
    14 => 'reservation_project',
    15 => 'history_qty',
    16 => 'history_project',
    17 => 'original_quantity',
    18 => 'status',
    19 => 'status_particular',
    20 => 'remarks',
];

$items = [];
$lastItem = null;

for ($i = $startDataIdx; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (empty(array_filter($row))) continue;
    $rowJoined = strtolower(implode(' ', $row));
    if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) continue;

    $rowData = [];
    foreach ($row as $cIdx => $rawVal) {
        $key = $columnMap[$cIdx] ?? null;
        if ($key) {
            $rowData[$key] = $rawVal;
        }
    }

    $model = $cleanStr($rowData['model'] ?? '');
    $desc = $cleanStr($rowData['item_description'] ?? '');
    $po = $cleanStr($rowData['po_number'] ?? '');
    $mfg = $cleanStr($rowData['manufacturer'] ?? '');
    $location = $cleanStr($rowData['location'] ?? '');

    $onHand = $parseNum($rowData['quantity'] ?? null);
    $totalOnHand = $parseNum($rowData['acu_quantity'] ?? null);
    $resQty = $parseNum($rowData['reservation_qty'] ?? null);
    $resProject = $cleanStr($rowData['reservation_project'] ?? '');
    $histQty = $parseNum($rowData['history_qty'] ?? null);
    $histProject = $cleanStr($rowData['history_project'] ?? '');

    // Inherit from preceding model if this row has its own location or stock quantity
    if (empty($model) && empty($desc) && $lastItem !== null) {
        if (!empty($location) || ($onHand !== null && $onHand > 0) || !empty($po)) {
            $model = $lastItem['model'];
            $desc = $lastItem['item_description'];
            if (empty($mfg)) $mfg = $lastItem['manufacturer'];
            if (empty($location)) $location = $lastItem['location'];
            if (empty($po)) $po = $lastItem['po_number'];
        }
    }

    if (!empty($model) || !empty($desc)) {
        $item = [
            'row_idx' => $i,
            'tag' => $rowData['tag_number'] ?? '',
            'po' => $po,
            'manufacturer' => $mfg ?: 'UNILUMIN',
            'model' => $model ?: substr($desc, 0, 50),
            'item_description' => $desc ?: $model,
            'location' => $location ?: 'Globaltronics',
            'quantity' => $onHand ?? 0,
            'reservations' => [],
            'history' => [],
        ];
        if ($resQty || !empty($resProject)) {
            $item['reservations'][] = ['qty' => $resQty, 'proj' => $resProject];
        }
        if ($histQty || !empty($histProject)) {
            $item['history'][] = ['qty' => $histQty, 'proj' => $histProject];
        }
        $items[] = $item;
        $lastItem = &$items[count($items) - 1];
    } elseif ($lastItem !== null) {
        if ($resQty || !empty($resProject)) {
            $lastItem['reservations'][] = ['qty' => $resQty, 'proj' => $resProject];
        }
        if ($histQty || !empty($histProject)) {
            $lastItem['history'][] = ['qty' => $histQty, 'proj' => $histProject];
        }
    }
}

echo "Total items recognized: " . count($items) . "\n";

// Check if items have unique identity:
$keys = [];
foreach ($items as $idx => $it) {
    // Unique key: tag if present, else po + model + desc + location
    $k = $it['tag'] ? "TAG:{$it['tag']}" : "PO:{$it['po']}|M:{$it['model']}|D:{$it['item_description']}|L:{$it['location']}";
    if (isset($keys[$k])) {
        echo "Duplicate item key: {$k} (rows {$keys[$k]} and {$it['row_idx']})\n";
    } else {
        $keys[$k] = $it['row_idx'];
    }
}
echo "Total unique item identities: " . count($keys) . "\n";
