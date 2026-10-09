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
$h1Idx = 3;
$h2Idx = 4;
$startDataIdx = 5;

// Let's inspect subrows that have something other than reservation_qty, reservation_project, history_qty, history_project
for ($i = $startDataIdx; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (empty(array_filter($row))) continue;
    $rowJoined = strtolower(implode(' ', $row));
    if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) continue;

    $tag = trim($row[0] ?? '');
    $date = trim($row[1] ?? '');
    $po = trim($row[2] ?? '');
    $mfg = trim($row[3] ?? '');
    $model = trim($row[4] ?? '');
    $desc = trim($row[5] ?? '');
    $loc = trim($row[6] ?? '');
    $qty = trim($row[7] ?? '');
    $totalOnHand = trim($row[8] ?? '');
    $availQty = trim($row[11] ?? '');

    if (empty($model) && empty($desc)) {
        // If this row has a location, or po, or date, or qty!
        if (!empty($loc) || !empty($qty) || !empty($po) || !empty($tag) || !empty($mfg)) {
            echo "Row {$i} has no model/desc but has: tag='{$tag}', date='{$date}', po='{$po}', mfg='{$mfg}', loc='{$loc}', qty='{$qty}', totalOnHand='{$totalOnHand}'\n";
        }
    }
}
