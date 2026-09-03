<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * İzinleri ve rolleri config/permissions.php'den senkronlar.
 *
 * Tekrar çalıştırılabilir: mevcut izinlerin etiketi güncellenir, rol
 * atamaları korunur. Config'ten kaldırılan izinler SİLİNMEZ — rol
 * atamalarını sessizce düşürmemek için sadece uyarı basılır.
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
                    'guard_name' => $permission['guard_name'] ?? 'web',
                ],
                [
                    'label' => $permission['label'] ?? $permission['name'],
                    'category' => $permission['category'] ?? null,
                ],
            );
        }

        foreach (config('permissions.roles', []) as $role => $patterns) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web'])
                ->syncPermissions($this->match($patterns));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->warnAboutOrphans($defined->pluck('name'));
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

    /** @param  Collection<int, string>  $definedNames */
    private function warnAboutOrphans(Collection $definedNames): void
    {
        $orphans = Permission::whereNotIn('name', $definedNames)->pluck('name');

        if ($orphans->isEmpty()) {
            return;
        }

        $this->command?->warn(
            'config/permissions.php içinde bulunmayan izinler var (silinmedi): '.$orphans->implode(', '),
        );
    }
}
