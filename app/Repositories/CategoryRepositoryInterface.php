<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories for a user
     */
    public function getAllCategories(int $userId): Collection;

    /**
     * Get all categories with task counts for a user
     */
    public function getAllCategoriesWithTaskCount(int $userId): Collection;

    /**
     * Get paginated categories with task counts for a user
     */
    public function getPaginatedCategories(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get category by id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCategoryById(int $id, int $userId): Category;

    /**
     * Create new category for a user
     */
    public function createCategory(array $categoryData): Category;

    /**
     * Update category
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCategory(int $id, array $categoryData, int $userId): Category;

    /**
     * Delete category
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteCategory(int $id, int $userId): bool;
}
