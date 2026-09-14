<?php

namespace App\Services\Setup;

use App\Models\Media\Media;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use App\Models\Module\Module;
use App\Models\Role\Role;
use App\Models\User;
use App\Services\Media\MediaService;
use App\Services\Menu\MenuRenderer;
use App\Services\Setting\SettingService;
use App\Support\Activity;
use App\Support\Ffmpeg;
use App\Support\ModuleRegistry;
use App\Support\Settings;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

/**
 * İlk kurulum. Adım verisi controller'dan gelir (oturum HTTP katmanında kalır).
 */
class SetupService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly MediaService $media,
        private readonly ModuleRegistry $modules,
    ) {}

    public function isComplete(): bool
    {
        try {
            return Settings::bool('setup.completed');
        } catch (QueryException) {
            // Tablo yoksa: kullanıcı varsa çalışan kurulum, yoksa sihirbaz.
            return $this->hasUsers();
        }
    }

    public function hasUsers(): bool
    {
        try {
            return Schema::hasTable('users') && User::query()->exists();
        } catch (QueryException) {
            return false;
        }
    }

    public function databaseReady(): bool
    {
        try {
            return Schema::hasTable('users')
                && Schema::hasTable('settings')
                && Schema::hasTable('roles');
        } catch (QueryException) {
            return false;
        }
    }

    public function markComplete(): void
    {
        $this->settings->putGroup('setup', [
            'completed' => '1',
            'completed_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * İçeriği, kullanıcıları ve ayarları siler; kurulum sihirbazı yeniden açılır.
     * Rol/izin ve seeder iskeleti (config/setup.php > keep_tables) durur.
     */
    public function reset(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (config('setup.reset_tables', []) as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $disk = Storage::disk((string) config('media.disk', 'public'));
        $directory = (string) config('media.directory', 'uploads');
        $disk->deleteDirectory($directory);
        $disk->makeDirectory($directory);

        File::deleteDirectory(storage_path('app/'.config('sitemap.path', 'sitemaps')));

        Cache::flush();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        MenuRenderer::forget('header');
        MenuRenderer::forget('footer_primary');
        MenuRenderer::forget('footer_secondary');
    }

    /**
     * Sihirbazın ilk ekranı: modül kartları, kilitli anahtarlar, logo önizlemesi.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function formData(array $payload = []): array
    {
        $locked = config('setup.locked_modules', []);
        $logoId = $payload['company']['logo_media_id'] ?? null;
        $logo = $logoId ? Media::query()->find($logoId) : null;

        return [
            'ready' => $this->databaseReady(),
            'steps' => config('setup.steps'),
            'tasks' => config('setup.tasks'),
            'lockedModules' => $locked,
            'logoPreview' => $logo?->url('medium'),
            'modules' => collect(config('modules.definitions', []))
                ->reject(fn (array $definition, string $key) => in_array($key, $locked, true))
                ->map(fn (array $definition, string $key) => [
                    'key' => $key,
                    'label' => $definition['label'],
                    'icon' => $definition['icon'],
                    'description' => $definition['description'],
                ])
                ->values()
                ->all(),
        ];
    }

    public function storeLogo(UploadedFile $file): int
    {
        return $this->media->store($file, ['name' => 'Firma logosu'])->id;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function runTask(string $task, array $payload): array
    {
        return match ($task) {
            'foundation' => $this->foundation(),
            'admin' => $this->createAdmin($payload['admin'] ?? []),
            'company' => $this->saveCompany($payload['company'] ?? []),
            'modules' => $this->saveModules($payload['modules'] ?? []),
            'contact' => $this->saveContact($payload),
            'mail' => $this->saveMail($payload['mail'] ?? []),
            'legal' => $this->saveLegal($payload),
            'menus' => $this->saveMenus($payload),
            'storage' => $this->linkStorage(),
            'ffmpeg' => $this->ffmpegStatus(),
            'finalize' => $this->finalize($payload),
            default => throw new DomainException('Bilinmeyen kurulum adımı.'),
        };
    }

    /** @return array<string, mixed> */
    private function foundation(): array
    {
        foreach (config('setup.seeders', []) as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        return ['message' => 'Altyapı hazır.'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function createAdmin(array $data): array
    {
        if (blank($data['email'] ?? null) || blank($data['password'] ?? null) || blank($data['name'] ?? null)) {
            throw new DomainException('Yönetici bilgileri eksik.');
        }

        $existing = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', Role::SUPER_ADMIN))
            ->first();

        if ($existing) {
            return ['message' => 'Yönetici hesabı zaten vardı.', 'user_id' => $existing->id];
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole(Role::SUPER_ADMIN);

        return ['message' => 'Yönetici hesabı oluşturuldu.', 'user_id' => $user->id];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function saveCompany(array $data): array
    {
        if (blank($data['name'] ?? null)) {
            throw new DomainException('Firma adı eksik.');
        }

        $this->settings->putGroup('company', [
            'name' => $data['name'],
            'legal_name' => $data['legal_name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'logo_media_id' => $data['logo_media_id'] ?? '',
        ]);

        $this->settings->putGroup('schema', [
            'business_type' => 'Organization',
        ]);

        return ['message' => 'Firma bilgileri kaydedildi.'];
    }

    /**
     * @param  array<string, bool>  $modules
     * @return array<string, mixed>
     */
    private function saveModules(array $modules): array
    {
        $locked = config('setup.locked_modules', []);

        foreach (array_keys(config('modules.definitions', [])) as $key) {
            $active = in_array($key, $locked, true) ? true : (bool) ($modules[$key] ?? false);

            Module::query()->updateOrCreate(
                ['key' => $key],
                ['is_active' => $active],
            );
        }

        $this->modules->flush();

        return ['message' => 'Modüller ayarlandı.'];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveContact(array $payload): array
    {
        $contact = $payload['contact'] ?? [];
        $companyEmail = $payload['company']['email'] ?? '';

        if (! empty($contact['skipped'])) {
            $this->settings->putGroup('contact', [
                'enabled' => '1',
                'to_email' => $companyEmail,
            ]);

            return ['message' => 'İletişim formu varsayılanlarla bırakıldı.'];
        }

        $this->settings->putGroup('contact', [
            'enabled' => filter_var($contact['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'to_email' => $contact['to_email'] ?? $companyEmail,
            'cc_email' => $contact['cc_email'] ?? '',
            'auto_reply_enabled' => filter_var($contact['auto_reply_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
        ]);

        return ['message' => 'İletişim formu ayarlandı.'];
    }

    /**
     * @param  array<string, mixed>  $mail
     * @return array<string, mixed>
     */
    private function saveMail(array $mail): array
    {
        if (! empty($mail['skipped']) || blank($mail['host'] ?? null)) {
            return ['message' => 'E-posta adımı atlandı.'];
        }

        $this->settings->updateMail([
            'host' => $mail['host'],
            'port' => $mail['port'] ?? 587,
            'username' => $mail['username'] ?? '',
            'password' => $mail['password'] ?? null,
            'encryption' => $mail['encryption'] ?? 'tls',
            'from_name' => $mail['from_name'] ?? '',
            'from_address' => $mail['from_address'],
        ]);

        return ['message' => 'E-posta ayarları kaydedildi.'];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveLegal(array $payload): array
    {
        $legal = $payload['legal'] ?? [];

        if (! empty($legal['skipped'])) {
            return ['message' => 'Yasal sayfalar atlandı.'];
        }

        $name = e($payload['company']['name'] ?? 'Şirket');
        $email = e($payload['company']['email'] ?? '');

        $replace = ['{name}' => $name, '{email}' => $email ?: 'iletişim e-postanız'];

        $this->settings->putGroup('contents', [
            'kvkk_content' => strtr((string) config('setup.legal.kvkk'), $replace),
            'cookie_content' => strtr((string) config('setup.legal.cookie'), $replace),
        ]);

        $this->settings->putGroup('cookie', [
            'enabled' => '1',
        ]);

        return ['message' => 'Yasal sayfa şablonları yazıldı.'];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveMenus(array $payload): array
    {
        $modules = $payload['modules'] ?? [];
        $legalSkipped = ! empty($payload['legal']['skipped']);

        $header = [
            ['label' => 'Ana Sayfa', 'route_name' => 'anasayfa'],
            ['label' => 'Hakkımızda', 'route_name' => 'hakkimizda'],
        ];

        if ($this->moduleOn($modules, 'service')) {
            $header[] = ['label' => 'Hizmetler', 'route_name' => 'hizmetler'];
        }

        if ($this->moduleOn($modules, 'blog')) {
            $header[] = ['label' => 'Blog', 'route_name' => 'blog'];
        }

        if ($this->moduleOn($modules, 'project')) {
            $header[] = ['label' => 'Neler Yaptık', 'route_name' => 'projeler'];
        }

        $header[] = ['label' => 'İletişim', 'route_name' => 'iletisim'];

        $footer = $header;

        if (! $legalSkipped) {
            $footer[] = ['label' => 'KVKK', 'route_name' => 'kvkk'];
            $footer[] = ['label' => 'Çerez Politikası', 'route_name' => 'cerez-politikasi'];
        }

        $this->fillMenu('header', $header);
        $this->fillMenu('footer_primary', $footer);

        return ['message' => 'Menüler kuruldu.'];
    }

    /** @return array<string, mixed> */
    private function linkStorage(): array
    {
        try {
            Artisan::call('storage:link');
        } catch (Throwable) {
            // Link zaten varsa veya symlink desteklenmiyorsa kurulum yarıda kalmasın.
        }

        return ['message' => 'Dosya bağlantısı hazır.'];
    }

    /** @return array<string, mixed> */
    private function ffmpegStatus(): array
    {
        $installed = Ffmpeg::installed();
        $path = Ffmpeg::ffmpeg();

        return [
            'message' => $installed
                ? 'ffmpeg kurulu'.($path ? " ({$path})" : '').'.'
                : 'ffmpeg bulunamadı — video kopyaları üretilmez, kurulum devam eder.',
            'installed' => $installed,
            'os' => PHP_OS_FAMILY,
            'path' => $path,
            'command' => Ffmpeg::installCommand(),
            'warning' => ! $installed,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function finalize(array $payload): array
    {
        $this->markComplete();

        $user = User::query()->where('email', $payload['admin']['email'] ?? '')->first()
            ?? User::query()->whereHas('roles', fn ($query) => $query->where('name', Role::SUPER_ADMIN))->first();

        Activity::record(
            logName: 'setup',
            event: 'created',
            description: 'Kurulum sihirbazı tamamlandı.',
            subjectLabel: $payload['company']['name'] ?? 'Site',
            causer: $user,
        );

        return [
            'message' => 'Kurulum tamamlandı.',
            'user_id' => $user?->id,
        ];
    }

    public function userById(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    /** @param  list<array{label: string, route_name: string}>  $items */
    private function fillMenu(string $key, array $items): void
    {
        $menu = Menu::query()->where('key', $key)->first();

        if (! $menu) {
            return;
        }

        if ($menu->items()->exists()) {
            return;
        }

        foreach ($items as $index => $item) {
            $menu->items()->create([
                'label' => $item['label'],
                'link_type' => MenuItem::TYPE_ROUTE,
                'route_name' => $item['route_name'],
                'status' => true,
                'sort_order' => $index,
            ]);
        }

        MenuRenderer::forget($key);
    }

    /** @param  array<string, bool>  $modules */
    private function moduleOn(array $modules, string $key): bool
    {
        if (in_array($key, config('setup.locked_modules', []), true)) {
            return true;
        }

        return (bool) ($modules[$key] ?? false);
    }
}
