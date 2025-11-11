<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@app.com',
            'password' => 'admin1234',
        ]);
    }
}
