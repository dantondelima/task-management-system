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
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAllCategories();
    }

    /**
     * Get all categories with task counts
     */
    public function getAllCategoriesWithTaskCount(): Collection
    {
        return $this->categoryRepository->getAllCategoriesWithTaskCount();
    }

    /**
     * Get paginated categories with task counts
     */
    public function getPaginatedCategories(int $perPage = 10): LengthAwarePaginator
    {
        return $this->categoryRepository->getPaginatedCategories($perPage);
    }

    /**
     * Get category by id
     *
     * @throws ModelNotFoundException
     */
    public function getCategoryById(int $id): Category
    {
        try {
            return $this->categoryRepository->getCategoryById($id);
        } catch (ModelNotFoundException $e) {
            Log::warning("Category not found: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Create new category
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
    public function updateCategory(int $id, array $categoryData): Category
    {
        try {
            return $this->categoryRepository->updateCategory($id, $categoryData);
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
    public function deleteCategory(int $id): bool
    {
        try {
            return $this->categoryRepository->deleteCategory($id);
        } catch (ModelNotFoundException $e) {
            Log::warning("Category not found for deletion: {$e->getMessage()}");
            throw $e;
        }
    }
}
