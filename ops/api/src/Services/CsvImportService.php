<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Models\Expense;
use App\Models\BankImport;
use App\Models\VendorMapping;

class CsvImportService
{
    private string $userId;
    private array $columnMapping;
    private string $dateFormat;
    private bool $skipFirstRow;

    public function __construct(
        string $userId,
        array $columnMapping,
        string $dateFormat = 'd/m/Y',
        bool $skipFirstRow = true
    ) {
        $this->userId = $userId;
        $this->columnMapping = $columnMapping;
        $this->dateFormat = $dateFormat;
        $this->skipFirstRow = $skipFirstRow;
    }

    /**
     * Import a CSV file
     * Returns [imported => int, skipped => int, errors => array]
     */
    public function import(string $filePath, string $filename): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Cannot open file');
        }

        // Create import record
        $importId = BankImport::create([
            'filename' => $filename,
            'imported_by' => $this->userId
        ]);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 0;

        Database::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $rowNumber++;

                // Skip header row if needed
                if ($rowNumber === 1 && $this->skipFirstRow) {
                    continue;
                }

                try {
                    $result = $this->processRow($row, $importId, $rowNumber);
                    if ($result) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Ligne {$rowNumber}: " . $e->getMessage();
                    $skipped++;
                }
            }

            BankImport::updateCounts($importId, $imported, $skipped);
            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            throw $e;
        } finally {
            fclose($handle);
        }

        return [
            'import_id' => $importId,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Preview CSV file without importing
     */
    public function preview(string $filePath, int $maxRows = 10): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Cannot open file');
        }

        $rows = [];
        $rowNumber = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false && count($rows) < $maxRows) {
            $rowNumber++;

            // Skip header but return it separately
            if ($rowNumber === 1) {
                $headers = $row;
                if ($this->skipFirstRow) {
                    continue;
                }
            }

            $parsed = $this->parseRow($row);
            if ($parsed) {
                // Try to find category suggestion
                if (!empty($parsed['vendor'])) {
                    $mapping = VendorMapping::findCategoryForVendor($parsed['vendor']);
                    if ($mapping) {
                        $parsed['suggested_category_id'] = $mapping['category_id'];
                        $parsed['suggested_category_name'] = $mapping['category_name'];
                        if ($mapping['vendor_display_name']) {
                            $parsed['suggested_vendor'] = $mapping['vendor_display_name'];
                        }
                    }
                }
                $rows[] = $parsed;
            }
        }

        fclose($handle);

        return [
            'headers' => $headers ?? [],
            'rows' => $rows,
            'total_rows' => $rowNumber - ($this->skipFirstRow ? 1 : 0)
        ];
    }

    private function processRow(array $row, string $importId, int $rowNumber): bool
    {
        $parsed = $this->parseRow($row);

        if (!$parsed || $parsed['amount'] === null) {
            return false;
        }

        // Only import expenses (negative amounts or positive if configured)
        // Most bank exports show expenses as negative
        $amount = $parsed['amount'];
        if ($amount >= 0) {
            // This is likely income, skip
            return false;
        }

        // Make amount positive for storage
        $amount = abs($amount);

        // Try to find category from vendor mapping
        $categoryId = null;
        $vendor = $parsed['vendor'] ?? $parsed['description'];

        if ($vendor) {
            $mapping = VendorMapping::findCategoryForVendor($vendor);
            if ($mapping) {
                $categoryId = $mapping['category_id'];
                if ($mapping['vendor_display_name']) {
                    $vendor = $mapping['vendor_display_name'];
                }
            }
        }

        // If no category found, we need one - use "Frais généraux" as default
        if (!$categoryId) {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id FROM expense_categories WHERE slug = 'general' LIMIT 1");
            $cat = $stmt->fetch();
            $categoryId = $cat ? $cat['id'] : null;

            if (!$categoryId) {
                throw new \RuntimeException("Catégorie par défaut non trouvée");
            }
        }

        Expense::create([
            'category_id' => $categoryId,
            'description' => $parsed['description'],
            'amount' => $amount,
            'expense_date' => $parsed['date'],
            'payment_method' => 'card',
            'vendor' => $vendor,
            'bank_import_id' => $importId,
            'created_by' => $this->userId
        ]);

        return true;
    }

    private function parseRow(array $row): ?array
    {
        $dateCol = $this->columnMapping['date'] ?? 0;
        $descCol = $this->columnMapping['description'] ?? 1;
        $amountCol = $this->columnMapping['amount'] ?? 2;
        $vendorCol = $this->columnMapping['vendor'] ?? null;

        if (!isset($row[$dateCol]) || !isset($row[$amountCol])) {
            return null;
        }

        // Parse date
        $dateStr = trim($row[$dateCol]);
        $date = \DateTime::createFromFormat($this->dateFormat, $dateStr);
        if (!$date) {
            // Try common formats
            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $format) {
                $date = \DateTime::createFromFormat($format, $dateStr);
                if ($date) break;
            }
        }

        if (!$date) {
            return null;
        }

        // Parse amount
        $amountStr = trim($row[$amountCol]);
        // Handle European format (comma as decimal separator)
        $amountStr = str_replace([' ', '€'], '', $amountStr);
        $amountStr = str_replace(',', '.', $amountStr);
        $amount = is_numeric($amountStr) ? (float) $amountStr : null;

        return [
            'date' => $date->format('Y-m-d'),
            'description' => isset($row[$descCol]) ? trim($row[$descCol]) : '',
            'amount' => $amount,
            'vendor' => $vendorCol !== null && isset($row[$vendorCol]) ? trim($row[$vendorCol]) : null
        ];
    }
}
