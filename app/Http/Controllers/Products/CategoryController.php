<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\DeleteCategory;
use App\Actions\Category\UpdateCategory;
use App\Data\Category\CategoryData;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class CategoryController
{
    public function index(): Response
    {
        return Inertia::render('products/categories/index', [
            'categories' => Category::withInactive()
                ->withCount('products')
                ->latest()
                ->paginate(25),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('products/categories/create');
    }

    /**
     * @throws Throwable
     */
    public function store(CategoryData $data, CreateCategory $action): RedirectResponse
    {
        $category = $action->handle($data);

        return to_route('categories.index')
            ->with('success', "Category '{$category->name}' created.");
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('products/categories/edit', [
            'category' => $category,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Category $category,
        CategoryData $data,
        UpdateCategory $action,
    ): RedirectResponse {
        $action->handle($category, $data);

        return to_route('categories.index')
            ->with('success', "Category '{$category->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Category $category, DeleteCategory $action): RedirectResponse
    {
        $action->handle($category);

        return to_route('categories.index')
            ->with('success', 'Category deleted.');
    }
}
