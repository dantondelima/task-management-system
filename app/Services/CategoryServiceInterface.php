<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{
    /**
     * Get all categories
     */
    public function getAllCategories(): Collection;

    /**
     * Get all categories with task counts
     */
    public function getAllCategoriesWithTaskCount(): Collection;

    /**
     * Get paginated categories with task counts
     */
    public function getPaginatedCategories(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get category by id
     *
     * @throws ModelNotFoundException
     */
    public function getCategoryById(int $id): Category;

    /**
     * Create new category
     */
    public function createCategory(array $categoryData): Category;

    /**
     * Update category
     *
     * @throws ModelNotFoundException
     */
    public function updateCategory(int $id, array $categoryData): Category;

    /**
     * Delete category
     *
     * @throws ModelNotFoundException
     */
    public function deleteCategory(int $id): bool;
}
