<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

use Spatie\Permission\Models\Role;

$roles = ['editor', 'analyst', 'viewer'];
foreach ($roles as $roleName) {
    echo "CHECKING Role: $roleName\n";
    $role = Role::findByName($roleName);
    echo "Role ID: " . $role->id . "\n";
    $permissions = $role->permissions->pluck('name');
    echo "Total Permissions: " . $permissions->count() . "\n";
    
    $prohibited = $permissions->filter(function($p) {
        return str_contains($p, 'user') || str_contains($p, 'role');
    });

    if ($prohibited->isNotEmpty()) {
        echo "FAILED: Found prohibited permissions: " . $prohibited->implode(', ') . "\n";
    } else {
        echo "SUCCESS: No restricted permissions found (user/role).\n";
    }
    echo "-------------------\n";
}
