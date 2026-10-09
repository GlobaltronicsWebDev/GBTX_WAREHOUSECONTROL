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

$classified = [];
$lastItemIdx = null;

for ($i = $startDataIdx; $i < count($rows); $i++) {
    $row = $rows[$i];
    $nonEmpty = array_filter($row, fn($x) => trim((string)$x) !== '');
    if (empty($nonEmpty)) {
        $classified[$i] = ['type' => 'EMPTY'];
        continue;
    }

    $rowJoined = strtolower(implode(' ', $row));
    if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) {
        $classified[$i] = ['type' => 'SUMMARY'];
        continue;
    }

    $tag = $cleanStr($row[0] ?? '');
    $po = $cleanStr($row[2] ?? '');
    $mfg = $cleanStr($row[3] ?? '');
    $model = $cleanStr($row[4] ?? '');
    $desc = $cleanStr($row[5] ?? '');
    $location = $cleanStr($row[6] ?? '');

    $onHand = $parseNum($row[7] ?? null);
    $totalOnHand = $parseNum($row[8] ?? null);
    $resQty = $parseNum($row[13] ?? null);
    $resProj = $cleanStr($row[14] ?? '');
    $histQty = $parseNum($row[15] ?? null);
    $histProj = $cleanStr($row[16] ?? '');

    $isItem = false;
    if (!empty($model) || !empty($desc)) {
        $isItem = true;
    } elseif ($lastItemIdx !== null && (!empty($location) || ($onHand !== null && $onHand > 0) || !empty($po))) {
        // Inherits parent model & desc
        $isItem = true;
    }

    if ($isItem) {
        $classified[$i] = [
            'type' => 'ITEM',
            'model' => $model,
            'desc' => $desc,
            'loc' => $location,
            'qty' => $onHand,
        ];
        $lastItemIdx = $i;
    } else {
        // Pure sub-row (reservation or history)
        $classified[$i] = [
            'type' => 'SUBROW',
            'parent' => $lastItemIdx,
            'resQty' => $resQty,
            'resProj' => $resProj,
            'histQty' => $histQty,
            'histProj' => $histProj,
        ];
    }
}

$counts = ['ITEM' => 0, 'SUBROW' => 0, 'EMPTY' => 0, 'SUMMARY' => 0];
foreach ($classified as $c) {
    $counts[$c['type']]++;
}

print_r($counts);

// Are there any subrows where resQty, resProj, histQty, histProj are ALL empty?
foreach ($classified as $i => $c) {
    if ($c['type'] === 'SUBROW') {
        if (empty($c['resQty']) && empty($c['resProj']) && empty($c['histQty']) && empty($c['histProj'])) {
            echo "UNHANDLED SUBROW at {$i}: " . json_encode($rows[$i]) . "\n";
        }
    }
}
