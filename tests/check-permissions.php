<?php

require_once __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Spatie\Permission\Models\Permission;

echo 'Total Permissions: '.Permission::count()."\n\n";

echo "First 10 permissions:\n";
Permission::take(10)->get(['id', 'name', 'guard_name', 'group'])->each(function ($p) {
    echo "  - ID: {$p->id}, Name: {$p->name}, Group: {$p->group}\n";
});

echo "\nPermissions grouped by 'group' field:\n";
$grouped = Permission::all()->groupBy('group');
foreach ($grouped as $group => $perms) {
    echo "  - {$group}: {$perms->count()} permissions\n";
}
