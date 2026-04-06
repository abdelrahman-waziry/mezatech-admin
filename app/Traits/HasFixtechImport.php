<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Shared import logic for FixTech pricing Excel files.
 * Enhanced with Smart Subcategory Normalization to prevent clutter.
 */
trait HasFixtechImport
{
    protected function setSyncProgress($percent, $status = null)
    {
        \Illuminate\Support\Facades\Cache::put('fixtech_sync_progress', [
            'percent' => $percent,
            'status' => $status,
            'updated_at' => now()->toDateTimeString(),
        ], 300);
    }

    protected function getRepairSheetMap(): array
    {
        return [
            'Etisalat Offers'               => ['iPhone',         true],
            'iPhone (Screens & Battery)'    => ['iPhone',         false],
            'iPhone (Back Glass & Housing)' => ['iPhone',         false],
            'iPhone (Small Spare Parts)'    => ['iPhone',         false],
            'iPhone (Hardware Issues)'      => ['iPhone',         false],
            'iPad (Spare Parts)'            => ['iPad',           false],
            'iPad (Hardware)'               => ['iPad',           false],
            'Macbook (Screens)'             => ['MacBook',        false],
            'Macbook (Spare Parts)'         => ['MacBook',        false],
            'Macbook (Hardware)'            => ['MacBook',        false],
            'Apple Watch'                   => ['Apple Watch',    false],
            'Airpods'                       => ['AirPods',        false],
            'Samsung'                       => ['Samsung',        false],
            'Google Pixel'                  => ['Google Pixel',   false],
            'Gaming Consoles'               => ['Gaming Console', false],
            'Laptops General'               => ['Laptops',        false],
            'Laptops Spare Parts'           => ['Laptops',        false],
            'Other Services'                => ['Other Services', false],
        ];
    }

    protected function getAccessorySheetMap(): array
    {
        return [
            'Protection'        => 'Protection',
            'Cables'            => 'Cables',
            'Chargers'          => 'Chargers',
            'Other Accessories' => 'Other Accessories',
        ];
    }

    protected function mapHeaders(array $row): array
    {
        $map = [
            'price'          => null,
            'discount'       => null,
            'model'          => null,
            'name'           => null,
            'service'        => null,
            'product'        => null,
            'issue'          => null,
            'warranty'       => null,
            'sla'            => null,
            'product_number' => null,
            'item_code'      => null,
            'brand'          => null,
            'section'        => null,
            'subsection'     => null,
            'generation'     => null,
        ];

        foreach ($row as $idx => $val) {
            if ($val === null) continue;
            $header = strtolower(trim((string)$val));

            if ($header === 'price')                 $map['price'] = $idx;
            if ($header === 'discount')              $map['discount'] = $idx;
            if ($header === 'model')                 $map['model'] = $idx;
            if ($header === 'name')                  $map['name'] = $idx;
            if ($header === 'service')               $map['service'] = $idx;
            if ($header === 'product')               $map['product'] = $idx;
            if ($header === 'issue')                 $map['issue'] = $idx;
            if ($header === 'warranty')              $map['warranty'] = $idx;
            if ($header === 'sla')                   $map['sla'] = $idx;
            if ($header === 'product number')        $map['product_number'] = $idx;
            if ($header === 'item code')             $map['item_code'] = $idx;
            if ($header === 'brand')                 $map['brand'] = $idx;
            if ($header === 'section')               $map['section'] = $idx;
            if ($header === 'subsection')            $map['subsection'] = $idx;
            if ($header === 'generation')            $map['generation'] = $idx;
        }

        return $map;
    }

    protected function seedRepairSheets($spreadsheet): array
    {
        $log = [];
        $map = $this->getRepairSheetMap();
        $total = count($map);
        $i = 0;

        foreach ($map as $sheetName => [$categoryName, $isEtisalat]) {
            $i++;
            $percent = round(($i / $total) * 100);
            $this->setSyncProgress($percent, "Processing Repairs: {$sheetName}...");

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) {
                $log[] = "❌ Sheet not found: {$sheetName}";
                continue;
            }

            $categoryId = $this->getOrCreateRepairCategory($categoryName);
            $rows       = $sheet->toArray(null, true, false, false);
            
            $count = $this->parseRepairSheet($rows, $categoryId, $isEtisalat, $sheetName);
            $log[] = "✓ Seeded {$count} prices from {$sheetName}";
        }
        return $log;
    }

    protected function normalizeSubcategoryName(string $name, string $sheetName): string
    {
        $name = trim($name);
        if (!$name) return '';

        $sName = strtolower($sheetName);
        $nName = strtolower($name);

        // 1. Global High-Priority Groups (Cross-Device)
        if (stripos($nName, 'screens') !== false || stripos($nName, 'display') !== false) return 'Screens';
        if (stripos($nName, 'battery') !== false) return 'Battery';
        if (stripos($nName, 'back glass') !== false) return 'Back Glass';
        if (stripos($nName, 'housing') !== false) return 'Housing';
        if (stripos($nName, 'charging port') !== false || stripos($nName, 'charge port') !== false) return 'Charging Port';
        if (stripos($nName, 'circuit') !== false || stripos($nName, 'hardware issue') !== false || stripos($nName, 'motherboard') !== false) return 'Hardware Issues';

        // 2. iPhone / Etisalat Normalization
        if (stripos($sName, 'iphone') !== false || stripos($sName, 'etisalat') !== false) {
            if (stripos($nName, 'etisalat') !== false) return 'Etisalat Offers';
            $smallPartsKeywords = ['speaker', 'mic', 'taptic', 'flat', 'camera', 'id', 'flash', 'butt', 'sensor'];
            foreach ($smallPartsKeywords as $kw) {
                if (stripos($nName, $kw) !== false) return 'Small Spare Parts';
            }
        }

        // 3. Gaming Console Normalization (PlayStation, XBox, Nintendo)
        if (stripos($sName, 'gaming') !== false) {
            if (stripos($nName, 'playstation') !== false || stripos($nName, 'ps4') !== false || stripos($nName, 'ps5') !== false) return 'PlayStation';
            if (stripos($nName, 'xbox') !== false) return 'XBox';
            if (stripos($nName, 'nintendo') !== false || stripos($nName, 'switch') !== false) return 'Nintendo';
        }

        // 4. Tablet Model Grouping (iPad Mini, Air, Pro)
        if (stripos($sName, 'ipad') !== false) {
            if (stripos($nName, 'mini') !== false) return 'iPad Mini';
            if (stripos($nName, 'air') !== false) return 'iPad Air';
            if (stripos($nName, 'pro') !== false) return 'iPad Pro';
        }

        // 5. Default Cleaning (Strip models and prefixes)
        $clean = preg_replace('/^iPhone\s+(\d+|[XVS]+)\s+/i', '', $name);
        $clean = preg_replace('/^iPad\s+(\d+|[XVS]+)\s+/i', '', $clean);
        $clean = preg_replace('/^Series\s+\d+\s+/i', '', $clean);
        $clean = preg_replace('/\s+Original|Copy|Housing|Back Glass/i', '', $clean);
        
        return trim($clean) ?: $name;
    }

    protected function parseRepairSheet(array $rows, int $categoryId, bool $isEtisalat, string $sheetName): int
    {
        $headerMap = null;
        $currentSubcategoryId = null;
        $count = 0;

        foreach ($rows as $row) {
            $row = array_values(array_pad((array)$row, 50, null));
            if ($this->isEmptyRow($row)) continue;

            if ($headerMap === null) {
                $tempMap = $this->mapHeaders($row);
                $hasName = ($tempMap['model'] !== null || $tempMap['name'] !== null || $tempMap['service'] !== null || $tempMap['product'] !== null || $tempMap['issue'] !== null);
                if ($tempMap['price'] !== null && $hasName) {
                    $headerMap = $tempMap;
                    continue;
                }
                continue;
            }

            $priceIdx    = $headerMap['price'];
            $sectionIdx  = $headerMap['section'];
            $subIdx      = $headerMap['subsection'];
            $warrantyIdx = $headerMap['warranty'];
            $slaIdx      = $headerMap['sla'];
            $pnIdx       = $headerMap['product_number'];

            $modelCol = trim((string)($row[$headerMap['model'] ?? -1] ?? ''));
            $nameCol  = null;

            // Priority logic: find the first name that is different from the model
            $sources = ['issue', 'service', 'product', 'name', 'section'];
            foreach ($sources as $key) {
                if (($headerMap[$key] ?? null) !== null) {
                    $val = trim((string)($row[$headerMap[$key]] ?? ''));
                    if ($val !== '' && strtolower($val) !== 'section' && strtolower($val) !== strtolower($modelCol)) {
                        $nameCol = $val;
                        break;
                    }
                }
            }
            
            // Fallback to name column or model if no unique descriptive name found
            if ($nameCol === null) {
                $nameCol = trim((string)($row[$headerMap['name'] ?? -1] ?? $modelCol));
            }

            if ($modelCol && $nameCol && strtolower($modelCol) !== strtolower($nameCol)) {
                $modelVal = $modelCol . " - " . $nameCol;
            } else {
                $modelVal = $modelCol ?: $nameCol;
            }

            if ($sectionIdx !== null && !empty($row[$sectionIdx])) {
                $sectionLabel = trim((string)$row[$sectionIdx]);
                if (strtolower($sectionLabel) !== 'section' && $sectionLabel !== '') {
                    $normalized = $this->normalizeSubcategoryName($sectionLabel, $sheetName);
                    $currentSubcategoryId = $this->getOrCreateRepairSubcategory($categoryId, $normalized);
                }
            }
            
            if (!$currentSubcategoryId) {
                $defaultLabel = $this->getDefaultSubcategory($sheetName);
                $currentSubcategoryId = $this->getOrCreateRepairSubcategory($categoryId, $defaultLabel ?: 'General');
            }

            if (!$modelVal) continue;
            
            $price = $this->extractFirstPrice($row[$priceIdx] ?? null);
            if ($price === null) continue;

            try {
                $discount = $this->normalizeDiscount($row[$headerMap['discount']] ?? 0);
                $pad      = round($price * (1 - $discount / 100), 2);
                
                $fullModel = trim((string)$modelVal);
                if (($headerMap['generation'] ?? null) !== null && !empty($row[$headerMap['generation']])) {
                    $genVal = trim((string)$row[$headerMap['generation']]);
                    if ($genVal && strtolower($genVal) !== 'generation') {
                        $firstGen = trim(explode("\n", $genVal)[0]);
                        $fullModel .= " ({$firstGen})";
                    }
                }

                DB::table('repair_prices')->insert([
                    'repair_subcategory_id' => $currentSubcategoryId,
                    'product_number'        => $pnIdx !== null ? trim((string)$row[$pnIdx]) : null,
                    'model'                 => $fullModel,
                    'price'                 => $price,
                    'discount'              => $discount,
                    'price_after_discount'  => $pad,
                    'warranty'              => $warrantyIdx !== null ? trim((string)$row[$warrantyIdx]) : null,
                    'sla'                   => $slaIdx !== null ? trim((string)$row[$slaIdx]) : null,
                    'is_etisalat_offer'     => $isEtisalat,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
                $count++;
            } catch (\Throwable $e) {}
        }
        return $count;
    }

    protected function seedAccessorySheets($spreadsheet): array
    {
        $log = [];
        $map = $this->getAccessorySheetMap();
        $total = count($map);
        $i = 0;

        foreach ($map as $sheetName => $categoryName) {
            $i++;
            $percent = round(($i / $total) * 100);
            $this->setSyncProgress($percent, "Processing Accessories: {$sheetName}...");

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) {
                $log[] = "❌ Sheet not found: {$sheetName}";
                continue;
            }

            $categoryId = $this->getOrCreateAccessoryCategory($categoryName);
            $rows       = $sheet->toArray(null, true, false, false);
            
            $headerMap = null;
            $count = 0;
            foreach ($rows as $row) {
                $row = array_values(array_pad((array)$row, 50, null));
                if ($this->isEmptyRow($row)) continue;

                if ($headerMap === null) {
                    $tempMap = $this->mapHeaders($row);
                    $hasName = ($tempMap['model'] !== null || $tempMap['name'] !== null || $tempMap['service'] !== null || $tempMap['product'] !== null || $tempMap['issue'] !== null);
                    if ($tempMap['price'] !== null && $hasName) {
                        $headerMap = $tempMap;
                        continue;
                    }
                    continue;
                }

                $nameVal = null;
                foreach (['name', 'model', 'product', 'service', 'issue'] as $key) {
                    if (($headerMap[$key] ?? null) !== null && !empty($row[$headerMap[$key]])) {
                        $nameVal = $row[$headerMap[$key]];
                        break;
                    }
                }

                $priceIdx    = $headerMap['price'];
                $brandIdx    = $headerMap['brand'];
                $itemCodeIdx = $headerMap['item_code'];
                $discountIdx = $headerMap['discount'];

                $priceVal = $row[$priceIdx] ?? null;

                if (!$nameVal) continue;
                $price = $this->extractFirstPrice($priceVal);
                if ($price === null) continue;

                try {
                    $discount = $this->normalizeDiscount($row[$discountIdx] ?? 0);
                    $pad      = round($price * (1 - $discount / 100), 2);

                    DB::table('accessories')->insert([
                        'accessory_category_id' => $categoryId,
                        'brand'                 => $brandIdx !== null ? trim((string)$row[$brandIdx]) : null,
                        'item_code'             => $itemCodeIdx !== null ? trim((string)$row[$itemCodeIdx]) : null,
                        'name'                  => trim((string)$nameVal),
                        'price'                 => $price,
                        'discount'              => $discount,
                        'price_after_discount'  => $pad,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                    $count++;
                } catch (\Throwable $e) {}
            }
            $log[] = "✓ Seeded {$count} items from {$sheetName}";
        }
        return $log;
    }

    protected function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($v) => $v !== null && $v !== ''));
    }

    protected function normalizeDiscount(mixed $discount): float
    {
        if ($discount === null || $discount === '') return 0.0;
        $value = (float)$discount;
        if ($value > 0 && $value <= 1) return round($value * 100, 2);
        return round($value, 2);
    }

    protected function extractFirstPrice(mixed $raw): ?float
    {
        if (is_numeric($raw)) return (float)$raw;
        if (!is_string($raw)) return null;
        $lines = array_filter(array_map('trim', explode("\n", $raw)), fn($l) => is_numeric($l));
        $first = array_values($lines)[0] ?? null;
        return $first !== null ? (float)$first : null;
    }

    protected function getDefaultSubcategory(string $sheetName): ?string
    {
        if (stripos($sheetName, 'Etisalat') !== false) return 'Etisalat Offers';
        if (stripos($sheetName, 'Screens & Battery') !== false) return 'Screens';
        if (stripos($sheetName, 'Samsung') !== false) return 'Screens';
        if (stripos($sheetName, 'Google Pixel') !== false) return 'Screens';
        
        return null;
    }

    protected function getOrCreateRepairCategory(string $name): int
    {
        $id = DB::table('repair_categories')->where('name', $name)->value('id');
        if ($id) return $id;
        return DB::table('repair_categories')->insertGetId([
            'name' => $name, 'created_at' => now(), 'updated_at' => now()
        ]);
    }

    protected function getOrCreateRepairSubcategory(int $categoryId, string $name): int
    {
        $id = DB::table('repair_subcategories')
            ->where('repair_category_id', $categoryId)
            ->where('name', $name)
            ->value('id');
        if ($id) return $id;
        return DB::table('repair_subcategories')->insertGetId([
            'repair_category_id' => $categoryId, 'name' => $name, 'created_at' => now(), 'updated_at' => now()
        ]);
    }

    protected function getOrCreateAccessoryCategory(string $name): int
    {
        $id = DB::table('accessory_categories')->where('name', $name)->value('id');
        if ($id) return $id;
        return DB::table('accessory_categories')->insertGetId([
            'name' => $name, 'created_at' => now(), 'updated_at' => now()
        ]);
    }
}
