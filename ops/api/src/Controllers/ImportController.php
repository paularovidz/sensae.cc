<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BankImport;
use App\Services\CsvImportService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

class ImportController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function history(): void
    {
        $limit = (int) ($_GET['limit'] ?? 50);
        $imports = BankImport::getAll($limit);
        Response::success($imports);
    }

    public function preview(): void
    {
        if (!isset($_FILES['file'])) {
            Response::error('Aucun fichier envoyé', 400);
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('Erreur lors du téléchargement', 400);
        }

        // Parse column mapping from request
        $columnMapping = [
            'date' => (int) ($_POST['date_column'] ?? 0),
            'description' => (int) ($_POST['description_column'] ?? 1),
            'amount' => (int) ($_POST['amount_column'] ?? 2),
            'vendor' => isset($_POST['vendor_column']) ? (int) $_POST['vendor_column'] : null
        ];

        $dateFormat = $_POST['date_format'] ?? 'd/m/Y';
        $skipFirstRow = ($_POST['skip_header'] ?? 'true') === 'true';

        $service = new CsvImportService(
            AuthMiddleware::getUserId(),
            $columnMapping,
            $dateFormat,
            $skipFirstRow
        );

        try {
            $preview = $service->preview($file['tmp_name'], 20);
            Response::success($preview);
        } catch (\Exception $e) {
            Response::error('Erreur lors de la lecture du fichier: ' . $e->getMessage(), 400);
        }
    }

    public function import(): void
    {
        if (!isset($_FILES['file'])) {
            Response::error('Aucun fichier envoyé', 400);
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('Erreur lors du téléchargement', 400);
        }

        // Parse column mapping from request
        $columnMapping = [
            'date' => (int) ($_POST['date_column'] ?? 0),
            'description' => (int) ($_POST['description_column'] ?? 1),
            'amount' => (int) ($_POST['amount_column'] ?? 2),
            'vendor' => isset($_POST['vendor_column']) ? (int) $_POST['vendor_column'] : null
        ];

        $dateFormat = $_POST['date_format'] ?? 'd/m/Y';
        $skipFirstRow = ($_POST['skip_header'] ?? 'true') === 'true';

        $service = new CsvImportService(
            AuthMiddleware::getUserId(),
            $columnMapping,
            $dateFormat,
            $skipFirstRow
        );

        try {
            $result = $service->import($file['tmp_name'], $file['name']);
            Response::success($result, "{$result['imported']} lignes importées");
        } catch (\Exception $e) {
            Response::error('Erreur lors de l\'import: ' . $e->getMessage(), 400);
        }
    }
}
