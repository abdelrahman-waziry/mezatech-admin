<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) && ! filter_var(env('SEED_DEFAULT_USERS', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $password = Hash::make('password');

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@mezatech.com'],
            ['name' => 'Super Admin', 'password' => $password]
        );
        $superAdmin->assignRole('super_admin');

        // Editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@mezatech.com'],
            ['name' => 'Editor User', 'password' => $password]
        );
        $editor->assignRole('editor');

        // Analyst
        $analyst = User::firstOrCreate(
            ['email' => 'analyst@mezatech.com'],
            ['name' => 'Analyst User', 'password' => $password]
        );
        $analyst->assignRole('analyst');

        // Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@mezatech.com'],
            ['name' => 'Viewer User', 'password' => $password]
        );
        $viewer->assignRole('viewer');
    }
}
