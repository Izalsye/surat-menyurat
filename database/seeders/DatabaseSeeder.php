<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\LetterCategorySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin',
            'email' => 'iqbaleff214@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            RoleSeeder::class,
            LetterCategorySeeder::class,
        ]);
    }
}
