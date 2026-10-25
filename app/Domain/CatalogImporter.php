<?php

namespace App\Domain;

use App\Models\CatalogImport;
use App\Models\Category;
use App\Models\Offer;
use App\Models\ProductProposal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class CatalogImporter
{
    public function scan(CatalogImport $import): void
    {
        app(ModuleManager::class)->transaction('catalog', function () use ($import) {
            $locked = CatalogImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, ['uploaded', 'needs_correction'])) {
                throw ValidationException::withMessages(['import' => 'A staged import cannot be scanned again.']);
            }$this->readCsv($locked);
        }, $import->vendor_id);
    }

    private function readCsv(CatalogImport $import): void
    {
        if (! $import->rights_confirmed || ! str_starts_with($import->path, 'tenants/'.app(TenantContext::class)->id().'/imports/') || str_contains($import->path, '..')) {
            throw ValidationException::withMessages(['file' => 'Confirm rights and use private tenant import storage.']);
        }
        if (Storage::disk('local')->size($import->path) > 2097152) {
            throw ValidationException::withMessages(['file' => 'CSV imports are limited to 2 MB.']);
        }
        $stream = Storage::disk('local')->readStream($import->path);
        $header = fgetcsv($stream, 0, ',', '"', '');
        $header && isset($header[0]) && $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $required = ['name', 'description', 'category_slug', 'sku', 'price_minor', 'quantity'];
        if (! $header || array_diff($required, $header) || count($header) !== count(array_unique($header))) {
            if (is_resource($stream)) {
                fclose($stream);
            }throw ValidationException::withMessages(['file' => 'Required unique CSV headers: '.implode(', ', $required)]);
        }
        $rows = [];
        $errors = [];
        $number = 1;
        $seen = [];
        while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $number++;
            if ($number > 1001) {
                $errors[] = 'Maximum 1000 data rows.';
                break;
            }if (count($values) !== count($header)) {
                $errors[] = 'Row '.$number.': wrong column count.';

                continue;
            }$row = array_combine($header, $values);
            $validator = Validator::make($row, ['name' => 'required|string|max:255', 'description' => 'required|string|max:10000', 'category_slug' => 'required|string|max:100', 'sku' => 'required|string|max:100', 'price_minor' => 'required|integer|min:0|max:1000000000', 'quantity' => 'required|integer|min:0|max:1000000000']);
            $category = Category::where('slug', $row['category_slug'] ?? '')->first();
            if ($validator->fails() || ! $category || isset($seen[$row['sku'] ?? '']) || Offer::where('sku', $row['sku'] ?? '')->exists()) {
                $errors[] = 'Row '.$number.': '.($validator->errors()->first() ?: (! $category ? 'Unknown category.' : 'Duplicate SKU in this file.'));

                continue;
            }$seen[$row['sku']] = true;
            $rows[] = ['name' => $row['name'], 'description' => $row['description'], 'category_id' => $category->id, 'sku' => $row['sku'], 'price_minor' => (int) $row['price_minor'], 'initial_quantity' => (int) $row['quantity']];
        }
        fclose($stream);
        if (! $rows && ! $errors) {
            $errors[] = 'No product data rows were found.';
        }$import->update(['rows' => $rows, 'errors' => $errors, 'row_count' => count($rows), 'status' => $errors ? 'needs_correction' : 'review_ready']);
        Audit::record('catalog.import_scanned', (string) $import->id, ['rows' => count($rows), 'errors' => count($errors)]);
    }

    public function stage(CatalogImport $import): void
    {
        app(ModuleManager::class)->transaction('catalog', function () use ($import) {
            $import = CatalogImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
            if ($import->status === 'staged') {
                return;
            }if ($import->status !== 'review_ready' || ! $import->rights_confirmed) {
                throw ValidationException::withMessages(['import' => 'Scan and correct the entire file before staging.']);
            }foreach ($import->rows as $row) {
                ProductProposal::create($row + ['vendor_id' => $import->vendor_id, 'warehouse_id' => $import->warehouse_id, 'rights_confirmed' => true, 'status' => 'pending']);
            }$import->update(['status' => 'staged']);
            Audit::record('catalog.import_staged',(string) $import->id);
        }, $import->vendor_id);
    }
}
