<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class UpdateUserData extends Data
{
    public function __construct(
        #[StringType, Max(255)]
        public string $name,

        #[StringType, Max(255), Email]
        public string $email,

        public ?RoleEnum $role = null,

        #[FromRouteParameter('user')]
        public ?User $user = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(ValidationContext $context): array
    {
        $routeUser = request()->route('user');
        $userId = $routeUser instanceof User ? $routeUser->id : $routeUser;

        $userId ??= auth()->id();

        return [
            'email' => [
                'required',
                'string',
                'max:255',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
        ];
    }
}
