<?php

namespace App\Services\Health;

/**
 * Tek bir sağlık kontrolünün sonucu. Kontrol sınıfları bunu üretir,
 * HealthService diziye çevirip cache'ler ve panele verir.
 */
class Check
{
    /** @param array<string, mixed> $meta */
    private function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $status,
        public readonly string $message,
        public readonly ?string $hint,
        public readonly array $meta,
    ) {}

    /** @param array<string, mixed> $meta */
    public static function ok(string $key, string $label, string $message, array $meta = []): self
    {
        return new self($key, $label, 'ok', $message, null, $meta);
    }

    /** @param array<string, mixed> $meta */
    public static function warning(string $key, string $label, string $message, ?string $hint = null, array $meta = []): self
    {
        return new self($key, $label, 'warning', $message, $hint, $meta);
    }

    /** @param array<string, mixed> $meta */
    public static function critical(string $key, string $label, string $message, ?string $hint = null, array $meta = []): self
    {
        return new self($key, $label, 'critical', $message, $hint, $meta);
    }

    /**
     * Kurulu olmayan servis (ör. SMTP girilmemiş) hata değildir; sayaçlara
     * girmez, kartta soluk gösterilir.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function skipped(string $key, string $label, string $message, ?string $hint = null, array $meta = []): self
    {
        return new self($key, $label, 'skipped', $message, $hint, $meta);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'status' => $this->status,
            'message' => $this->message,
            'hint' => $this->hint,
            'meta' => $this->meta,
            'order' => config('health.statuses.'.$this->status.'.order', 9),
        ];
    }
}
