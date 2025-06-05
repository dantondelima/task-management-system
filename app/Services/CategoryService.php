<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CategoryService implements CategoryServiceInterface
{
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * CategoryService constructor
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all categories for a user
     */
    public function getAllCategories(int $userId): Collection
    {
        return $this->categoryRepository->getAllCategories($userId);
    }

    /**
     * Get all categories with task counts for a user
     */
    public function getAllCategoriesWithTaskCount(int $userId): Collection
    {
        return $this->categoryRepository->getAllCategoriesWithTaskCount($userId);
    }

    /**
     * Get paginated categories with task counts for a user
     */
    public function getPaginatedCategories(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->categoryRepository->getPaginatedCategories($userId, $perPage);
    }

    /**
     * Get category by id
     *
     * @throws ModelNotFoundException
     */
    public function getCategoryById(int $id, int $userId): Category
    {
        try {
            return $this->categoryRepository->getCategoryById($id, $userId);
        } catch (ModelNotFoundException $e) {
            Log::warning("Category not found: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Create new category for a user
     */
    public function createCategory(array $categoryData): Category
    {
        return $this->categoryRepository->createCategory($categoryData);
    }

    /**
     * Update category
     *
     * @throws ModelNotFoundException
     */
    public function updateCategory(int $id, array $categoryData, int $userId): Category
    {
        try {
            return $this->categoryRepository->updateCategory($id, $categoryData, $userId);
        } catch (ModelNotFoundException $e) {
            Log::warning("Category not found for update: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Delete category
     *
     * @throws ModelNotFoundException
     */
    public function deleteCategory(int $id, int $userId): bool
    {
        try {
            return $this->categoryRepository->deleteCategory($id, $userId);
        } catch (ModelNotFoundException $e) {
            Log::warning("Category not found for deletion: {$e->getMessage()}");
            throw $e;
        }
    }
}
