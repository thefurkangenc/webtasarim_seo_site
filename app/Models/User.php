<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\LogsActivity;
use App\Models\Country\Country;
use App\Models\Role\Role;
use App\Support\Phone;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'country_id', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasMedia, HasRoles, LogsActivity, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Header ve profil ekranındaki avatar. Yüklenmiş bir görsel yoksa null
     * döner; çağıran taraf o zaman baş harflerden oluşan bir daire basar —
     * şablondan kalan sabit "admin.png" artık kullanılmıyor.
     */
    public function avatarUrl(?string $conversion = 'thumb'): ?string
    {
        return $this->getFirstMedia('avatar')?->url($conversion);
    }

    /** Avatar yoksa gösterilecek baş harfler: "İbrahim Oğlakcı" -> "İO". */
    public function initials(): string
    {
        return collect(preg_split('/\s+/u', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->join('');
    }

    /**
     * Rollerin okunabilir adı. `roles.label` doldurulmuşsa o, değilse rolün
     * teknik adı kullanılır; birden çok rol varsa virgülle birleşir.
     */
    public function roleLabel(): string
    {
        return $this->roles
            ->map(fn ($role) => $role->label ?: $role->name)
            ->join(', ') ?: 'Kullanıcı';
    }

    public function formattedPhone(): ?string
    {
        if (blank($this->phone) || ! $this->country) {
            return null;
        }

        $national = Phone::format($this->phone, $this->country->mask);

        return $national !== '' ? '+'.$this->country->dial_code.' '.$national : null;
    }

    public function phoneHref(): ?string
    {
        return Phone::href($this->phone, $this->country);
    }

    /** Giriş damgası denetim kaydına düşmesin diye yok sayılır. */
    public function activityIgnored(): array
    {
        return ['last_login_at'];
    }
}
