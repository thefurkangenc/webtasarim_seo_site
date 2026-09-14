<?php

namespace App\Enums;

/**
 * Gelen talebin formu. Değer `leads.source` kolonuna yazılır
 * (`contact`, `quote`…); yeni bir form türü eklenince buraya case yazılır.
 */
enum LeadSource: string
{
    case Contact = 'contact';
    case Quote = 'quote';

    public function label(): string
    {
        return match ($this) {
            self::Contact => 'İletişim',
            self::Quote => 'Teklif',
        };
    }

    /** Trezo rozet paleti — `text-{color}-600 bg-{color}-100`. */
    public function color(): string
    {
        return match ($this) {
            self::Contact => 'primary',
            self::Quote => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Contact => 'mail',
            self::Quote => 'request_quote',
        };
    }

    public function detailTitle(): string
    {
        return match ($this) {
            self::Contact => 'İletişim talebi',
            self::Quote => 'Teklif talebi',
        };
    }

    public function replySubject(?string $original = null): string
    {
        return match ($this) {
            self::Quote => 'Re: Teklif talebiniz',
            self::Contact => 'Re: '.(filled($original) ? $original : 'Mesajınız hakkında'),
        };
    }

    public function replyPlaceholder(): string
    {
        return match ($this) {
            self::Quote => 'Merhaba, teklif talebiniz için teşekkürler…',
            self::Contact => 'Merhaba, mesajınız için teşekkürler…',
        };
    }

    public function quotedMessageLabel(): string
    {
        return match ($this) {
            self::Quote => 'Bize gönderdiğiniz teklif talebi',
            self::Contact => 'Bize gönderdiğiniz mesaj',
        };
    }

    public function notificationTitle(string $name): string
    {
        return match ($this) {
            self::Quote => "{$name} teklif talebi gönderdi",
            self::Contact => "{$name} yeni bir mesaj gönderdi",
        };
    }
}
