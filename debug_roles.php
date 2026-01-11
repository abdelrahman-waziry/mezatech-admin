<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();

$output = "";
foreach ($users as $user) {
    $output .= "---------------------------\n";
    $output .= "ID: " . $user->id . "\n";
    $output .= "Name: " . $user->name . "\n";
    $output .= "Email: " . $user->email . "\n";
    $output .= "Roles: [" . $user->getRoleNames()->implode(', ') . "]\n";
    $isSuper = $user->hasRole(config('filament-shield.super_admin.name'));
    $output .= "Is Super Admin Rule Match: " . ($isSuper ? "YES" : "NO") . "\n";
}
$output .= "---------------------------\n";
$output .= "Config Super Admin Role Name: " . config('filament-shield.super_admin.name') . "\n";

file_put_contents(__DIR__.'/debug_roles.txt', $output);
