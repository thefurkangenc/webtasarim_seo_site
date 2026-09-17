<?php

namespace App\Services\AutoBlog;

use App\Models\Ai\AiProvider;
use App\Models\Blog\Blog;
use App\Models\Service\Service;
use App\Models\User;
use App\Services\Media\MediaService;
use App\Support\Settings;
use App\Support\Slug;
use DomainException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Cron'dan tetiklenen blog üretimi: yazı ve kapak görseli tek yerde.
 *
 * Bilinçli olarak AiService/BlogService'ten bağımsızdır — o hat paneldeki
 * elle üretim içindir (prompt kayıtları, kuyruk, durum sorgulama). Burada
 * tek bir düz akış vardır ve her iki prompt da bu sınıfın içindedir.
 *
 * Tek istisna MediaService'tir: görsel medya kütüphanesine kaydedilmeli,
 * WebP/kırpma/türev üretimi orada tek yerde durur, kopyalanmaz.
 *
 * Adımlar: sağlayıcı bul -> konu seç -> yazıyı üret -> kapağı üret ->
 * taslak kaydı aç.
 */
class AutoBlogService
{
    public function __construct(private readonly MediaService $media) {}

    public function generate(array $input = []): Blog
    {
        $provider = $this->provider();
        $topic = $this->topic($input);
        $article = $this->write($provider, $topic);

        // Aynı başlık iki kez üretilirse ikinci kayıt açılmaz: konu havuzu
        // rastgele seçtiği için tekrar mümkündür, kopya içerik SEO'ya zarar verir.
        if (Blog::where('title', $article['title'])->exists()) {
            throw new DomainException("Bu başlıkla bir yazı zaten var: {$article['title']}");
        }

        return $this->store($article, $topic, $this->cover($provider, $article));
    }

    /*
    |--------------------------------------------------------------------------
    | Sağlayıcı ve konu
    |--------------------------------------------------------------------------
    */

    /** Panelde tanımlı ChatGPT kaydı — yalnızca adres ve anahtar için. */
    private function provider(): AiProvider
    {
        /** @var AiProvider|null $provider */
        $provider = AiProvider::query()
            ->where('driver', 'openai')
            ->where('is_active', true)
            ->when(config('auto-blog.provider_id'), fn ($query, $id) => $query->whereKey($id))
            ->orderByDesc('is_default')
            ->first();

        if (! $provider || blank($provider->api_key)) {
            throw new DomainException(
                'Aktif bir ChatGPT sağlayıcısı bulunamadı. Yapay Zeka > Sağlayıcılar ekranından anahtarı girin.',
            );
        }

        return $provider;
    }

    /**
     * Konu normalde modelin kendi seçimidir; üçü de boş dönebilir. Adrese
     * ?keywords=... verilirse o brief kazanır (elle deneme ve tek seferlik
     * istekler için).
     *
     * @return array{keywords: string, title: string, notes: string}
     */
    private function topic(array $input): array
    {
        return [
            'keywords' => trim((string) ($input['keywords'] ?? '')),
            'title' => trim((string) ($input['title'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Yazı
    |--------------------------------------------------------------------------
    */

    /** @return array<string, mixed> */
    private function write(AiProvider $provider, array $topic): array
    {
        $config = config('auto-blog.text');

        $response = $this->call($provider, 'chat/completions', $config['timeout'], [
            'model' => $config['model'],
            'temperature' => $config['temperature'],
            'max_completion_tokens' => $config['max_tokens'],
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->userPrompt($topic)],
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            throw new DomainException('Model boş yanıt döndürdü.');
        }

        $article = $this->decode($content);

        if (blank($article['title'] ?? null) || blank($article['content'] ?? null)) {
            throw new DomainException('Model başlık ya da içerik döndürmedi.');
        }

        return $article;
    }

    private function systemPrompt(): string
    {
        $company = Settings::group('company')['name'] ?: config('app.name');
        $words = config('auto-blog.text.words');
        $links = $this->links();

        $linkList = $links === []
            ? '(Şu an bağlanabilecek bir hizmet sayfası yok; hiç iç bağlantı verme.)'
            : collect($links)->map(fn (array $link) => "- {$link['anchor']} => {$link['path']}")->implode("\n");

        return <<<PROMPT
        Sen {$company} adlı web tasarım ve dijital pazarlama ajansının içerik
        editörüsün. Türkçe, akıcı ve satış odaklı ama abartısız yazarsın.
        Okuyucu küçük ya da orta ölçekli bir işletmenin sahibidir; teknik
        terimi açıklamadan kullanma.

        Yanıtını YALNIZCA geçerli bir JSON nesnesi olarak ver. JSON dışında
        açıklama, selamlama ya da kod çiti yazma.

        Şema:
        {
          "title": "yazının başlığı",
          "excerpt": "tek paragraf özet",
          "content": "HTML gövde",
          "tags": ["etiket", "etiket"],
          "focus_keyword": "tek odak ifadesi",
          "meta_title": "arama sonucu başlığı",
          "meta_description": "arama sonucu açıklaması",
          "meta_keywords": "virgülle ayrılmış ifadeler",
          "image_prompt": "İngilizce fotoğraf sahnesi tarifi"
        }

        Konu seçimi:
        - Konuyu sen seçeceksin. Kullanıcı mesajında sitedeki mevcut yazıların
          başlıkları listelenir; o listedeki bir konuyu, eş anlamlısını ya da
          aynı sorunun başka türlü sorulmuş halini TEKRAR YAZMA.
        - Konu, yukarıdaki hizmetlerden en az biriyle ilişkili ve bir
          işletme sahibinin gerçekten aradığı bir soru olsun: karar kriteri,
          süreç, maliyet mantığı, karşılaştırma, hazırlık kontrol listesi.
        - Şu tür konuları seçme: yıl içeren "trendler" yazıları, genel
          "... nedir" tanım yazıları, her sitede bulunan ansiklopedik
          anlatımlar, yapay zeka/teknoloji üzerine genel yorumlar.
        - Konu ne kadar daralırsa o kadar iyi. "SEO nedir" değil, "hizmet
          sayfası yazarken hangi başlıklar zorunlu" gibi.

        İçerik kuralları:
        - content HTML olacak: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>,
          <em>, <blockquote>. <h1>, <script>, <style> ve satır içi style kullanma.
        - Bir <p> giriş paragrafıyla başla, en az üç <h2> bölümü olsun,
          gövde {$words} kelime civarında olsun.
        - excerpt en fazla 200 karakter, düz metin.
        - meta_title en fazla 60 karakter, meta_description 150-160 karakter.
        - focus_keyword tek bir ifade; başlıkta, giriş paragrafında ve meta
          açıklamada doğal biçimde geçsin.
        - meta_keywords 5-8 ifade, tags 3-6 kısa etiket.

        Yasaklar:
        - Uydurma istatistik, yüzde, tarih, fiyat, müşteri adı ya da vaka yazma.
        - "Dijital dünyada", "günümüzün hızla değişen", "başarıya giden yol"
          gibi klişe açılışlar kullanma; doğrudan konuya gir.
        - Metnin yapay zeka tarafından üretildiğini ima etme.
        - Rakip firma adı verme.

        İç bağlantı:
        - Yalnızca aşağıdaki adresleri kullanabilirsin, yeni adres uydurma.
        - Konuya uyuyorsa metin içinde en fazla ikisini <a href="...">...</a>
          ile bağla; uymuyorsa hiç bağlama.
        {$linkList}

        Kapak görseli (image_prompt):
        - İngilizce yaz, 1-2 cümle, somut bir sahne anlat: mekân, nesne, ışık.
        - Soyut kavram ("success", "growth") değil, fotoğraflanabilir bir an olsun.
        - Sahnede yazı, harf, rakam, logo ya da okunabilir arayüz metni OLMAYACAK.
        PROMPT;
    }

    private function userPrompt(array $topic): string
    {
        $history = $this->history();

        $brief = $topic['keywords'] === ''
            ? <<<'BRIEF'
            Konuyu sen belirle. Aşağıdaki listede olmayan, hizmetlerden biriyle
            ilişkili ve arama niyeti olan tek bir konu seç; sonra o konuda yazıyı
            üret. Seçtiğin konuyu ayrıca açıklama, doğrudan JSON'u döndür.
            BRIEF
            : <<<BRIEF
            Bu yazının konusu: {$topic['keywords']}
            Bu anahtar kelimeleri metin içinde doğal biçimde geçir.
            BRIEF;

        if ($topic['title'] !== '') {
            $brief .= "\nBaşlık verildi, aynen kullan: {$topic['title']}";
        }

        if ($topic['notes'] !== '') {
            $brief .= "\nEk notlar: {$topic['notes']}";
        }

        return <<<PROMPT
        {$brief}

        Sitede hâlihazırda bulunan yazılar (taslaklar dahil). Bu konuları ve
        bunların farklı kelimelerle yazılmış hallerini tekrar etme:
        {$history}
        PROMPT;
    }

    /*
    |--------------------------------------------------------------------------
    | Kapak görseli
    |--------------------------------------------------------------------------
    */

    /** Görseli üretir, medya kütüphanesine kaydeder, media id döner. */
    private function cover(AiProvider $provider, array $article): ?int
    {
        $config = config('auto-blog.image');

        if (! ($config['enabled'] ?? false)) {
            return null;
        }

        $response = $this->call($provider, 'images/generations', $config['timeout'], [
            'model' => $config['model'],
            'prompt' => $this->imagePrompt((string) ($article['image_prompt'] ?? '')),
            'size' => $config['size'],
            'quality' => $config['quality'],
            'n' => 1,
        ]);

        // gpt-image ailesi base64 döner; eski dall-e uçları url döndürüyordu.
        $encoded = $response['data'][0]['b64_json'] ?? null;
        $binary = is_string($encoded) ? base64_decode($encoded, true) : false;

        if ($binary === false || $binary === '') {
            throw new DomainException('Görsel üretildi ama okunamadı.');
        }

        $path = sys_get_temp_dir().'/'.Str::uuid()->toString().'.png';
        file_put_contents($path, $binary);

        try {
            $media = $this->media->store(
                new UploadedFile($path, Str::slug($article['title']).'.png', 'image/png', null, true),
                [
                    'preset' => 'blog.cover',
                    'accept' => 'image',
                    'name' => $article['title'],
                    'alt' => $article['title'],
                ],
            );
        } finally {
            // storeAs geçici dosyayı taşır; hata durumunda arkada kalmasın.
            if (is_file($path)) {
                unlink($path);
            }
        }

        return $media->id;
    }

    /**
     * Modelin verdiği sahneyi sabit bir fotoğraf diliyle sarar. Stil tarifi
     * burada durur ki her kapak hizmet görselleriyle aynı aileden çıksın;
     * modelin kendi başına "AI illüstrasyonu" üretmesi engellenir.
     */
    private function imagePrompt(string $scene): string
    {
        $scene = trim($scene) !== ''
            ? trim($scene)
            : 'A tidy modern office desk with an open laptop showing a clean, '
                .'unbranded website layout, a closed notebook and a cup of coffee, soft window light.';

        return <<<PROMPT
        Professional editorial stock photograph for the blog of a corporate web design agency.

        Scene: {$scene}

        Style: photorealistic DSLR photograph, natural daylight, shallow depth of field,
        muted corporate colour palette with a deep navy (#05051C) accent, calm and clean
        composition, real materials and a real workspace, subject slightly off-centre with
        open space on one side, landscape framing.

        Must not contain: any text, letters, numbers, logos, watermarks, readable user
        interface labels, labelled charts, illustration, 3D render, CGI, neon or cyberpunk
        lighting, holograms, collage, split screen, distorted hands or faces.
        PROMPT;
    }

    /*
    |--------------------------------------------------------------------------
    | Kayıt
    |--------------------------------------------------------------------------
    */

    private function store(array $article, array $topic, ?int $coverId): Blog
    {
        $defaults = config('auto-blog.defaults');
        $status = $defaults['status'] === Blog::STATUS_PUBLISHED
            ? Blog::STATUS_PUBLISHED
            : Blog::STATUS_DRAFT;

        return DB::transaction(function () use ($article, $topic, $coverId, $defaults, $status) {
            $blog = Blog::create([
                'blog_category_id' => $defaults['blog_category_id'] ?: null,
                'user_id' => $defaults['author_id'] ?: User::orderBy('id')->value('id'),
                'title' => $article['title'],
                'slug' => Slug::unique($article['title'], 'blogs'),
                'excerpt' => Str::limit(strip_tags((string) ($article['excerpt'] ?? '')), 480, ''),
                'content' => $article['content'],
                'status' => $status,
                'published_at' => $status === Blog::STATUS_PUBLISHED ? now() : null,
            ]);

            $blog->syncMedia($coverId, 'cover');
            $blog->syncTags(Arr::wrap($article['tags'] ?? []));
            $blog->syncSeo([
                'meta_title' => $article['meta_title'] ?? null,
                'meta_description' => $article['meta_description'] ?? null,
                'meta_keywords' => $article['meta_keywords'] ?? null,
                'focus_keyword' => ($article['focus_keyword'] ?? null) ?: $topic['keywords'],
                'og_media_id' => $coverId,
            ]);

            return $blog;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Yardımcılar
    |--------------------------------------------------------------------------
    */

    /** OpenAI'ye tek giriş noktası — metin ve görsel aynı anahtarı kullanır. */
    private function call(AiProvider $provider, string $endpoint, int $timeout, array $body): array
    {
        try {
            $response = Http::timeout($timeout)
                ->withToken((string) $provider->api_key)
                ->acceptJson()
                ->post(rtrim($provider->base_url, '/').'/'.$endpoint, $body);
        } catch (ConnectionException $exception) {
            throw new DomainException("OpenAI adresine ulaşılamadı: {$exception->getMessage()}");
        }

        if ($response->failed()) {
            throw new DomainException(
                $response->json('error.message') ?? "OpenAI isteği reddetti (HTTP {$response->status()}).",
            );
        }

        return (array) $response->json();
    }

    /**
     * Model JSON'u kod çitiyle sarabiliyor; ilk { ile son } arası okunur.
     *
     * @return array<string, mixed>
     */
    private function decode(string $raw): array
    {
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        $decoded = $start === false || $end === false
            ? null
            : json_decode(substr($raw, $start, $end - $start + 1), true);

        if (! is_array($decoded)) {
            throw new DomainException('Model geçerli JSON döndürmedi.');
        }

        return $decoded;
    }

    /**
     * Modele verilecek iç bağlantı listesi. Hizmet başlıkları bölge yer
     * tutucusu taşıyabildiği için ({{city}} gibi) temizlenir.
     *
     * @return array<int, array{anchor: string, path: string}>
     */
    private function links(): array
    {
        return Service::where('status', Service::STATUS_PUBLISHED)
            ->orderBy('sort_order')
            ->limit(12)
            ->get(['id', 'title', 'slug'])
            ->map(fn (Service $service) => [
                'anchor' => trim((string) preg_replace('/\{\{\s*\w+\s*\}\}/', '', $service->title)),
                'path' => '/hizmetler/'.$service->slug,
            ])
            ->filter(fn (array $link) => $link['anchor'] !== '')
            ->values()
            ->all();
    }

    /**
     * Mevcut arşiv. Konuyu model seçtiği için tekrarı engelleyen tek şey bu
     * listedir; taslaklar da dahildir, yoksa aynı konu üst üste üretilir.
     * Odak kelime varsa eklenir — hangi kelime alanının tutulduğunu başlıktan
     * daha net söyler.
     */
    private function history(): string
    {
        $blogs = Blog::with('seo')
            ->orderByDesc('id')
            ->limit((int) config('auto-blog.text.history', 200))
            ->get(['id', 'title']);

        if ($blogs->isEmpty()) {
            return '(henüz yazı yok, arşivi sen açıyorsun)';
        }

        return $blogs
            ->map(function (Blog $blog) {
                $keyword = $blog->seo?->focus_keyword;

                return $keyword ? "- {$blog->title} (odak: {$keyword})" : "- {$blog->title}";
            })
            ->implode("\n");
    }
}
