<?php

declare(strict_types=1);

use App\Actions\Suppliers\DeleteSupplier;
use App\Models\Supplier;
use App\Models\User;

it('may delete a supplier', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteSupplier::class);
    $action->handle($supplier);

    expect(Supplier::query()->find($supplier->id))->toBeNull()
        ->and($supplier->created_by)->toBeNull();
});
