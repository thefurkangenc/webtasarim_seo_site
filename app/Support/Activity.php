<?php

namespace App\Support;

use App\Models\ActivityLog\ActivityLog;
use App\Services\ActivityLog\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

/**
 * Denetim kaydı yazmak için kısa yol.
 *
 * Model ekleme/düzenleme/silme için buna gerek YOKTUR — modele
 * App\Models\Concerns\LogsActivity trait'ini ekle, yeter. Bu sınıf model
 * olayı olmayan durumlar içindir:
 *
 *   Activity::record('auth', 'login', 'Panele giriş yapıldı');
 *
 *   Activity::record('media', 'bulk_delete', '3 dosya silindi',
 *       properties: ['new' => ['ids' => [4, 7, 9]]]);
 *
 * Loglama HİÇBİR ZAMAN asıl işlemi bozmamalı: yazma başarısız olursa
 * (tablo yok, disk dolu, kuyruk kapalı) hata yutulur ve uygulama akışı
 * devam eder. Sessiz kalmasın diye laravel.log'a yazılır.
 */
class Activity
{
    /**
     * @param  array<string, mixed>  $properties
     * @param  array<int, string>  $changedKeys
     */
    public static function record(
        string $logName,
        string $event,
        string $description,
        ?Model $subject = null,
        ?string $subjectLabel = null,
        array $properties = [],
        array $changedKeys = [],
        ?string $severity = null,
        ?Model $causer = null,
    ): ?ActivityLog {
        try {
            return app(ActivityLogger::class)->log(
                logName: $logName,
                event: $event,
                description: $description,
                subject: $subject,
                subjectLabel: $subjectLabel,
                properties: $properties,
                changedKeys: $changedKeys,
                severity: $severity,
                causer: $causer,
            );
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Bir öznitelik dizisini loglanabilir hale getirir: yok sayılan alanları
     * atar, hassas olanları maskeler, çok uzun metinleri kırpar.
     *
     * Bu mantık TEK YERDE durmalı — hem LogsActivity trait'i hem de elle
     * Activity::record() çağıran servisler (ayarlar gibi) bunu kullanır.
     * Kopyalanırsa bir kopyada unutulan maskeleme sızıntı demektir.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $extraIgnored
     * @return array<string, mixed>
     */
    public static function sanitize(array $attributes, array $extraIgnored = []): array
    {
        $ignored = [...config('activity-log.ignore', []), ...$extraIgnored];
        $redact = config('activity-log.redact', []);
        $max = (int) config('activity-log.max_value_length', 500);

        $result = [];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $ignored, true)) {
                continue;
            }

            // Alan adı hassas anahtarlardan birini içeriyorsa değer gizlenir;
            // alanın DEĞİŞTİĞİ bilgisi yine kalır.
            foreach ($redact as $needle) {
                if (str_contains(strtolower((string) $key), $needle)) {
                    $result[$key] = '•••';

                    continue 2;
                }
            }

            if (is_string($value) && mb_strlen($value) > $max) {
                $value = mb_substr($value, 0, $max).'…';
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Yetkisiz erişim denemesi. Hem FormRequest::authorize() reddinde hem de
     * permission/role middleware reddinde çağrılır (bkz. bootstrap/app.php).
     *
     * Route adının ilk parçası modül anahtarı olarak kullanılır:
     * `admin.blog.destroy` -> 'blog'. Böylece deneme, ilgili modülün log
     * listesinde de görünür.
     */
    public static function forbidden(Request $request): ?ActivityLog
    {
        $route = $request->route()?->getName() ?? '';
        // admin. önekini at, ilk segmenti al: admin.blog.destroy -> blog
        $module = explode('.', str_replace('admin.', '', $route))[0] ?: 'security';

        $user = $request->user();
        $who = $user ? $user->email : 'oturumsuz ziyaretçi';

        return self::record(
            logName: config("activity-log.modules.{$module}") ? $module : 'security',
            event: 'forbidden',
            description: "Yetkisiz erişim denemesi ({$who}): {$request->method()} {$request->path()}",
            properties: ['new' => ['route' => $route ?: null, 'path' => $request->path()]],
        );
    }
}
