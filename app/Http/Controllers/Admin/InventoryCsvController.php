<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\WarehouseLocation;
use App\Support\InventoryItemValidator;
use App\Support\InventoryItemWriter;
use App\Support\InventorySpreadsheetImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryCsvController extends Controller
{
    private const LEGACY_HEADERS = [
        'id', 'tag_number', 'date_received', 'po_sku_number', 'manufacturer', 'model_pixel_pitch',
        'item_description', 'warehouse_location_id', 'location', 'sub_location', 'on_hand', 'total_on_hand',
        'per_panel_sqm', 'total_available_sqm', 'available_qty', 'available_sqm', 'reservation_qty',
        'reservation_project_details', 'histories', 'original_qty', 'status_qty', 'status_particular',
        'remarks', 'csv_format',
    ];

    private const COLUMNS = [
        'tag_number' => ['TAG #', ''],
        'date_received' => ['DATE RECEIVED', ''],
        'po_sku_number' => ['PO / SKU No.', ''],
        'manufacturer' => ['MANUFACTURER', ''],
        'model_pixel_pitch' => ['MODEL / PIXEL PITCH', ''],
        'item_description' => ['ITEM DESCRIPTION', ''],
        'location' => ['LOCATION', ''],
        'on_hand' => ['INVENTORY', 'ON-HAND'],
        'total_on_hand' => ['', 'TOTAL ON-HAND'],
        'per_panel_sqm' => ['', 'PER PANEL SQM'],
        'total_available_sqm' => ['', 'TOTAL AVAILABLE SQM'],
        'available_qty' => ['AVAILABLE QTY', 'QTY'],
        'available_sqm' => ['', 'SQM'],
        'reservation_qty' => ['RESERVATION', 'QTY'],
        'reservation_project_details' => ['', 'PROJECT DETAILS'],
        'history_qty' => ['HISTORY', 'QTY'],
        'history_project' => ['', 'PROJECT'],
        'original_qty' => ['ORIGINAL QTY', ''],
        'status_qty' => ['STATUS', 'QTY'],
        'status_particular' => ['', 'PARTICULAR'],
        'remarks' => ['REMARKS', ''],
        'id' => ['ITEM ID', ''],
        'warehouse_location_id' => ['WAREHOUSE ID', ''],
        'sub_location' => ['SUB-LOCATION', ''],
        'additional_histories' => ['ADDITIONAL HISTORIES', ''],
        'csv_format' => ['CSV FORMAT', ''],
    ];

    private const TEXT_FIELDS = [
        'tag_number', 'po_sku_number', 'manufacturer', 'model_pixel_pitch', 'item_description',
        'location', 'sub_location', 'reservation_project_details', 'history_project', 'status_particular', 'remarks',
    ];

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');
            if ($stream === false) {
                throw new RuntimeException('Unable to open CSV output.');
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, array_column(self::COLUMNS, 0), ',', '"', '');
            fputcsv($stream, array_column(self::COLUMNS, 1), ',', '"', '');
            InventoryItem::query()->with(['histories', 'warehouseLocation'])->orderBy('id')->chunkById(200, function ($items) use ($stream): void {
                foreach ($items as $item) {
                    $data = $item->only(array_keys(self::COLUMNS));
                    $data['date_received'] = $item->date_received?->format('Y-m-d');
                    $data['location'] = $item->warehouseLocation?->name ?? $item->location;
                    $data['history_qty'] = $item->histories->first()?->quantity;
                    $data['history_project'] = $item->histories->first()?->project;
                    $data['additional_histories'] = json_encode($item->histories->skip(1)->map(fn ($history) => [
                        'quantity' => $history->quantity,
                        'project' => $history->project,
                    ])->values()->all(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    $data['csv_format'] = 'spreadsheet-safe-v1';
                    foreach (self::TEXT_FIELDS as $field) {
                        $value = $data[$field] ?? '';
                        if (is_string($value) && preg_match('/^(?:[\'\t\r\n]|[ \t\r\n]*[=+\-@])/', $value)) {
                            $data[$field] = "'".$value;
                        }
                    }
                    fputcsv($stream, array_map(fn ($field) => $data[$field] ?? '', array_keys(self::COLUMNS)), ',', '"', '');
                }
            });
            fclose($stream);
        }, 'inventory.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, InventoryItemValidator $itemValidator, InventoryItemWriter $writer, InventorySpreadsheetImporter $spreadsheetImporter): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $stream = fopen($request->file('csv_file')->getRealPath(), 'r');
        if ($stream === false) {
            throw new RuntimeException('Unable to read the uploaded CSV file.');
        }

        try {
            if (fread($stream, 3) !== "\xEF\xBB\xBF") {
                rewind($stream);
            }
            $header = fgetcsv($stream, 0, ',', '"', '');
            $legacy = $header === self::LEGACY_HEADERS;
            $spreadsheet = $spreadsheetImporter->matchesTitle($header);
            if (! $legacy && ! $spreadsheet && ($header !== array_column(self::COLUMNS, 0) || fgetcsv($stream, 0, ',', '"', '') !== array_column(self::COLUMNS, 1))) {
                throw ValidationException::withMessages(['csv_file' => 'Invalid CSV columns. Download the inventory CSV and use its column headers.']);
            }

            $count = DB::transaction(function () use ($stream, $request, $itemValidator, $writer, $legacy, $spreadsheet, $spreadsheetImporter): int {
                if ($spreadsheet) {
                    return $spreadsheetImporter->import($stream, $request->user(), $itemValidator, $writer);
                }
                $record = $legacy ? 1 : 2;
                $imported = 0;
                $seenIds = [];
                $fields = $legacy ? self::LEGACY_HEADERS : array_keys(self::COLUMNS);
                while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
                    $record++;
                    if ($row === [null]) {
                        continue;
                    }
                    if ($imported >= 5000 || count($row) !== count($fields)) {
                        throw ValidationException::withMessages(['csv_file' => "CSV record {$record} has an invalid number of columns or exceeds the 5,000-record limit."]);
                    }
                    if (! mb_check_encoding($row, 'UTF-8')) {
                        throw ValidationException::withMessages(['csv_file' => "CSV record {$record} must use UTF-8 encoding."]);
                    }
                    $data = array_combine($fields, $row);
                    if (! in_array($data['csv_format'], ['', 'spreadsheet-safe-v1'], true)) {
                        throw ValidationException::withMessages(['csv_file' => "CSV record {$record} has an unsupported format."]);
                    }
                    if ($data['csv_format'] === 'spreadsheet-safe-v1') {
                        foreach (self::TEXT_FIELDS as $field) {
                            if (isset($data[$field]) && preg_match('/^\'(?:[\'\t\r\n]|[ \t\r\n]*[=+\-@])/', $data[$field])) {
                                $data[$field] = substr($data[$field], 1);
                            }
                        }
                    }
                    $data = array_map(fn ($value) => $value === '' ? null : $value, $data);
                    try {
                        $histories = json_decode(($legacy ? $data['histories'] : $data['additional_histories']) ?? '[]', true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $exception) {
                        throw ValidationException::withMessages(['csv_file' => "CSV record {$record} has invalid project history JSON."]);
                    }
                    if (! is_array($histories) || ! array_is_list($histories)) {
                        throw ValidationException::withMessages(['csv_file' => "CSV record {$record} must have a list of project histories."]);
                    }
                    if (! $legacy && ($data['history_qty'] !== null || $data['history_project'] !== null)) {
                        array_unshift($histories, ['quantity' => $data['history_qty'], 'project' => $data['history_project']]);
                    }
                    $data['histories'] = array_map(
                        fn ($history) => is_array($history) ? array_map(fn ($value) => $value === '' ? null : $value, $history) : $history,
                        $histories,
                    );

                    try {
                        Validator::make($data, [
                            'id' => ['nullable', 'integer', 'min:1'],
                            'warehouse_location_id' => ['nullable', 'integer', 'min:1'],
                            'on_hand' => ['required', 'integer', 'min:0', 'max:4294967295'],
                            'total_on_hand' => ['required', 'integer', 'min:0', 'max:4294967295'],
                            'available_qty' => ['required', 'integer', 'min:0', 'max:4294967295'],
                            'reservation_qty' => ['required', 'integer', 'min:0', 'max:4294967295'],
                            'original_qty' => ['required', 'integer', 'min:0', 'max:4294967295'],
                            'status_qty' => ['required', 'integer', 'min:-4294967295', 'max:4294967295'],
                            'per_panel_sqm' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                            'total_available_sqm' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
                            'available_sqm' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
                        ])->validate();
                        $item = null;
                        if ($data['id'] !== null) {
                            $id = (int) $data['id'];
                            if (isset($seenIds[$id])) {
                                throw ValidationException::withMessages(['id' => 'Duplicate item ID.']);
                            }
                            $seenIds[$id] = true;
                            $item = InventoryItem::query()->lockForUpdate()->find($id);
                            if ($item === null) {
                                throw ValidationException::withMessages(['id' => 'Item ID was not found or has been deleted.']);
                            }
                        }
                        if (! $legacy && $data['warehouse_location_id'] !== null && $data['location'] !== null) {
                            $warehouse = WarehouseLocation::query()->find($data['warehouse_location_id']);
                            if ($warehouse !== null && $data['location'] !== $warehouse->name) {
                                throw ValidationException::withMessages(['location' => 'Location must match the selected warehouse ID.']);
                            }
                        }
                        $validated = $itemValidator->validate($data, $item, true);
                    } catch (ValidationException $exception) {
                        throw ValidationException::withMessages(['csv_file' => "CSV record {$record}: ".$exception->validator->errors()->first()]);
                    }

                    $item ??= new InventoryItem(['created_by' => $request->user()->id]);
                    $writer->save($item, $validated);
                    $imported++;
                }
                if ($imported === 0) {
                    throw ValidationException::withMessages(['csv_file' => 'The CSV file does not contain any inventory records.']);
                }

                return $imported;
            });
        } finally {
            fclose($stream);
        }

        return redirect()->route('admin.inventory.dashboard')->with('status', "Imported {$count} inventory item(s).");
    }
}
