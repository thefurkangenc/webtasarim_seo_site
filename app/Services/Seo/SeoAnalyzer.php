<?php

namespace App\Services\Seo;

/**
 * Yoast tarzı SEO analizi. `config/seo.php` eşiklerini kullanır; birebir aynı
 * mantık tarayıcıda `core/seo-analyzer.js` içinde de var (canlı panel). Bu
 * sınıf yetkili olandır: `HasSeo::syncSeo()` kaydederken çalışır ve skoru
 * `seo` tablosuna yazar.
 */
class SeoAnalyzer
{
    /**
     * @param  array{focus_keyword?: string, title?: string, description?: string, slug?: string, content?: string, url_host?: string, type?: string}  $input
     * @return array{score: int, grade: string, readability: int|null, readability_label: string, checks: list<array{id: string, label: string, status: string, text: string}>}
     */
    public function analyze(array $input): array
    {
        $rules = config('seo');

        $keyword = $this->norm($input['focus_keyword'] ?? '');
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $html = (string) ($input['content'] ?? '');
        $host = (string) ($input['url_host'] ?? '');
        $type = (string) ($input['type'] ?? 'default');

        $text = $this->plainText($html);
        $words = $this->words($text);
        $wordCount = count($words);
        $sentences = $this->sentences($text);
        $paragraphs = $this->paragraphs($html, $text);
        $headings = $this->headings($html);
        $links = $this->links($html, $host);
        $images = $this->images($html);
        $intro = $this->norm(implode(' ', array_slice($words, 0, (int) $rules['first_paragraph_words'])));

        $hasKw = $keyword !== '';
        $checks = [];

        $checks[] = $this->row('keyword_set', 'Odak anahtar kelime', $hasKw ? 'good' : 'bad',
            $hasKw ? "Odak kelime belirlendi: “{$keyword}”." : 'Bir odak anahtar kelime belirleyin — analiz buna göre yapılır.');

        $checks[] = $this->kw('keyword_in_title', 'Anahtar kelime meta başlıkta', $hasKw,
            str_contains($this->norm($title), $keyword),
            'Meta başlık odak kelimeyi içeriyor.', 'Meta başlıkta odak kelime geçmiyor.');

        $checks[] = $this->kw('keyword_in_description', 'Anahtar kelime meta açıklamada', $hasKw,
            str_contains($this->norm($description), $keyword),
            'Meta açıklama odak kelimeyi içeriyor.', 'Meta açıklamada odak kelime geçmiyor.');

        $checks[] = $this->kw('keyword_in_slug', 'Anahtar kelime adreste (slug)', $hasKw,
            $slug !== '' && str_contains($this->slugify($slug), $this->slugify($keyword)),
            'Adres odak kelimeyi içeriyor.', 'Sayfa adresinde odak kelime geçmiyor.');

        $checks[] = $this->kw('keyword_in_intro', 'Anahtar kelime ilk paragrafta', $hasKw,
            str_contains($intro, $keyword),
            'Odak kelime girişte (ilk paragrafta) geçiyor.', 'Odak kelimeyi ilk paragrafta kullanın.');

        $checks[] = $this->kw('keyword_in_subheading', 'Anahtar kelime bir ara başlıkta', $hasKw,
            $this->anyContains($headings, $keyword),
            'En az bir H2/H3 odak kelimeyi içeriyor.', 'Ara başlıkların hiçbirinde odak kelime yok.');

        $checks[] = $this->imagesAltKeyword($hasKw, $keyword, $images);

        $checks[] = $this->density($hasKw, $keyword, $words, $wordCount, $rules['density']);

        $checks[] = $this->length('title_length', 'Meta başlık uzunluğu', mb_strlen($title), $rules['title'],
            'Meta başlık ideal uzunlukta.', 'Meta başlık %s (ideal %d–%d karakter).', 'karakter');

        $checks[] = $this->length('description_length', 'Meta açıklama uzunluğu', mb_strlen($description), $rules['description'],
            'Meta açıklama ideal uzunlukta.', 'Meta açıklama %s (ideal %d–%d karakter).', 'karakter');

        $checks[] = $this->contentLength($wordCount, (int) ($rules['min_words'][$type] ?? $rules['min_words']['default']));

        $checks[] = $this->imagesHaveAlt($images);

        $checks[] = $this->internalLinks($links, $wordCount);

        $checks[] = $this->outboundLinks($links, $wordCount);

        $checks[] = $this->paragraphLength($paragraphs, (int) $rules['paragraph_max_words']);

        $checks[] = $this->sentenceLength($sentences, $rules);

        $checks[] = $this->subheadingDistribution($html, $text, $wordCount, $headings, (int) $rules['section_max_words']);

        [$readability, $readabilityLabel, $readabilityCheck] = $this->readability($words, $sentences, $rules);
        $checks[] = $readabilityCheck;

        $score = $this->score($checks, $rules['weights']);

        return [
            'score' => $score,
            'grade' => $this->grade($score, $rules['grade']),
            'readability' => $readability,
            'readability_label' => $readabilityLabel,
            'checks' => array_values($checks),
        ];
    }

    /* ------------------------------------------------------------------ *
     | Kontroller
     * ------------------------------------------------------------------ */

    /** @param  list<array{alt: string}>  $images */
    private function imagesAltKeyword(bool $hasKw, string $keyword, array $images): array
    {
        if (! $hasKw || $images === []) {
            return $this->row('keyword_in_image_alt', 'Anahtar kelime görsel alt metninde', 'na',
                $images === [] ? 'İçerikte görsel yok.' : 'Önce odak kelime belirleyin.');
        }

        $hit = $this->anyContains(array_column($images, 'alt'), $keyword);

        return $this->row('keyword_in_image_alt', 'Anahtar kelime görsel alt metninde', $hit ? 'good' : 'ok',
            $hit ? 'En az bir görselin alt metni odak kelimeyi içeriyor.' : 'Görsel alt metinlerinde odak kelime geçmiyor.');
    }

    /**
     * @param  list<string>  $words
     * @param  array{min: float, max: float}  $range
     */
    private function density(bool $hasKw, string $keyword, array $words, int $wordCount, array $range): array
    {
        if (! $hasKw || $wordCount < 50) {
            return $this->row('keyword_density', 'Anahtar kelime yoğunluğu', 'na',
                $wordCount < 50 ? 'Yoğunluk için içerik çok kısa.' : 'Önce odak kelime belirleyin.');
        }

        $kwWords = $this->words($keyword);
        $occurrences = $this->phraseCount($words, $kwWords);
        $density = round($occurrences * max(count($kwWords), 1) / $wordCount * 100, 2);

        $status = match (true) {
            $density >= $range['min'] && $density <= $range['max'] => 'good',
            $density > 0 && $density < $range['min'] => 'ok',
            $density > $range['max'] && $density <= $range['max'] * 2 => 'ok',
            default => 'bad',
        };

        $text = match ($status) {
            'good' => "Yoğunluk %{$density} — ideal aralıkta ({$occurrences} kez).",
            'ok' => $density < $range['min']
                ? "Yoğunluk %{$density} — biraz düşük (ideal %{$range['min']}–%{$range['max']})."
                : "Yoğunluk %{$density} — biraz yüksek (ideal %{$range['min']}–%{$range['max']}).",
            default => $density == 0.0
                ? 'Odak kelime içerikte hiç geçmiyor.'
                : "Yoğunluk %{$density} — aşırı (keyword stuffing riski).",
        };

        return $this->row('keyword_density', 'Anahtar kelime yoğunluğu', $status, $text);
    }

    /** @param  array{min: int, max: int}  $range */
    private function length(string $id, string $label, int $len, array $range, string $goodText, string $badFmt, string $unit): array
    {
        if ($len === 0) {
            return $this->row($id, $label, 'bad', 'Boş — '.mb_strtolower($label).' girin.');
        }

        $status = match (true) {
            $len >= $range['min'] && $len <= $range['max'] => 'good',
            $len >= $range['min'] - 5 && $len <= $range['max'] + 10 => 'ok',
            default => 'bad',
        };

        return $this->row($id, $label, $status,
            $status === 'good' ? $goodText : sprintf($badFmt, "{$len} {$unit}", $range['min'], $range['max']));
    }

    private function contentLength(int $wordCount, int $min): array
    {
        $status = match (true) {
            $wordCount >= $min => 'good',
            $wordCount >= (int) round($min * 0.6) => 'ok',
            default => 'bad',
        };

        return $this->row('content_length', 'İçerik uzunluğu', $status,
            $status === 'good'
                ? "İçerik {$wordCount} kelime — yeterli."
                : "İçerik {$wordCount} kelime (en az {$min} önerilir).");
    }

    /** @param  list<array{alt: string}>  $images */
    private function imagesHaveAlt(array $images): array
    {
        if ($images === []) {
            return $this->row('images_have_alt', 'Görsel alt metinleri', 'na', 'İçerikte görsel yok.');
        }

        $missing = count(array_filter($images, fn ($i) => trim($i['alt']) === ''));

        $status = $missing === 0 ? 'good' : ($missing < count($images) ? 'ok' : 'bad');

        return $this->row('images_have_alt', 'Görsel alt metinleri', $status,
            $missing === 0
                ? count($images).' görselin tamamında alt metni var.'
                : "{$missing}/".count($images).' görselde alt metni eksik.');
    }

    /** @param  list<array{internal: bool}>  $links */
    private function internalLinks(array $links, int $wordCount): array
    {
        if ($wordCount < 50) {
            return $this->row('internal_links', 'İç bağlantılar', 'na', 'İçerik çok kısa.');
        }

        $count = count(array_filter($links, fn ($l) => $l['internal']));

        return $this->row('internal_links', 'İç bağlantılar', $count > 0 ? 'good' : 'bad',
            $count > 0 ? "{$count} iç bağlantı var." : 'Sitedeki başka sayfalara bağlantı verin.');
    }

    /** @param  list<array{internal: bool}>  $links */
    private function outboundLinks(array $links, int $wordCount): array
    {
        if ($wordCount < 50) {
            return $this->row('outbound_links', 'Dış bağlantılar', 'na', 'İçerik çok kısa.');
        }

        $count = count(array_filter($links, fn ($l) => ! $l['internal']));

        return $this->row('outbound_links', 'Dış bağlantılar', $count > 0 ? 'good' : 'ok',
            $count > 0 ? "{$count} dış bağlantı var." : 'Güvenilir dış kaynaklara bağlantı vermeyi düşünün.');
    }

    /** @param  list<string>  $paragraphs */
    private function paragraphLength(array $paragraphs, int $max): array
    {
        if ($paragraphs === []) {
            return $this->row('paragraph_length', 'Paragraf uzunluğu', 'na', 'Paragraf bulunamadı.');
        }

        $long = 0;

        foreach ($paragraphs as $p) {
            if (count($this->words($p)) > $max) {
                $long++;
            }
        }

        return $this->row('paragraph_length', 'Paragraf uzunluğu', $long === 0 ? 'good' : 'bad',
            $long === 0 ? 'Paragraflar makul uzunlukta.' : "{$long} paragraf {$max} kelimeden uzun — bölün.");
    }

    /**
     * @param  list<string>  $sentences
     * @param  array<string, mixed>  $rules
     */
    private function sentenceLength(array $sentences, array $rules): array
    {
        if (count($sentences) < 3) {
            return $this->row('sentence_length', 'Cümle uzunluğu', 'na', 'Değerlendirmek için çok az cümle.');
        }

        $limit = (int) $rules['sentence_long_words'];
        $long = count(array_filter($sentences, fn ($s) => count($this->words($s)) > $limit));
        $ratio = round($long / count($sentences) * 100);
        $threshold = (int) $rules['sentence_long_ratio'];

        $status = match (true) {
            $ratio <= $threshold => 'good',
            $ratio <= $threshold * 1.6 => 'ok',
            default => 'bad',
        };

        return $this->row('sentence_length', 'Cümle uzunluğu', $status,
            $status === 'good'
                ? "Uzun cümle oranı %{$ratio} — iyi."
                : "Cümlelerin %{$ratio}'i {$limit} kelimeden uzun (hedef ≤ %{$threshold}).");
    }

    /**
     * @param  list<string>  $headings
     */
    private function subheadingDistribution(string $html, string $text, int $wordCount, array $headings, int $max): array
    {
        if ($wordCount < $max) {
            return $this->row('subheading_distribution', 'Ara başlık dağılımı', 'na', 'İçerik kısa, ara başlık şart değil.');
        }

        if ($headings === []) {
            return $this->row('subheading_distribution', 'Ara başlık dağılımı', 'bad',
                'Uzun içerikte hiç ara başlık yok — H2/H3 ekleyin.');
        }

        // Alt başlıklara göre böl; en uzun bölüm eşiği aşıyor mu?
        $chunks = preg_split('/<h[2-6][^>]*>.*?<\/h[2-6]>/is', $html) ?: [];
        $longest = 0;

        foreach ($chunks as $chunk) {
            $longest = max($longest, count($this->words($this->plainText($chunk))));
        }

        return $this->row('subheading_distribution', 'Ara başlık dağılımı', $longest <= $max ? 'good' : 'ok',
            $longest <= $max
                ? count($headings).' ara başlık, bölümler dengeli.'
                : "Bir bölüm {$longest} kelime — araya başlık ekleyin.");
    }

    /**
     * @param  list<string>  $words
     * @param  list<string>  $sentences
     * @param  array<string, mixed>  $rules
     * @return array{0: int|null, 1: string, 2: array<string, string>}
     */
    private function readability(array $words, array $sentences, array $rules): array
    {
        $wordCount = count($words);
        $sentenceCount = max(count($sentences), 1);

        if ($wordCount < 30) {
            return [null, '—', $this->row('readability', 'Okunabilirlik (Ateşman)', 'na', 'Puan için içerik çok kısa.')];
        }

        $syllables = 0;

        foreach ($words as $word) {
            $syllables += max(preg_match_all('/[aeıioöuüâîû]/iu', $word), 1);
        }

        $raw = 198.825 - 40.175 * ($syllables / $wordCount) - 2.610 * ($wordCount / $sentenceCount);
        $score = (int) round(max(0, min(100, $raw)));

        $label = '—';

        foreach ($rules['readability_bands'] as $band) {
            if ($score >= $band['min']) {
                $label = $band['label'];
                break;
            }
        }

        $status = match (true) {
            $score >= $rules['readability']['good'] => 'good',
            $score >= $rules['readability']['ok'] => 'ok',
            default => 'bad',
        };

        return [$score, $label, $this->row('readability', 'Okunabilirlik (Ateşman)', $status,
            "Ateşman puanı {$score} — {$label}.")];
    }

    /* ------------------------------------------------------------------ *
     | Skorlama
     * ------------------------------------------------------------------ */

    /**
     * @param  list<array{id: string, status: string}>  $checks
     * @param  array<string, int>  $weights
     */
    private function score(array $checks, array $weights): int
    {
        $earned = 0.0;
        $possible = 0.0;

        foreach ($checks as $check) {
            if ($check['status'] === 'na') {
                continue;
            }

            $weight = (float) ($weights[$check['id']] ?? 1);
            $possible += $weight;
            $earned += match ($check['status']) {
                'good' => $weight,
                'ok' => $weight * 0.5,
                default => 0,
            };
        }

        return $possible > 0 ? (int) round($earned / $possible * 100) : 0;
    }

    /** @param  array{bad: int, ok: int}  $grade */
    private function grade(int $score, array $grade): string
    {
        return match (true) {
            $score <= $grade['bad'] => 'bad',
            $score <= $grade['ok'] => 'ok',
            default => 'good',
        };
    }

    /* ------------------------------------------------------------------ *
     | Metin yardımcıları
     * ------------------------------------------------------------------ */

    private function row(string $id, string $label, string $status, string $text): array
    {
        return ['id' => $id, 'label' => $label, 'status' => $status, 'text' => $text];
    }

    private function kw(string $id, string $label, bool $hasKw, bool $pass, string $goodText, string $badText): array
    {
        if (! $hasKw) {
            return $this->row($id, $label, 'na', 'Önce bir odak anahtar kelime belirleyin.');
        }

        return $this->row($id, $label, $pass ? 'good' : 'bad', $pass ? $goodText : $badText);
    }

    private function norm(string $value): string
    {
        return trim(mb_strtolower(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }

    private function plainText(string $html): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<[^>]+>/', ' ', $html) ?? $html;

        return $this->norm(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $this->norm($text), -1, PREG_SPLIT_NO_EMPTY);

        return $parts ?: [];
    }

    /** @return list<string> */
    private function sentences(string $text): array
    {
        $parts = preg_split('/(?<=[.!?…])\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($parts, fn ($s) => count($this->words($s)) >= 2));
    }

    /**
     * @return list<string>
     */
    private function paragraphs(string $html, string $text): array
    {
        if (preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $html, $m)) {
            $paras = array_map(fn ($p) => $this->plainText($p), $m[1]);
        } else {
            $paras = preg_split('/\n{2,}/', $text) ?: [$text];
        }

        return array_values(array_filter(array_map('trim', $paras), fn ($p) => $p !== ''));
    }

    /** @return list<string> */
    private function headings(string $html): array
    {
        preg_match_all('/<h[2-6][^>]*>(.*?)<\/h[2-6]>/is', $html, $m);

        return array_values(array_filter(array_map(fn ($h) => $this->norm(strip_tags($h)), $m[1] ?? []), fn ($h) => $h !== ''));
    }

    /**
     * @return list<array{href: string, internal: bool}>
     */
    private function links(string $html, string $host): array
    {
        preg_match_all('/<a\b[^>]*href=["\']([^"\'#][^"\']*)["\']/i', $html, $m);
        $links = [];

        foreach ($m[1] ?? [] as $href) {
            $internal = ! str_contains($href, '://') || ($host !== '' && str_contains($href, $host));
            $links[] = ['href' => $href, 'internal' => $internal];
        }

        return $links;
    }

    /**
     * @return list<array{alt: string}>
     */
    private function images(string $html): array
    {
        preg_match_all('/<img\b[^>]*>/i', $html, $m);
        $images = [];

        foreach ($m[0] ?? [] as $tag) {
            preg_match('/\balt=["\']([^"\']*)["\']/i', $tag, $alt);
            $images[] = ['alt' => $alt[1] ?? ''];
        }

        return $images;
    }

    /**
     * @param  list<string>  $haystackList
     */
    private function anyContains(array $haystackList, string $needle): bool
    {
        foreach ($haystackList as $item) {
            if (str_contains($this->norm($item), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kelime dizisinde bir ifadenin (çok kelimeli olabilir) kaç kez geçtiği.
     *
     * @param  list<string>  $words
     * @param  list<string>  $phrase
     */
    private function phraseCount(array $words, array $phrase): int
    {
        $n = count($phrase);

        if ($n === 0) {
            return 0;
        }

        if ($n === 1) {
            return count(array_keys($words, $phrase[0], true));
        }

        $count = 0;

        for ($i = 0, $max = count($words) - $n; $i <= $max; $i++) {
            if (array_slice($words, $i, $n) === $phrase) {
                $count++;
            }
        }

        return $count;
    }

    private function slugify(string $value): string
    {
        $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u'];
        $value = strtr(mb_strtolower(trim($value)), $map);

        return trim(preg_replace('/[^a-z0-9]+/', '-', $value) ?? '', '-');
    }
}
