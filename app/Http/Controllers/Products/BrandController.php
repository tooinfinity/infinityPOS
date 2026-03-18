<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Brand\CreateBrand;
use App\Actions\Brand\DeleteBrand;
use App\Actions\Brand\UpdateBrand;
use App\Data\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class BrandController
{
    public function index(): Response
    {
        /** @var array{search?: string|null, sort?: string|null, direction?: string|null} $filters */
        $filters = request()->only(['search', 'sort', 'direction']);
        $perPage = request()->integer('per_page');

        return Inertia::render('brands/index', [
            'brands' => Brand::withInactive()
                ->paginateWithFilters($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('brands/create');
    }

    /**
     * @throws Throwable
     */
    public function store(BrandData $data, CreateBrand $action): RedirectResponse
    {
        $brand = $action->handle($data);

        return to_route('brands.show', $brand)
            ->with('success', "Brand '{$brand->name}' created.");
    }

    public function show(Brand $brand): Response
    {
        $brand->loadCount('products');

        return Inertia::render('brands/show', [
            'brand' => $brand,
        ]);
    }

    public function edit(Brand $brand): Response
    {
        return Inertia::render('brands/edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(Brand $brand, BrandData $data, UpdateBrand $action): RedirectResponse
    {
        $action->handle($brand, $data);

        return to_route('brands.index')
            ->with('success', "Brand '{$brand->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Brand $brand, DeleteBrand $action): RedirectResponse
    {
        $action->handle($brand);

        return to_route('brands.index')
            ->with('success', 'Brand deleted.');
    }
}
