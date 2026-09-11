<?php

namespace App\Services\Integration;

use App\Services\Setting\SettingService;
use DomainException;

class IntegrationService
{
    public function __construct(private readonly SettingService $settings) {}

    /** @return list<array<string, mixed>> */
    public function cards(): array
    {
        $stored = $this->settings->getGroup('integrations');
        $cards = [];

        foreach (config('integrations', []) as $key => $meta) {
            $values = $this->decode($stored[$key] ?? null);
            $cards[] = [
                'key' => $key,
                'title' => $meta['title'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'placement' => $meta['placement'] ?? null,
                'enabled' => (bool) ($values['enabled'] ?? false),
                // Bilgileri eksikse entegrasyon açık olsa da ön yüzde görünmez
                // (frontend() aynı kontrolü yapıyor) — panel bunu uyarı olarak gösterir.
                'ready' => $this->ready($key, $values),
                'values' => $values,
            ];
        }

        return $cards;
    }

    /** @return array<string, mixed> */
    public function formData(string $key): array
    {
        $meta = $this->meta($key);
        $values = $this->values($key);

        if (in_array($key, ['whatsapp', 'phone'], true) && blank($values['phone'] ?? null)) {
            $values['phone'] = $this->settings->get('company', 'phone');
        }

        return [
            'key' => $key,
            'title' => $meta['title'],
            'enabled' => (bool) ($values['enabled'] ?? false),
            'values' => $values,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(string $key, array $data): array
    {
        $this->meta($key);

        $payload = match ($key) {
            'whatsapp' => [
                'enabled' => true,
                'phone' => $data['phone'],
                'message' => $data['message'] ?? null,
                'position' => $data['position'] ?? 'right',
            ],
            'tawk' => [
                'enabled' => true,
                'property_id' => $data['property_id'],
                'widget_id' => $data['widget_id'],
            ],
            'phone' => [
                'enabled' => true,
                'phone' => $data['phone'],
                'position' => $data['position'] ?? 'right',
            ],
            'maps' => [
                'enabled' => true,
                'api_key' => $data['api_key'],
            ],
            default => throw new DomainException('Bu entegrasyon bulunamadı.'),
        };

        $this->put($key, $payload);

        return $this->card($key);
    }

    /** @return array<string, mixed> */
    public function toggle(string $key, bool $enabled): array
    {
        $values = $this->values($key);

        if ($enabled && ! $this->ready($key, $values)) {
            throw new DomainException('Önce entegrasyon bilgilerini kaydedin.');
        }

        $values['enabled'] = $enabled;
        $this->put($key, $values);

        return $this->card($key);
    }

    /** @return array<string, array<string, mixed>> */
    public function frontend(): array
    {
        $widgets = [];
        $offset = ['left' => 0, 'right' => 0];

        foreach ($this->cards() as $card) {
            if (! $card['enabled'] || ! $this->ready($card['key'], $card['values'])) {
                continue;
            }

            $values = $card['values'];
            $position = $values['position'] ?? 'right';

            if ($card['key'] === 'whatsapp') {
                $values['url'] = $this->whatsappUrl($values);
                $values['offset'] = $offset[$position];
                $offset[$position] += 68;
            }

            if ($card['key'] === 'phone') {
                $values['url'] = 'tel:+'.$this->digits((string) $values['phone']);
                $values['offset'] = $offset[$position];
                $offset[$position] += 68;
            }

            if ($card['key'] === 'maps') {
                continue;
            }

            $widgets[$card['key']] = $values;
        }

        return $widgets;
    }

    public function mapsApiKey(): ?string
    {
        $values = $this->values('maps');

        if (! ($values['enabled'] ?? false) || blank($values['api_key'] ?? null)) {
            return null;
        }

        return (string) $values['api_key'];
    }

    /** @return array<string, mixed> */
    private function card(string $key): array
    {
        foreach ($this->cards() as $card) {
            if ($card['key'] === $key) {
                return $card;
            }
        }

        throw new DomainException('Bu entegrasyon bulunamadı.');
    }

    /** @return array<string, mixed> */
    private function meta(string $key): array
    {
        $meta = config('integrations.'.$key);

        if (! is_array($meta)) {
            throw new DomainException('Bu entegrasyon bulunamadı.');
        }

        return $meta;
    }

    /** @return array<string, mixed> */
    private function values(string $key): array
    {
        return $this->decode($this->settings->get('integrations', $key));
    }

    /** @param  array<string, mixed>  $values */
    private function put(string $key, array $values): void
    {
        $this->settings->putGroup('integrations', [
            $key => json_encode($values, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /** @return array<string, mixed> */
    private function decode(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @param  array<string, mixed>  $values */
    private function ready(string $key, array $values): bool
    {
        return match ($key) {
            'whatsapp', 'phone' => filled($values['phone'] ?? null),
            'tawk' => filled($values['property_id'] ?? null) && filled($values['widget_id'] ?? null),
            'maps' => filled($values['api_key'] ?? null),
            default => false,
        };
    }

    /** @param  array<string, mixed>  $values */
    private function whatsappUrl(array $values): string
    {
        $url = 'https://wa.me/'.$this->digits((string) $values['phone']);

        if (filled($values['message'] ?? null)) {
            $url .= '?text='.rawurlencode((string) $values['message']);
        }

        return $url;
    }

    private function digits(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '90'.substr($digits, 1);
        }

        return $digits;
    }
}
