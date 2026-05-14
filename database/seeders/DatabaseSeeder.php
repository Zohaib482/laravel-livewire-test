<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Standard User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
            ]
        );

        foreach (['Sugar', 'Rice', 'Salt', 'Oil'] as $itemName) {
            Item::query()->firstOrCreate(['name' => $itemName]);
        }

        foreach (['ABC', 'XYZ', 'DEF', 'FreshCo'] as $brandName) {
            Brand::query()->firstOrCreate(['name' => $brandName]);
        }
    }
}
