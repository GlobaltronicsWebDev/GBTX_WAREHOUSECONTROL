<?php

namespace App\Support;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventorySpreadsheetImporter
{
    private const HEADERS = [
        ['TAG #', 'DATE RECEIVED', 'PO / SKU NO.', 'MANUFACTURER', 'MODEL / PIXEL PITCH', 'ITEM DESCRIPTION', 'LOCATION', 'INVENTORY', '', '', '', 'AVAILABLE QTY', '', 'RESERVATION', '', 'HISTORY', '', 'ORIGINAL QTY', 'STATUS', '', 'REMARKS'],
        ['', '', '', '', '', '', '', 'ON-HAND', 'TOTAL ON-HAND', 'PER PANEL SQM', 'TOTAL AVAILABLE SQM', 'QTY', 'SQM', 'QTY', 'PROJECT DETAILS', 'QTY', 'PROJECT', '', 'QTY', 'PARTICULAR', ''],
    ];

    private const FIELDS = [
        'tag_number', 'date_received', 'po_sku_number', 'manufacturer', 'model_pixel_pitch', 'item_description',
        'location', 'on_hand', 'total_on_hand', 'per_panel_sqm', 'total_available_sqm', 'available_qty',
        'available_sqm', 'reservation_qty', 'reservation_project_details', 'history_qty', 'history_project',
        'original_qty', 'status_qty', 'status_particular', 'remarks',
    ];

    private const LABELS = [
        'tag_number' => 'Tag #', 'date_received' => 'Date received', 'po_sku_number' => 'PO / SKU',
        'manufacturer' => 'Manufacturer', 'model_pixel_pitch' => 'Model / pixel pitch', 'location' => 'Location',
        'on_hand' => 'On-hand', 'total_on_hand' => 'Total on-hand', 'per_panel_sqm' => 'Per panel SQM',
        'total_available_sqm' => 'Total available SQM', 'available_qty' => 'Available qty',
        'available_sqm' => 'Available SQM', 'reservation_qty' => 'Reservation qty',
        'reservation_project_details' => 'Reservation project details', 'original_qty' => 'Original qty',
        'status_qty' => 'Status qty', 'status_particular' => 'Status particular', 'remarks' => 'Remarks',
    ];

    public function matchesTitle(array|false $row): bool
    {
        return is_array($row) && count($row) === 21 && mb_check_encoding($row, 'UTF-8')
            && str_starts_with($this->normalize($row[0] ?? ''), 'CENTRALIZED LED INVENTORY')
            && $this->isBlank(array_slice($row, 1));
    }

    /**
     * @param  resource  $stream
     */
    public function import($stream, User $user, InventoryItemValidator $validator, InventoryItemWriter $writer): int
    {
        for ($record = 2; $record <= 5; $record++) {
            $row = fgetcsv($stream, 0, ',', '"', '');
            if ($row === false || ! mb_check_encoding($row, 'UTF-8')
                || ($record <= 3 && ! $this->isBlank($row))
                || ($record >= 4 && array_map($this->normalize(...), $row) !== self::HEADERS[$record - 4])) {
                $this->fail($record, 'Invalid spreadsheet headers.');
            }
        }

        $count = 0;
        $current = null;
        $currentRecord = 0;
        $finished = false;
        $record = 5;
        $warehouses = WarehouseLocation::query()->get();
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $record++;
            if ($this->isBlank($row)) {
                continue;
            }
            if ($finished || count($row) !== 21 || ! mb_check_encoding($row, 'UTF-8') || $record > 5005) {
                $this->fail($record, 'Invalid spreadsheet row, encoding, or record limit.');
            }
            $data = array_combine(self::FIELDS, array_map(fn ($value) => trim($value ?? ''), $row));
            if ($this->normalize($data['item_description']) === 'TOTAL COUNT') {
                if (array_filter($data, fn ($value, $key) => $value !== '' && ! in_array($key, ['item_description', 'on_hand'], true), ARRAY_FILTER_USE_BOTH)) {
                    $this->fail($record, 'Unexpected data in total row.');
                }
                $this->number($data['on_hand'], false, $record);
                $finished = true;

                continue;
            }
            if ($data['item_description'] !== '') {
                if ($current !== null) {
                    $this->save($current, $currentRecord, $user, $validator, $writer);
                    $count++;
                }
                $currentRecord = $record;
                $current = $this->item($data, $record, $warehouses);
            } else {
                if ($current === null) {
                    $this->fail($record, 'Continuation row has no preceding item.');
                }
                $current['histories'][] = $this->history($data, $record, true);
            }
        }
        if ($current !== null) {
            $this->save($current, $currentRecord, $user, $validator, $writer);
            $count++;
        }
        if ($count === 0) {
            $this->fail(5, 'The CSV file does not contain any inventory records.');
        }

        return $count;
    }

    /**
     * @param  Collection<int, WarehouseLocation>  $warehouses
     */
    private function item(array $data, int $record, Collection $warehouses): array
    {
        $location = $this->normalize($data['location']);
        [$name, $subLocation] = match ($location) {
            'MARIKINA', 'MARIKINA WAREHOUSE' => ['Marikina', null],
            'GLOBALTRONICS', 'GLOBALTRONICS WAREHOUSE' => ['Globaltronics', null],
            'A JUAN - 1ST FLR', 'A JUAN MAIN 1ST FLOOR' => ['A Juan', 'A JUAN MAIN 1ST FLOOR'],
            'A JUAN MAIN 3RD FLOOR' => ['A Juan', 'A JUAN MAIN 3RD FLOOR'],
            'CLARK OFFICE' => ['Clark Office', null],
            default => $this->fail($record, 'Unknown warehouse location.'),
        };
        $matches = $warehouses->filter(fn ($warehouse) => $this->normalize($warehouse->name) === $this->normalize($name));
        if ($matches->count() !== 1) {
            $this->fail($record, "Warehouse {$name} is missing or ambiguous.");
        }
        $data['warehouse_location_id'] = $matches->first()->id;
        $data['sub_location'] = $subLocation;
        $data['histories'] = [];
        if ($data['history_qty'] !== '' || $data['history_project'] !== '') {
            $data['histories'][] = $this->history($data, $record, false);
        }
        $date = $data['date_received'];
        if ($date !== '') {
            $parsed = $this->singleDate($date);
            if ($parsed === null) {
                $parts = preg_split('/\s*(?:\r?\n|&)\s*/', $date, -1, PREG_SPLIT_NO_EMPTY);
                $multiple = count($parts) > 1 && count(array_filter($parts, fn ($part) => $this->singleDate($part) !== null)) === count($parts);
                $range = preg_match('/^(\d{1,2})[\/-](\d{1,2})[-\/](\d{1,2})[\/-](\d{2}|\d{4})$/', $date, $matches)
                    && $this->singleDate("{$matches[1]}/{$matches[2]}/{$matches[4]}") !== null
                    && $this->singleDate("{$matches[1]}/{$matches[3]}/{$matches[4]}") !== null
                    && (int) $matches[2] <= (int) $matches[3];
                if (! $multiple && ! $range) {
                    $this->fail($record, 'Invalid received date.');
                }
                $data['histories'][] = ['quantity' => null, 'project' => 'Date received: '.$date];
            }
            $data['date_received'] = $parsed;
        } else {
            $data['date_received'] = null;
        }
        foreach (['on_hand', 'total_on_hand', 'available_qty', 'reservation_qty', 'original_qty', 'status_qty'] as $field) {
            $data[$field] = $this->number($data[$field], false, $record);
        }
        foreach (['per_panel_sqm', 'total_available_sqm', 'available_sqm'] as $field) {
            $data[$field] = $this->number($data[$field], true, $record);
        }
        $data['available_qty'] = max(0, $data['available_qty']);
        $data['available_sqm'] = max(0, $data['available_sqm']);
        $data['per_panel_sqm'] = number_format(round((float) $data['per_panel_sqm'], 2), 2, '.', '');
        foreach (['tag_number', 'po_sku_number', 'manufacturer', 'model_pixel_pitch', 'reservation_project_details', 'status_particular', 'remarks'] as $field) {
            $data[$field] = $data[$field] === '' ? null : $data[$field];
        }

        return $data;
    }

    private function history(array $data, int $record, bool $continuation): array
    {
        $quantity = $this->number($data['history_qty'], false, $record);
        $parts = [];
        if ($data['history_project'] !== '') {
            $parts[] = $data['history_project'];
        }
        if ($continuation) {
            foreach (self::LABELS as $field => $label) {
                if ($data[$field] !== '') {
                    $parts[] = $label.': '.$data[$field];
                }
            }
        }

        return ['quantity' => $quantity, 'project' => $parts === [] ? null : implode("\n", $parts)];
    }

    private function save(array $data, int $record, User $user, InventoryItemValidator $validator, InventoryItemWriter $writer): void
    {
        try {
            Validator::make($data, [
                'on_hand' => ['integer', 'min:0', 'max:4294967295'],
                'total_on_hand' => ['integer', 'min:0', 'max:4294967295'],
                'available_qty' => ['integer', 'min:0', 'max:4294967295'],
                'reservation_qty' => ['integer', 'min:0', 'max:4294967295'],
                'original_qty' => ['integer', 'min:0', 'max:4294967295'],
                'per_panel_sqm' => ['numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                'total_available_sqm' => ['numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
                'available_sqm' => ['numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            ])->validate();
            $validated = $validator->validate($data);
        } catch (ValidationException $exception) {
            $this->fail($record, $exception->validator->errors()->first());
        }
        $writer->save(new InventoryItem(['created_by' => $user->id]), $validated);
    }

    private function number(string $value, bool $sqm, int $record): string
    {
        if ($value === '') {
            return '0';
        }
        $unit = $sqm ? 'sqm' : 'pcs?';
        if (! preg_match('/^([+-]?(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d+)?)\s*(?:'.$unit.')?$/i', $value, $matches)) {
            $this->fail($record, 'Invalid quantity or SQM value.');
        }

        return str_replace(',', '', $matches[1]);
    }

    private function singleDate(string $value): ?string
    {
        if (! preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2}|\d{4})$/', trim($value), $matches)) {
            return null;
        }
        $year = (int) $matches[3];
        if (strlen($matches[3]) === 2) {
            $year += 2000;
        }
        if (! checkdate((int) $matches[1], (int) $matches[2], $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $matches[1], $matches[2]);
    }

    private function normalize(?string $value): string
    {
        return mb_strtoupper(preg_replace('/\s+/u', ' ', trim($value ?? '')));
    }

    private function isBlank(array $row): bool
    {
        return ! array_filter($row, fn ($value) => trim($value ?? '') !== '');
    }

    private function fail(int $record, string $message): never
    {
        throw ValidationException::withMessages(['csv_file' => "CSV record {$record}: {$message}"]);
    }
}
