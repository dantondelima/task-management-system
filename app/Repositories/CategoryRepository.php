<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
     * Get all categories for a user
     */
    public function getAllCategories(int $userId): Collection
    {
        return $this->category->where('user_id', $userId)->orderBy('name')->get();
    }

    /**
     * Get all categories with task counts for a user
     */
    public function getAllCategoriesWithTaskCount(int $userId): Collection
    {
        return $this->category
            ->where('user_id', $userId)
            ->withCount(['tasks' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get paginated categories with task counts for a user
     */
    public function getPaginatedCategories(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->category
            ->where('user_id', $userId)
            ->withCount(['tasks' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * Get category by id
     *
     * @throws ModelNotFoundException
     */
    public function getCategoryById(int $id, int $userId): Category
    {
        $category = $this->category
            ->where('id', $id)
            ->where('user_id', $userId)
            ->withCount(['tasks' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->first();

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found for this user");
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
    public function updateCategory(int $id, array $categoryData, int $userId): Category
    {
        $category = $this->category->where('user_id', $userId)->find($id);

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found for this user");
        }

        $category->update($categoryData);

        return $category;
    }

    /**
     * Delete category
     *
     * @throws ModelNotFoundException
     */
    public function deleteCategory(int $id, int $userId): bool
    {
        $category = $this->category->where('user_id', $userId)->find($id);

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found for this user");
        }

        return $category->delete();
    }
}
