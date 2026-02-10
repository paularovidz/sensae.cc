<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\VendorMapping;
use App\Models\ExpenseCategory;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Validator;

class VendorMappingController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $mappings = VendorMapping::getAll();
        Response::success($mappings);
    }

    public function show(string $id): void
    {
        $mapping = VendorMapping::findById($id);

        if (!$mapping) {
            Response::notFound('Mapping non trouvé');
        }

        Response::success($mapping);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data)
            ->required('vendor_pattern')
            ->min('vendor_pattern', 2)
            ->required('category_id')
            ->uuid('category_id');

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Verify category exists
        if (!ExpenseCategory::findById($data['category_id'])) {
            Response::error('Catégorie non trouvée', 404);
        }

        $userId = AuthMiddleware::getUserId();

        $id = VendorMapping::create([
            'vendor_pattern' => $data['vendor_pattern'],
            'vendor_display_name' => $data['vendor_display_name'] ?? null,
            'category_id' => $data['category_id'],
            'is_regex' => $data['is_regex'] ?? 0,
            'priority' => $data['priority'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId
        ]);

        $mapping = VendorMapping::findById($id);
        Response::success($mapping, 'Mapping créé', 201);
    }

    public function update(string $id): void
    {
        $mapping = VendorMapping::findById($id);

        if (!$mapping) {
            Response::notFound('Mapping non trouvé');
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data);
        if (isset($data['category_id'])) {
            $validator->uuid('category_id');
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Verify category exists if changing
        if (isset($data['category_id']) && !ExpenseCategory::findById($data['category_id'])) {
            Response::error('Catégorie non trouvée', 404);
        }

        VendorMapping::update($id, $data);
        $updated = VendorMapping::findById($id);
        Response::success($updated, 'Mapping mis à jour');
    }

    public function destroy(string $id): void
    {
        $mapping = VendorMapping::findById($id);

        if (!$mapping) {
            Response::notFound('Mapping non trouvé');
        }

        VendorMapping::delete($id);
        Response::success(null, 'Mapping supprimé');
    }

    public function suggest(): void
    {
        $vendorName = $_GET['vendor'] ?? '';

        if (empty($vendorName)) {
            Response::success(null);
            return;
        }

        $result = VendorMapping::findCategoryForVendor($vendorName);
        Response::success($result);
    }

    /**
     * Search vendor names for autocomplete
     */
    public function search(): void
    {
        $query = $_GET['q'] ?? '';

        if (strlen($query) < 1) {
            // Return all vendor names if no query
            $vendors = VendorMapping::getAllVendorNames();
            Response::success($vendors);
            return;
        }

        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $vendors = VendorMapping::search($query, $limit);
        Response::success($vendors);
    }
}
