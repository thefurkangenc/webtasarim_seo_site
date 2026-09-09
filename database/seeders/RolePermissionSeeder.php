<?php

namespace Database\Seeders;

use App\Models\Permission\Permission;
use App\Models\Role\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * İzinleri ve rolleri config/permissions.php'den senkronlar.
 *
 * Her satır olduğu gibi yazılır: name, label, category, guard_name.
 * Tekrar çalıştırılabilir. Config'ten kalkan izinler silinir.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $defined = collect(config('permissions.permissions', []));

        foreach ($defined as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'label' => $permission['label'],
                    'category' => $permission['category'],
                ],
            );
        }

        $orphans = Permission::whereNotIn('name', $defined->pluck('name'))->get();

        if ($orphans->isNotEmpty()) {
            $this->command?->warn('Config dışında kalan izinler silindi: '.$orphans->pluck('name')->implode(', '));
            Permission::whereIn('id', $orphans->pluck('id'))->delete();
        }

        foreach (config('permissions.roles', []) as $role => $patterns) {
            $label = match ($role) {
                'super-admin' => 'Süper Yönetici',
                default => $role,
            };

            $record = Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web'],
                ['label' => $label],
            );

            if (blank($record->label)) {
                $record->update(['label' => $label]);
            }

            $record->syncPermissions($this->match($patterns));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Desenlere uyan izinleri döndürür. '*' tümü demektir.
     *
     * @param  string|array<int, string>  $patterns
     * @return Collection<int, Permission>
     */
    private function match(string|array $patterns): Collection
    {
        $permissions = Permission::where('guard_name', 'web')->get();

        if ($patterns === '*') {
            return $permissions;
        }

        return $permissions->filter(
            fn (Permission $permission) => Str::is((array) $patterns, $permission->name),
        );
    }
}
