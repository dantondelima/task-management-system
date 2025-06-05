<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
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
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCategoryById(int $id): Category;

    /**
     * Create new category
     */
    public function createCategory(array $categoryData): Category;

    /**
     * Update category
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCategory(int $id, array $categoryData): Category;

    /**
     * Delete category
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteCategory(int $id): bool;
}
