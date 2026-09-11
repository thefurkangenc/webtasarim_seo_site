<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Bir kullanıcının bildirim merkezinde görüp okuduğu öğe.
 *
 * LogsActivity KULLANMAZ: "bildirimi okudum" bir denetim olayı değil, arayüz
 * durumudur — loglanırsa günde yüzlerce satırla denetim kaydını işe yaramaz
 * hale getirir. (Projede bu trait'i atlayan tek model budur.)
 */
#[Fillable(['user_id', 'key'])]
class NotificationRead extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
