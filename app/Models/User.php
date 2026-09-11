<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\LogsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasMedia, HasRoles, LogsActivity, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
}
