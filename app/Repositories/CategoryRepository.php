<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected Category $category;

    /**
     * CategoryRepository constructor
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return $this->category->orderBy('name')->get();
    }

    /**
     * Get all categories with task counts
     */
    public function getAllCategoriesWithTaskCount(): Collection
    {
        return $this->category->withCount('tasks')->orderBy('name')->get();
    }

    /**
     * Get paginated categories with task counts
     */
    public function getPaginatedCategories(int $perPage = 10): LengthAwarePaginator
    {
        return $this->category->withCount('tasks')->orderBy('name')->paginate($perPage);
    }

    /**
     * Get category by id
     *
     * @throws ModelNotFoundException
     */
    public function getCategoryById(int $id): Category
    {
        $category = $this->category->withCount('tasks')->find($id);

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found");
        }

        return $category;
    }

    /**
     * Create new category
     */
    public function createCategory(array $categoryData): Category
    {
        return $this->category->create($categoryData);
    }

    /**
     * Update category
     *
     * @throws ModelNotFoundException
     */
    public function updateCategory(int $id, array $categoryData): Category
    {
        $category = $this->category->find($id);

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found");
        }

        $category->update($categoryData);

        return $category;
    }

    /**
     * Delete category
     *
     * @throws ModelNotFoundException
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->category->find($id);

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found");
        }

        return $category->delete();
    }
}
