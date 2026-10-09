<?php

$csvPath = 'C:\\Users\\marke\\.gemini\\antigravity-ide\\brain\\ff63eb90-28ed-4fcf-8de1-82955ba9a024\\.user_uploaded\\media_1791531955406.csv';

$rows = [];
if (($handle = fopen($csvPath, 'r')) !== false) {
    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }
    fclose($handle);
}

echo "Total CSV rows parsed by fgetcsv: " . count($rows) . "\n";

// Run header detection
$headerRow1Idx = null;
$headerRow2Idx = null;

foreach ($rows as $idx => $r) {
    $joined = strtolower(implode(' ', $r));
    $score = 0;
    foreach (['tag', 'date', 'po', 'sku', 'manufacturer', 'model', 'description', 'location', 'inventory', 'on-hand'] as $kw) {
        if (str_contains($joined, $kw)) $score++;
    }
    if ($score >= 3) {
        $headerRow1Idx = $idx;
        break;
    }
}

if ($headerRow1Idx !== null && isset($rows[$headerRow1Idx + 1])) {
    $nextJoined = strtolower(implode(' ', $rows[$headerRow1Idx + 1]));
    $subScore = 0;
    foreach (['on-hand', 'total', 'sqm', 'panel', 'qty', 'particular', 'project'] as $kw) {
        if (str_contains($nextJoined, $kw)) $subScore++;
    }
    if ($subScore >= 2) {
        $headerRow2Idx = $headerRow1Idx + 1;
    }
}

echo "Header 1 index: {$headerRow1Idx}, Header 2 index: {$headerRow2Idx}\n";

$columnMap = [];
$h1 = $rows[$headerRow1Idx];
$h2 = $headerRow2Idx !== null ? $rows[$headerRow2Idx] : [];

$currentParent = '';
foreach ($h1 as $colIdx => $colVal) {
    $colClean = preg_replace('/[^a-z0-9]/', '', strtolower((string)$colVal));
    if (!empty($colClean)) {
        $currentParent = $colClean;
    }

    $subVal = isset($h2[$colIdx]) ? preg_replace('/[^a-z0-9]/', '', strtolower((string)$h2[$colIdx])) : '';
    $colKey = null;

    if (in_array($colClean, ['tag', 'tagno', 'tagnumber', 'tagid']) || str_starts_with($colClean, 'tag')) {
        $colKey = 'tag_number';
    } elseif (in_array($colClean, ['datereceived', 'date', 'checkindate'])) {
        $colKey = 'check_in_date';
    } elseif (str_contains($colClean, 'po') || str_contains($colClean, 'sku')) {
        $colKey = 'po_number';
    } elseif (str_contains($colClean, 'manufacturer') || str_contains($colClean, 'brand') || str_contains($colClean, 'mfr')) {
        $colKey = 'manufacturer';
    } elseif (str_contains($colClean, 'model') || str_contains($colClean, 'pixelpitch')) {
        $colKey = 'model';
    } elseif (str_contains($colClean, 'description') || str_contains($colClean, 'item')) {
        $colKey = 'item_description';
    } elseif (str_contains($colClean, 'location') || str_contains($colClean, 'warehouse') || str_contains($colClean, 'facility')) {
        $colKey = 'location';
    } elseif (str_contains($currentParent, 'inventory')) {
        if (str_contains($subVal, 'sqm')) {
            if (str_contains($subVal, 'perpanel') || str_contains($subVal, 'panel')) {
                $colKey = 'per_panel_sqm';
            } else {
                $colKey = 'sqm';
            }
        } elseif (str_contains($subVal, 'total') || str_contains($subVal, 'acu')) {
            $colKey = 'acu_quantity';
        } elseif (str_contains($subVal, 'onhand') || str_contains($subVal, 'qty')) {
            $colKey = 'quantity';
        }
    } elseif (str_contains($currentParent, 'available') || str_contains($colClean, 'available')) {
        if (str_contains($subVal, 'sqm') || str_contains($colClean, 'availablesqm')) {
            $colKey = 'available_sqm';
        } else {
            $colKey = 'forecasted_quantity';
        }
    } elseif (str_contains($currentParent, 'reservation')) {
        if (str_contains($subVal, 'project') || str_contains($subVal, 'detail')) {
            $colKey = 'reservation_project';
        } else {
            $colKey = 'reservation_qty';
        }
    } elseif (str_contains($currentParent, 'history')) {
        if (str_contains($subVal, 'project')) {
            $colKey = 'history_project';
        } else {
            $colKey = 'history_qty';
        }
    } elseif (str_contains($currentParent, 'original') || str_contains($colClean, 'original')) {
        $colKey = 'original_quantity';
    } elseif (str_contains($currentParent, 'status') || str_contains($colClean, 'status')) {
        if (str_contains($subVal, 'particular')) {
            $colKey = 'status_particular';
        } else {
            $colKey = 'status';
        }
    } elseif (str_contains($colClean, 'screensize') || str_contains($colClean, 'size')) {
        $colKey = 'screen_size';
    } elseif (str_contains($colClean, 'category')) {
        $colKey = 'category';
    } elseif (str_contains($colClean, 'remark') || str_contains($colClean, 'note')) {
        $colKey = 'remarks';
    }

    if (!$colKey && !empty($subVal)) {
        if ($subVal === 'onhand') $colKey = 'quantity';
        elseif (str_contains($subVal, 'totalonhand')) $colKey = 'acu_quantity';
        elseif (str_contains($subVal, 'totalsqm') || str_contains($subVal, 'totalavailablesqm')) $colKey = 'sqm';
        elseif (str_contains($subVal, 'perpanelsqm')) $colKey = 'per_panel_sqm';
        elseif ($subVal === 'qty') $colKey = 'quantity';
        elseif ($subVal === 'sqm') $colKey = 'sqm';
    }

    $columnMap[$colIdx] = $colKey;
}

echo "Column map:\n";
print_r($columnMap);

$startDataIdx = ($headerRow2Idx ?? $headerRow1Idx) + 1;
$mainItemRows = 0;
$subRows = 0;
$emptyRows = 0;
$summaryRows = 0;
$skippedRows = [];

for ($i = $startDataIdx; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (empty(array_filter($row))) {
        $emptyRows++;
        continue;
    }

    $rowJoined = strtolower(implode(' ', $row));
    if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) {
        $summaryRows++;
        continue;
    }

    $rowData = [];
    foreach ($row as $cIdx => $rawVal) {
        $key = $columnMap[$cIdx] ?? null;
        if ($key) {
            $rowData[$key] = $rawVal;
        }
    }

    $model = trim($rowData['model'] ?? '');
    $desc = trim($rowData['item_description'] ?? '');

    if (!empty($model) || !empty($desc)) {
        $mainItemRows++;
    } else {
        $subRows++;
        // Check if there is data in this subRow
        $hasData = false;
        foreach ($rowData as $k => $v) {
            if (trim((string)$v) !== '') $hasData = true;
        }
        if (!$hasData) {
            $skippedRows[] = $i;
        }
    }
}

echo "Total data rows processed: " . (count($rows) - $startDataIdx) . "\n";
echo "Main item rows (with model or desc): {$mainItemRows}\n";
echo "Sub rows (reservations/history/location): {$subRows}\n";
echo "Empty rows skipped: {$emptyRows}\n";
echo "Summary rows skipped: {$summaryRows}\n";
