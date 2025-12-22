<?php

declare(strict_types=1);

namespace App\Http\Controllers\Categories;

use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\DeleteCategory;
use App\Actions\Categories\UpdateCategory;
use App\Data\Categories\CategoryData;
use App\Data\Categories\CreateCategoryData;
use App\Data\Categories\UpdateCategoryData;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class CategoryController
{
    public function index(): Response
    {
        $categories = Category::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('categories/index', [
            'categories' => CategoryData::collect($categories),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('categories/create');
    }

    public function store(CreateCategoryData $data, CreateCategory $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('categories.index');
    }

    public function show(Category $category): Response
    {
        $category->load('creator');

        return Inertia::render('categories/show', [
            'category' => CategoryData::from($category),
        ]);
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('categories/edit', [
            'category' => CategoryData::from($category),
        ]);
    }

    public function update(UpdateCategoryData $data, Category $category, UpdateCategory $action): RedirectResponse
    {
        $action->handle($category, $data);

        return back();
    }

    public function destroy(Category $category, DeleteCategory $action): RedirectResponse
    {
        $action->handle($category);

        return to_route('categories.index');
    }
}
