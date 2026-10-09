<?php
$h = fopen('C:\\Users\\marke\\.gemini\\antigravity-ide\\brain\\ff63eb90-28ed-4fcf-8de1-82955ba9a024\\.user_uploaded\\media_1791531955406.csv', 'r');
$i = 0;
while (($r = fgetcsv($h)) !== false) {
    if ($i >= 155 && $i <= 170) {
        echo "Row {$i}: " . json_encode($r) . "\n";
    }
    $i++;
}
