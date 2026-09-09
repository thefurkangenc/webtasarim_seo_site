<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Models\Role as SpatieRole;

#[Fillable(['name', 'guard_name', 'label'])]
class Role extends SpatieRole
{
    public const SUPER_ADMIN = 'super-admin';

    /** Panelde seçilebilir korumalar; şu an yalnızca web. */
    public const GUARDS = [
        'web' => 'web',
    ];

    public function isProtected(): bool
    {
        return $this->name === self::SUPER_ADMIN;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label ?: $this->name,
            'guard_name' => $this->guard_name,
            'permissions_count' => $this->permissions_count ?? 0,
            'users_count' => $this->users_count ?? 0,
            'is_protected' => $this->isProtected(),
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
