<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('active_role')->update([
            'active_role' => 'student',
        ]);

        $studentRole = Role::findOrCreate('Student', 'student');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelHasRolesTable = $tableNames['model_has_roles'];
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        $userIds = DB::table('users')
            ->leftJoin($modelHasRolesTable, function ($join) use ($modelHasRolesTable, $rolePivotKey, $modelMorphKey) {
                $join->on("users.id", '=', "{$modelHasRolesTable}.{$modelMorphKey}")
                    ->where("{$modelHasRolesTable}.model_type", '=', User::class);
            })
            ->whereNull("{$modelHasRolesTable}.{$rolePivotKey}")
            ->pluck('users.id');

        if ($userIds->isEmpty()) {
            return;
        }

        $rows = $userIds
            ->map(fn ($userId) => [
                $rolePivotKey => $studentRole->id,
                $modelMorphKey => $userId,
                'model_type' => User::class,
            ])
            ->all();

        DB::table($modelHasRolesTable)->insert($rows);
    }

    public function down(): void
    {
        // Intentionally left blank because role backfills are not safely reversible.
    }
};
