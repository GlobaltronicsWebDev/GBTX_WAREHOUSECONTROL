<?php

$csvPath = 'C:\\Users\\marke\\.gemini\\antigravity-ide\\brain\\ff63eb90-28ed-4fcf-8de1-82955ba9a024\\.user_uploaded\\media_1791531955406.csv';

$rows = [];
if (($handle = fopen($csvPath, 'r')) !== false) {
    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }
    fclose($handle);
}

$startDataIdx = 5;
$items = [];
for ($i = $startDataIdx; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (empty(array_filter($row))) continue;
    $rowJoined = strtolower(implode(' ', $row));
    if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) continue;

    $tag = trim($row[0] ?? '');
    $po = trim($row[2] ?? '');
    $model = trim($row[4] ?? '');
    $desc = trim($row[5] ?? '');
    $loc = trim($row[6] ?? '');

    if (!empty($model) || !empty($desc)) {
        $key = "Tag: '{$tag}', PO: '{$po}', Model: '{$model}', Loc: '{$loc}'";
        if (isset($items[$key])) {
            $items[$key][] = $i;
        } else {
            $items[$key] = [$i];
        }
    }
}

$dups = 0;
foreach ($items as $k => $lineList) {
    if (count($lineList) > 1) {
        $dups++;
        echo "DUPLICATE KEY: {$k} on rows: " . implode(', ', $lineList) . "\n";
    }
}
echo "Total distinct keys with duplicates: {$dups}\n";
