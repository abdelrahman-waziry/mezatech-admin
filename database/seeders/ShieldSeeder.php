<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $roles = [
            'super_admin', // Ensure super_admin role exists
            'editor',
            'analyst',
            'viewer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // -----------------------------
        // Viewer: View access only (Excluding Users & Roles)
        // -----------------------------
        $viewerRole = Role::findByName('viewer');
        $viewerPermissions = Permission::where('name', 'like', 'view_%')
            ->where('name', 'not like', '%_user')
            ->where('name', 'not like', '%_role')
            ->get();
        $viewerRole->syncPermissions($viewerPermissions);

        // -----------------------------
        // Editor: View + Create + Update (Excluding Users & Roles)
        // -----------------------------
        $editorRole = Role::findByName('editor');
        $editorPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'view_%')
                  ->orWhere('name', 'like', 'create_%')
                  ->orWhere('name', 'like', 'update_%');
        })
        ->where('name', 'not like', '%_user')
        ->where('name', 'not like', '%_role')
        ->get();
        
        $editorRole->syncPermissions($editorPermissions);

        // -----------------------------
        // Analyst: View access (Excluding Users & Roles)
        // -----------------------------
        $analystRole = Role::findByName('analyst');
        $analystPermissions = Permission::where('name', 'like', 'view_%')
            ->where('name', 'not like', '%_user')
            ->where('name', 'not like', '%_role')
            ->orWhere('name', 'like', 'widget_%') // If widgets have permissions
            ->get();
            
        // Filter analyst permissions again to ensure no user/role leaked via OR clause if not carefully constructed
        // The above query might be tricky with OR. Let's do it cleanly via collection filter or better query grouping
        
        $analystPermissions = Permission::where(function($q) {
             $q->where('name', 'like', 'view_%')
               ->orWhere('name', 'like', 'widget_%');
        })
        ->where('name', 'not like', '%_user')
        ->where('name', 'not like', '%_role')
        ->get();

        $analystRole->syncPermissions($analystPermissions);
        
        // Super Admin is handled by Filament Shield via Gate interception, 
        // effectively granting all permissions without explicit assignment.
    }
}
