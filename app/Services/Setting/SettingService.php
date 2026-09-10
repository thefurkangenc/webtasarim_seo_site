<?php

namespace App\Services\Setting;

use App\Models\Media\Media;
use App\Models\Setting\Setting;
use App\Services\Integration\IntegrationService;
use App\Support\Activity;
use DomainException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class SettingService
{
    public function formData(string $group): array
    {
        $groups = config('settings.groups', []);

        if (! isset($groups[$group])) {
            throw new DomainException('Bu ayar grubu bulunamadı.');
        }

        $values = array_replace(config('settings.defaults.'.$group, []), $this->getGroup($group));

        if ($group === 'contact' && blank($values['to_email'] ?? null)) {
            $values['to_email'] = $this->get('company', 'email');
        }

        if ($group === 'mail') {
            $values['has_password'] = filled($values['password'] ?? null);
            unset($values['password']);
        }

        if ($group === 'analytics') {
            // Şifreli service account JSON hiçbir zaman view'e gitmez; yalnızca
            // "bağlı mı" bilgisi ve gizli olmayan tanımlayıcı e-posta gösterilir.
            $values['has_service_account'] = filled($values['service_account'] ?? null);
            unset($values['service_account']);
        }

        $company = $group === 'company' ? $values : $this->getGroup('company');
        $media = $this->resolveMedia($values);
        $companyMedia = $group === 'company' ? $media : $this->resolveMedia($company);
        $logo = $companyMedia['logo_media_id'] ?? null;
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'site.com';

        return [
            'group' => $group,
            'groups' => $groups,
            'values' => $values,
            'media' => $media,
            'integrations' => $group === 'integrations'
                ? app(IntegrationService::class)->cards()
                : [],
            'preview' => [
                'host' => $host,
                'site_name' => $company['name'] ?? $host,
                'favicon' => $logo?->url('medium'),
            ],
        ];
    }

    /** @return array<string, string|null> */
    public function getGroup(string $group): array
    {
        return Cache::remember("settings.{$group}", 3600, function () use ($group) {
            return Setting::query()
                ->where('group', $group)
                ->pluck('value', 'key')
                ->all();
        });
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return $this->getGroup($group)[$key] ?? $default;
    }

    /** @param  array<string, mixed>  $values */
    public function putGroup(string $group, array $values): void
    {
        // Diff için önceki değerler; döngüden ÖNCE okunmalı.
        $before = $this->getGroup($group);

        DB::transaction(function () use ($group, $values) {
            foreach ($values as $key => $value) {
                Setting::query()->updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value === null ? null : (string) $value],
                );
            }
        });

        Cache::forget("settings.{$group}");

        $this->logChanges($group, $before, $values);
    }

    /**
     * Ayar grubu için TEK bir log yazar. Setting modeline LogsActivity
     * eklenmedi: her ayar ayrı satır olduğu için tek kaydetmede onlarca
     * anlamsız log oluşurdu.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function logChanges(string $group, array $before, array $after): void
    {
        // Yalnızca gerçekten değişen anahtarlar; dokunulmayanlar loga girmez.
        $changed = array_keys(array_filter(
            $after,
            fn ($value, $key) => ($before[$key] ?? null) !== $value,
            ARRAY_FILTER_USE_BOTH,
        ));

        if ($changed === []) {
            return;
        }

        $label = config("settings.groups.{$group}.title") ?? $group;

        Activity::record(
            logName: 'setting',
            event: 'updated',
            description: "Site ayarları güncellendi: {$label}",
            subjectLabel: $label,
            // sanitize(): SMTP parolası, API anahtarı gibi değerler maskelenir.
            properties: [
                'old' => Activity::sanitize(array_intersect_key($before, array_flip($changed))),
                'new' => Activity::sanitize(array_intersect_key($after, array_flip($changed))),
            ],
            changedKeys: $changed,
        );
    }

    /** @param  array<string, mixed>  $data */
    public function updateMail(array $data): void
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Crypt::encryptString($data['password']);
        }

        $this->putGroup('mail', $data);
        $this->applyMailConfig();
    }

    /**
     * Panelden kaydedilen SMTP bilgisi varsa Laravel mailer'ı ona alır.
     * Host yoksa .env (ör. log) olduğu gibi kalır.
     */
    public function applyMailConfig(): void
    {
        try {
            $mail = $this->getGroup('mail');
        } catch (QueryException) {
            return;
        }

        if (blank($mail['host'] ?? null) || blank($mail['from_address'] ?? null)) {
            return;
        }

        $password = $mail['password'] ?? null;

        if (filled($password)) {
            try {
                $password = Crypt::decryptString($password);
            } catch (DecryptException) {
                $password = null;
            }
        }

        $encryption = $mail['encryption'] ?? 'tls';

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $mail['host'],
            'mail.mailers.smtp.port' => (int) ($mail['port'] ?? 587),
            'mail.mailers.smtp.username' => $mail['username'] ?? '',
            'mail.mailers.smtp.password' => $password ?? '',
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.auto_tls' => $encryption !== 'none',
            'mail.from.address' => $mail['from_address'],
            'mail.from.name' => filled($mail['from_name'] ?? null) ? $mail['from_name'] : config('mail.from.name'),
        ]);

        if (app()->resolved('mail.manager')) {
            app('mail.manager')->purge('smtp');
        }
    }

    /** Formdaki (veya kayıtlı) SMTP bilgileriyle sunucuya bağlanır. */
    public function testMail(array $data): void
    {
        $password = $data['password'] ?? null;

        if (blank($password)) {
            $stored = $this->get('mail', 'password');

            if (blank($stored)) {
                throw new DomainException('Şifre zorunludur.');
            }

            try {
                $password = Crypt::decryptString($stored);
            } catch (DecryptException) {
                throw new DomainException('Kayıtlı şifre okunamadı, yeniden girin.');
            }
        }

        $encryption = $data['encryption'] ?? 'tls';
        $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';

        $transport = (new EsmtpTransportFactory)->create(new Dsn(
            $scheme,
            (string) $data['host'],
            (string) ($data['username'] ?? ''),
            (string) $password,
            (int) $data['port'],
        ));

        $stream = $transport->getStream();

        if ($stream instanceof SocketStream) {
            $stream->setTimeout(10);

            if ($encryption === 'none') {
                $stream->disableTls();
            }
        }

        if ($encryption === 'none') {
            $transport->setAutoTls(false);
        }

        try {
            $transport->start();
            $transport->stop();
        } catch (TransportExceptionInterface $e) {
            throw new DomainException($this->mailErrorMessage($e));
        }
    }

    private function mailErrorMessage(TransportExceptionInterface $e): string
    {
        $raw = strtolower($e->getMessage());

        if (str_contains($raw, 'auth') || str_contains($raw, '535') || str_contains($raw, '534')) {
            return 'Kimlik doğrulama başarısız. Kullanıcı adı ve şifreyi kontrol edin.';
        }

        if (str_contains($raw, 'timed out') || str_contains($raw, 'timeout')) {
            return 'SMTP sunucusu yanıt vermedi. Port ve şifrelemeyi kontrol edin.';
        }

        if (str_contains($raw, 'connection') || str_contains($raw, 'unable to connect')) {
            return 'SMTP sunucusuna bağlanılamadı. Sunucu adresi, port ve şifrelemeyi kontrol edin.';
        }

        return 'SMTP sunucusuna bağlanılamadı.';
    }

    /**
     * Formdaki *_media_id anahtarlarını Media modele çevirir; görsel bileşeni
     * mevcut kaydı göstermek için bunu bekler.
     *
     * @param  array<string, string|null>  $values
     * @return array<string, Media|null>
     */
    private function resolveMedia(array $values): array
    {
        $ids = [];

        foreach ($values as $key => $value) {
            if (str_ends_with((string) $key, '_media_id') && $value) {
                $ids[$key] = (int) $value;
            }
        }

        if ($ids === []) {
            return [];
        }

        $records = Media::query()->findMany(array_unique(array_values($ids)))->keyBy('id');

        $media = [];

        foreach ($ids as $key => $id) {
            $media[$key] = $records->get($id);
        }

        return $media;
    }
}
