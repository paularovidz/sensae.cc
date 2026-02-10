<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ExpenseCategory;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Validator;

class CategoryController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $activeOnly = !isset($_GET['all']);
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        if (isset($_GET['with_stats'])) {
            $categories = ExpenseCategory::getWithStats($year, $month);
        } else {
            $categories = ExpenseCategory::getAll($activeOnly);
        }

        Response::success($categories);
    }

    public function show(string $id): void
    {
        $category = ExpenseCategory::findById($id);

        if (!$category) {
            Response::notFound('Catégorie non trouvée');
        }

        Response::success($category);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data)
            ->required('name')
            ->min('name', 2)
            ->max('name', 100);

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Check if slug already exists
        $slug = $data['slug'] ?? ExpenseCategory::generateSlug($data['name']);
        if (ExpenseCategory::findBySlug($slug)) {
            Response::error('Une catégorie avec ce nom existe déjà', 409);
        }

        $id = ExpenseCategory::create([
            'name' => $data['name'],
            'slug' => $slug,
            'color' => $data['color'] ?? '#6B7280',
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1
        ]);

        $category = ExpenseCategory::findById($id);
        Response::success($category, 'Catégorie créée', 201);
    }

    public function update(string $id): void
    {
        $category = ExpenseCategory::findById($id);

        if (!$category) {
            Response::notFound('Catégorie non trouvée');
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data);
        if (isset($data['name'])) {
            $validator->min('name', 2)->max('name', 100);
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Check slug uniqueness if changing
        if (isset($data['slug']) && $data['slug'] !== $category['slug']) {
            if (ExpenseCategory::findBySlug($data['slug'])) {
                Response::error('Ce slug est déjà utilisé', 409);
            }
        }

        ExpenseCategory::update($id, $data);
        $updated = ExpenseCategory::findById($id);
        Response::success($updated, 'Catégorie mise à jour');
    }

    public function destroy(string $id): void
    {
        $category = ExpenseCategory::findById($id);

        if (!$category) {
            Response::notFound('Catégorie non trouvée');
        }

        if (!ExpenseCategory::delete($id)) {
            Response::error('Impossible de supprimer cette catégorie (dépenses existantes)', 409);
        }

        Response::success(null, 'Catégorie supprimée');
    }
}
