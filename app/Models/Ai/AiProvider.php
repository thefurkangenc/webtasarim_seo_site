<?php

namespace App\Models\Ai;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'driver', 'base_url', 'api_key', 'model',
    'temperature', 'max_tokens', 'timeout', 'options', 'is_active', 'is_default',
])]
class AiProvider extends Model
{
    use LogsActivity;

    /** Log modül anahtarı: bu model 'ai' altında toplanır. */
    public function activityLogName(): string
    {
        return 'ai';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            // Anahtar veritabanında şifreli durur; okuma/yazma şeffaftır.
            'api_key' => 'encrypted',
            'options' => 'array',
            'temperature' => 'float',
            'max_tokens' => 'integer',
            'timeout' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /** config/ai.php içindeki sürücü tanımı. */
    public function definition(): array
    {
        return config("ai.drivers.{$this->driver}", []);
    }

    public function driverLabel(): string
    {
        return $this->definition()['label'] ?? $this->driver;
    }

    /** Modelden JSON istemek güvenli mi — hem sürücü hem sağlayıcı ayarı açık olmalı. */
    public function usesJsonMode(): bool
    {
        return ($this->definition()['supports_json'] ?? false)
            && ($this->options['json_mode'] ?? true);
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'driver' => $this->driver,
            'driver_label' => $this->driverLabel(),
            'model' => $this->model,
            'base_url' => $this->base_url,
            'has_key' => filled($this->api_key),
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
