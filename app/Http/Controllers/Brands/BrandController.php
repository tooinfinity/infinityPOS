<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brands;

use App\Actions\Brands\CreateBrand;
use App\Actions\Brands\DeleteBrand;
use App\Actions\Brands\UpdateBrand;
use App\Data\Brands\BrandData;
use App\Data\Brands\CreateBrandData;
use App\Data\Brands\UpdateBrandData;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class BrandController
{
    public function index(): Response
    {
        $brands = Brand::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('brands/index', [
            'brands' => BrandData::collect($brands),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('brands/create');
    }

    public function store(CreateBrandData $data, CreateBrand $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('brands.index');
    }

    public function show(Brand $brand): Response
    {
        $brand->load('creator');

        return Inertia::render('brands/show', [
            'brand' => BrandData::from($brand),
        ]);
    }

    public function edit(Brand $brand): Response
    {
        return Inertia::render('brands/edit', [
            'brand' => BrandData::from($brand),
        ]);
    }

    public function update(UpdateBrandData $data, Brand $brand, UpdateBrand $action): RedirectResponse
    {
        $action->handle($brand, $data);

        return back();
    }

    public function destroy(Brand $brand, DeleteBrand $action): RedirectResponse
    {
        $action->handle($brand);

        return to_route('brands.index');
    }
}
