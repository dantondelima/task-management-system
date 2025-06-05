<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CategoryServiceInterface $categoryService;

    /**
     * CategoryController constructor
     */
    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|RedirectResponse
    {
        try {
            $categories = $this->categoryService->getAllCategoriesWithTaskCount();

            return view('categories.index', compact('categories'));
        } catch (Exception $e) {
            Log::error('Error fetching categories: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('home')
                ->with('error', 'An error occurred while fetching categories. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoryRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            $this->categoryService->createCategory($validatedData);

            return redirect()->route('categories.index')
                ->with('success', 'Category created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating category: '.$e->getMessage(), [
                'exception' => $e,
                'data' => $request->validated(),
            ]);

            return redirect()->route('categories.create')
                ->with('error', 'An error occurred while creating the category. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            return view('categories.edit', compact('category'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Category not found.');
        } catch (Exception $e) {
            Log::error('Error loading category edit form: '.$e->getMessage(), [
                'exception' => $e,
                'category_id' => $id,
            ]);

            return redirect()->route('categories.index')
                ->with('error', 'An error occurred while loading the category edit form. Please try again later.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, int $id): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            $this->categoryService->updateCategory($id, $validatedData);

            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Category not found.');
        } catch (Exception $e) {
            Log::error('Error updating category: '.$e->getMessage(), [
                'exception' => $e,
                'category_id' => $id,
                'data' => $request->validated(),
            ]);

            return redirect()->route('categories.edit', $id)
                ->with('error', 'An error occurred while updating the category. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->categoryService->deleteCategory($id);

            return redirect()->route('categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Category not found.');
        } catch (Exception $e) {
            Log::error('Error deleting category: '.$e->getMessage(), [
                'exception' => $e,
                'category_id' => $id,
            ]);

            return redirect()->route('categories.index')
                ->with('error', 'An error occurred while deleting the category. Please try again.');
        }
    }
}
