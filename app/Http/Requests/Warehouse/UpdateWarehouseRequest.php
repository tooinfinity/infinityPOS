<?php

declare(strict_types=1);

namespace App\Http\Requests\Warehouse;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $warehouse = $this->route('warehouse');
        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : null;

        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'code' => ['required', 'string', 'min:1', 'max:50', Rule::unique('warehouses', 'code')->ignore($warehouseId)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
