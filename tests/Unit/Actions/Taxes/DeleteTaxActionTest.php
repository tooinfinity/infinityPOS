<?php

declare(strict_types=1);

use App\Actions\Taxes\DeleteTax;
use App\Models\Tax;
use App\Models\User;

it('may delete a tax', function (): void {
    $user = User::factory()->create();
    $tax = Tax::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteTax::class);
    $action->handle($tax);

    expect(Tax::query()->find($tax->id))->toBeNull()
        ->and($tax->created_by)->toBeNull();
});
