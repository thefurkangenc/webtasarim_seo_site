<?php

namespace App\Support;

/**
 * Paylaşılan bir video adresini (YouTube/Vimeo) gömülebilir hale çevirir.
 *
 * Kullanıcı adres çubuğundan ne kopyalarsa onu yapıştırır: izleme adresi,
 * kısa adres, gömme adresi, shorts, zaman damgalı adres... Hepsinden aynı
 * video kimliği çıkar. Tanınmayan adres için null döner — çağıran taraf
 * o zaman adresi olduğu gibi bir bağlantı olarak gösterir.
 *
 *   VideoEmbed::parse('https://youtu.be/abc123?t=30');
 *   // ['provider' => 'youtube', 'id' => 'abc123', 'embed_url' => '...', 'thumbnail' => '...']
 */
class VideoEmbed
{
    /** @return array{provider: string, id: string, embed_url: string, thumbnail: string|null}|null */
    public static function parse(?string $url): ?array
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if ($id = self::youtubeId($url)) {
            return [
                'provider' => 'youtube',
                'id' => $id,
                // nocookie: ziyaretçi videoyu oynatmadıkça çerez yazılmaz.
                'embed_url' => "https://www.youtube-nocookie.com/embed/{$id}",
                'thumbnail' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
            ];
        }

        if ($id = self::vimeoId($url)) {
            return [
                'provider' => 'vimeo',
                'id' => $id,
                'embed_url' => "https://player.vimeo.com/video/{$id}",
                // Vimeo kapak görseli yalnızca API ile alınır; kullanıcı kendi
                // kapak görselini seçebildiği için istek atmıyoruz.
                'thumbnail' => null,
            ];
        }

        return null;
    }

    public static function providerLabel(?string $url): ?string
    {
        return match (self::parse($url)['provider'] ?? null) {
            'youtube' => 'YouTube',
            'vimeo' => 'Vimeo',
            default => null,
        };
    }

    private static function youtubeId(string $url): ?string
    {
        $patterns = [
            '#youtu\.be/([A-Za-z0-9_-]{6,})#i',
            '#youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|v/|shorts/|live/)([A-Za-z0-9_-]{6,})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private static function vimeoId(string $url): ?string
    {
        return preg_match('#vimeo\.com/(?:video/|channels/[\w]+/|groups/[^/]+/videos/)?(\d+)#i', $url, $matches)
            ? $matches[1]
            : null;
    }
}
