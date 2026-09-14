<?php

namespace App\Services\User;

use App\Models\Country\Country;
use App\Models\Role\Role;
use App\Models\User;
use App\Support\Activity;
use App\Support\Phone;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserService
{
    /** @param  array<string, mixed>  $filters */
    public function list(array $filters, User $actor): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles', 'country', 'media'])
            ->when($filters['search'] ?? null, function ($query, $term) {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->when($filters['role_id'] ?? null, fn ($query, $roleId) => $query->role((int) $roleId))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (User $user) => $this->payload($user, $actor));
    }

    /** @return array<string, mixed> */
    public function indexData(): array
    {
        return [
            'stats' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
            ],
            'roles' => $this->assignableRoles(),
        ];
    }

    /** @return array<string, mixed> */
    public function formData(?User $user, User $actor): array
    {
        $user?->load(['roles', 'country', 'media']);

        return [
            'user' => $user,
            'countries' => Country::query()->active()->ordered()->get(),
            'roles' => $this->assignableRoles(),
            'roleId' => $user?->roles->first()?->id,
            'lockRole' => $user !== null && ($user->isSuperAdmin() || $user->is($actor)),
            'lockStatus' => $user !== null && ($user->isSuperAdmin() || $user->is($actor)),
        ];
    }

    /** @param  array<string, mixed>  $data */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $this->assignableRole((int) $data['role_id']);
            $user = User::create($this->attributes($data, creating: true));
            $user->syncRoles([$role]);
            $user->syncMedia($data['avatar_media_id'] ?? null, 'avatar');

            return $user->load(['roles', 'country', 'media']);
        });
    }

    /** @param  array<string, mixed>  $data */
    public function update(User $user, array $data, User $actor): User
    {
        $this->assertCanManage($user, $actor);

        return DB::transaction(function () use ($user, $data, $actor) {
            $previousRole = $user->roleLabel();
            $hadPassword = filled($data['password'] ?? null);

            $user->update($this->attributes($data, creating: false, user: $user, actor: $actor));

            if (! $this->roleIsLocked($user, $actor) && isset($data['role_id'])) {
                $role = $this->assignableRole((int) $data['role_id']);
                $user->syncRoles([$role]);
                $user->unsetRelation('roles');
            }

            $user->syncMedia($data['avatar_media_id'] ?? null, 'avatar');
            $user->load(['roles', 'country', 'media']);

            if ($user->roleLabel() !== $previousRole) {
                Activity::record(
                    logName: 'user',
                    event: 'updated',
                    description: "Kullanıcının rolü değiştirildi: {$previousRole} → {$user->roleLabel()}",
                    subject: $user,
                    subjectLabel: $user->name,
                    properties: ['old' => ['role' => $previousRole], 'new' => ['role' => $user->roleLabel()]],
                    causer: $actor,
                );
            }

            if ($hadPassword) {
                Activity::record(
                    logName: 'user',
                    event: 'password_changed',
                    description: "Yönetici tarafından şifre değiştirildi: {$user->email}",
                    subject: $user,
                    subjectLabel: $user->name,
                    causer: $actor,
                );
            }

            return $user;
        });
    }

    public function delete(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw new DomainException('Kendi hesabınızı silemezsiniz.');
        }

        if ($user->isSuperAdmin()) {
            throw new DomainException('Süper yönetici hesabı silinemez.');
        }

        $this->assertCanManage($user, $actor);

        $user->syncMedia(null, 'avatar');
        $user->delete();
    }

    public function canView(User $user, User $actor): bool
    {
        return $actor->isSuperAdmin() || ! $user->isSuperAdmin();
    }

    public function assertCanManage(User $user, User $actor): void
    {
        if (! $this->canView($user, $actor)) {
            throw new DomainException('Süper yönetici hesabı yalnızca süper yönetici tarafından yönetilebilir.');
        }
    }

    /** @return Collection<int|string, string> */
    public function assignableRoles(): Collection
    {
        return Role::query()
            ->where('name', '!=', Role::SUPER_ADMIN)
            ->orderBy('label')
            ->get()
            ->mapWithKeys(fn (Role $role) => [$role->id => $role->label ?: $role->name]);
    }

    private function assignableRole(int $id): Role
    {
        $role = Role::query()
            ->where('name', '!=', Role::SUPER_ADMIN)
            ->find($id);

        if (! $role) {
            throw new DomainException('Seçilen rol atanamaz.');
        }

        return $role;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, bool $creating, ?User $user = null, ?User $actor = null): array
    {
        $country = isset($data['country_id'])
            ? Country::query()->find($data['country_id'])
            : null;

        $phone = Phone::normalize($data['phone'] ?? null, $country);

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'country_id' => $data['country_id'] ?? null,
            'phone' => $phone !== '' ? $phone : null,
        ];

        if ($creating || filled($data['password'] ?? null)) {
            $attributes['password'] = $data['password'];
        }

        if ($creating || ($user && $actor && ! $this->statusIsLocked($user, $actor))) {
            $attributes['is_active'] = (bool) ($data['is_active'] ?? true);
        }

        return $attributes;
    }

    private function roleIsLocked(User $user, User $actor): bool
    {
        return $user->isSuperAdmin() || $user->is($actor);
    }

    private function statusIsLocked(User $user, User $actor): bool
    {
        return $user->isSuperAdmin() || $user->is($actor);
    }

    /** @return array<string, mixed> */
    private function payload(User $user, User $actor): array
    {
        $protected = $user->isSuperAdmin();
        $self = $user->is($actor);
        $canView = $this->canView($user, $actor);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->formattedPhone(),
            'role' => $user->roleLabel(),
            'is_active' => $user->is_active,
            'is_self' => $self,
            'is_protected' => $protected,
            'avatar' => $user->avatarUrl(),
            'initials' => $user->initials(),
            'last_login_at' => $user->last_login_at?->format('d.m.Y H:i'),
            'created_at' => $user->created_at?->format('d.m.Y H:i'),
            'can_edit' => $canView,
            'can_delete' => $canView && ! $self && ! $protected,
        ];
    }
}
