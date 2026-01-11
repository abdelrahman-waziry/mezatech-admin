<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = \App\Models\User::find(4);
if ($u) {
    echo "User: {$u->email}, Roles: " . $u->getRoleNames();
    if (!$u->hasRole('super_admin')) {
        echo "\nAssigning super_admin role...";
        $u->assignRole('super_admin');
        echo " Done.";
    } else {
        echo "\nUser already has super_admin role.";
    }
} else {
    echo "User ID 4 not found.";
}
