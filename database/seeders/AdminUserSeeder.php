<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir1@nanang.store'],
            [
                'name' => 'Kasir 1',
                'password' => Hash::make('password'),
                'role' => 'cashier'
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir2@nanang.store'],
            [
                'name' => 'Kasir 2',
                'password' => Hash::make('password'),
                'role' => 'cashier'
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir3@nanang.store'],
            [
                'name' => 'Kasir 3',
                'password' => Hash::make('password'),
                'role' => 'cashier'
            ]
        );
    }
}
