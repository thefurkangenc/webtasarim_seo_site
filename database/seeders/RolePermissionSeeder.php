<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Modül izinleri. Yeni modül eklerken buraya bir satır ekle ve seeder'ı
     * tekrar çalıştır — mevcut kayıtlar korunur.
     *
     * @var array<int, string>
     */
    private const MODULES = [
        'user',
        'role',
    ];

    /** @var array<int, string> */
    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    /**
     * İçerik editörünün erişebileceği modüller.
     *
     * @var array<int, string>
     */
    private const EDITOR_MODULES = [];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // super-admin izinleri AppServiceProvider'daki Gate::before ile gelir,
        // bu yüzden ayrıca senkronlanmaz.
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::where('guard_name', 'web')->get());

        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
            ->syncPermissions($this->editorPermissions());
    }

    /** @return array<int, string> */
    private function editorPermissions(): array
    {
        $permissions = [];

        foreach (self::EDITOR_MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }
}
