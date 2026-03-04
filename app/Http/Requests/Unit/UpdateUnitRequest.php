<?php

declare(strict_types=1);

namespace App\Http\Requests\Unit;

use App\Models\Unit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUnitRequest extends FormRequest
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
        $unit = $this->route('unit');
        $unitId = $unit instanceof Unit ? $unit->id : null;

        return [
            'name' => ['required', 'string', 'min:1', 'max:80', Rule::unique('units', 'name')->ignore($unitId)],
            'short_name' => ['required', 'string', 'min:1', 'max:20', Rule::unique('units', 'short_name')->ignore($unitId)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
