<?php

namespace App\Services\Role;

use App\Models\Permission\Permission;
use App\Models\Role\Role;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function list(array $filters): LengthAwarePaginator
    {
        return Role::query()
            ->where('name', '!=', Role::SUPER_ADMIN)
            ->withCount(['permissions', 'users'])
            ->when($filters['search'] ?? null, function ($query, $term) {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('label', 'like', "%{$term}%"));
            })
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Role $role) => $role->toPayload());
    }

    /** @return array{role: ?Role, sections: Collection<int, array<string, mixed>>, selected: array<int, int>, guards: array<string, string>} */
    public function formData(?Role $role = null): array
    {
        $role?->load('permissions');

        return [
            'role' => $role,
            'sections' => $this->permissionSections(),
            'selected' => $role?->permissions->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [],
            'guards' => Role::GUARDS,
        ];
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create($this->attributes($data));
            $role->syncPermissions($this->permissions($data));

            return $role->loadCount(['permissions', 'users']);
        });
    }

    public function update(Role $role, array $data): Role
    {
        $this->rejectProtected($role);

        return DB::transaction(function () use ($role, $data) {
            $role->update($this->attributes($data));
            $role->syncPermissions($this->permissions($data));

            return $role->loadCount(['permissions', 'users']);
        });
    }

    public function delete(Role $role): void
    {
        $this->rejectProtected($role);

        if ($role->users()->exists()) {
            throw new DomainException('Bu role atanmış kullanıcılar var. Önce kullanıcıları başka bir role taşıyın.');
        }

        $role->delete();
    }

    private function rejectProtected(Role $role): void
    {
        if ($role->isProtected()) {
            throw new DomainException('Süper yönetici rolü panelden yönetilemez.');
        }
    }

    /** @return array{name: string, label: string, guard_name: string} */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'label' => $data['label'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ];
    }

    /** @return Collection<int, Permission> */
    private function permissions(array $data): Collection
    {
        $ids = collect($data['permissions'] ?? [])
            ->filter(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        return Permission::query()
            ->where('guard_name', $data['guard_name'] ?? 'web')
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Yetkiler önce config kategorisine, sonra rol formundaki bölüme göre
     * toplanır. Bölüme yazılmamış kategori "Diğer" altına düşer.
     *
     * @return Collection<int, array{key: string, title: string, groups: Collection<int, array{key: string, title: string, permissions: Collection<int, Permission>}>}>
     */
    private function permissionSections(): Collection
    {
        $titles = config('permissions.categories', []);
        $grouped = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->category ?: '_other');

        $groups = collect();

        foreach ($titles as $key => $title) {
            if (! $grouped->has($key)) {
                continue;
            }

            $groups->put($key, [
                'key' => $key,
                'title' => $title,
                'permissions' => $grouped->get($key),
            ]);
        }

        foreach ($grouped as $key => $permissions) {
            if ($groups->has($key)) {
                continue;
            }

            $groups->put($key, [
                'key' => $key,
                'title' => $key === '_other' ? 'Diğer' : $key,
                'permissions' => $permissions,
            ]);
        }

        $used = [];
        $sections = collect();

        foreach (config('permissions.sections', []) as $key => $section) {
            $sectionGroups = collect($section['categories'] ?? [])
                ->map(fn (string $category) => $groups->get($category))
                ->filter()
                ->values();

            if ($sectionGroups->isEmpty()) {
                continue;
            }

            $used = [...$used, ...$sectionGroups->pluck('key')->all()];

            $sections->push([
                'key' => $key,
                'title' => $section['title'],
                'groups' => $sectionGroups,
            ]);
        }

        $leftover = $groups->reject(fn (array $group) => in_array($group['key'], $used, true))->values();

        if ($leftover->isNotEmpty()) {
            $sections->push([
                'key' => 'other',
                'title' => 'Diğer',
                'groups' => $leftover,
            ]);
        }

        return $sections;
    }
}
