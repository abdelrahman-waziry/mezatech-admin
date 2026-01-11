<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;

$roles = ['editor', 'analyst', 'viewer'];
foreach ($roles as $roleName) {
    echo "Role: $roleName\n";
    $role = Role::findByName($roleName);
    $permissions = $role->permissions->pluck('name');
    
    $prohibited = $permissions->filter(function($p) {
        return str_contains($p, 'user') || str_contains($p, 'role');
    });

    if ($prohibited->isNotEmpty()) {
        echo "FAILED: Found prohibited permissions: " . $prohibited->implode(', ') . "\n";
    } else {
        echo "SUCCESS: No restricted permissions found.\n";
    }
    echo "-------------------\n";
}
