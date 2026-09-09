<?php

namespace Database\Seeders;

use App\Models\Ai\AiPrompt;
use Illuminate\Database\Seeder;

/**
 * Blog ve Hizmet için çalışır durumda başlangıç şablonları. Panelden
 * düzenlenebilir; seeder mevcut kaydı ezmez, yalnızca yoksa oluşturur.
 */
class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        $system = <<<'PROMPT'
        Sen kurumsal bir web tasarım ajansının blog editörüsün. Türkçe, akıcı ve
        SEO uyumlu içerik üretirsin.

        Yanıtını YALNIZCA geçerli bir JSON nesnesi olarak ver. JSON dışında
        açıklama, selamlama ya da kod çiti yazma.

        Şema:
        {
          "title": "yazının başlığı",
          "excerpt": "tek paragraf özet",
          "content": "HTML gövde",
          "tags": ["etiket", "etiket"],
          "meta_description": "arama sonucu açıklaması",
          "meta_keywords": "virgülle ayrılmış ifadeler"
        }

        Kurallar:
        - content HTML olacak: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>,
          <blockquote> ve gerekiyorsa <table> kullan. <h1>, <script>, <style> ve
          satır içi style kullanma.
        - Yazı bir <p> giriş paragrafıyla başlasın, en az üç <h2> bölümü olsun.
        - excerpt en fazla 200 karakter, düz metin.
        - meta_description 150-160 karakter arası.
        - meta_keywords 5-8 ifade.
        - tags 3-6 kısa etiket.
        - Uydurma istatistik, tarih, fiyat ya da alıntı kullanma.
        PROMPT;

        $user = <<<'PROMPT'
        Anahtar kelimeler: {{keywords}}
        Başlık: {{title}}
        Kategori: {{category}}
        İstenen uzunluk: {{length}}
        Ek notlar: {{notes}}

        Yukarıdaki anahtar kelimeleri metin içinde doğal biçimde geçen bir blog
        yazısı üret. Başlık verilmişse aynen onu kullan; verilmemişse anahtar
        kelimelerle uyumlu, dikkat çekici bir başlık üret.
        PROMPT;

        AiPrompt::firstOrCreate(
            ['key' => 'blog.content', 'name' => 'Standart blog yazısı'],
            [
                'system_prompt' => $system,
                'user_prompt' => $user,
                'is_active' => true,
                'is_default' => true,
            ],
        );

        $this->servicePrompt();
    }

    /**
     * Hizmet içeriği tek kez yazılır, her hizmet bölgesi için yeniden üretilir.
     * Bu yüzden model gerçek bir şehir adı yazmamalı, yer tutucu basmalıdır.
     *
     * Yer tutucular şablona LİTERAL yazılamaz: AiService, doldurmadığı her
     * {{...}} kalıbını gönderim öncesi siler. Bu yüzden biçim sözle anlatılıyor.
     */
    private function servicePrompt(): void
    {
        $system = <<<'PROMPT'
        Sen kurumsal bir web tasarım ajansının içerik editörüsün. Türkçe, akıcı
        ve SEO uyumlu hizmet sayfası metinleri üretirsin.

        Yanıtını YALNIZCA geçerli bir JSON nesnesi olarak ver. JSON dışında
        açıklama, selamlama ya da kod çiti yazma.

        Şema:
        {
          "title": "hizmetin başlığı",
          "excerpt": "tek paragraf açıklama",
          "content": "HTML gövde",
          "tags": ["etiket", "etiket"],
          "meta_description": "arama sonucu açıklaması",
          "meta_keywords": "virgülle ayrılmış ifadeler"
        }

        BÖLGE YER TUTUCULARI — en önemli kural:
        Bu metin tek bir şehir için değil, yüzlerce bölge için kullanılacak.
        Bu yüzden hiçbir yerde gerçek bir il, ilçe ya da mahalle adı yazma.
        Bunun yerine yer tutucu bas. Yer tutucu şu biçimde yazılır: iki adet
        açılış süslü parantez, hemen ardından anahtar, hemen ardından iki adet
        kapanış süslü parantez — aralarında boşluk bırakma.
        Kullanılabilir anahtarlar:
        - region  : bölgenin tam yolu (il, ya da "il - ilçe")
        - city    : yalnızca il adı
        - district: yalnızca ilçe adı (bölge il ise boş kalır, cümle onsuz da
                    anlamlı olmalı)
        En sık kullanacağın anahtar city'dir; başlıkta ve giriş paragrafında
        mutlaka geçsin. district'i yalnızca cümle onsuz da doğru okunuyorsa kullan.

        Diğer kurallar:
        - content HTML olacak: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>
          kullan. <h1>, <script>, <style> ve satır içi style kullanma.
        - Metin bir <p> giriş paragrafıyla başlasın, en az üç <h2> bölümü olsun.
        - excerpt en fazla 200 karakter, düz metin.
        - meta_description 150-160 karakter arası.
        - meta_keywords 5-8 ifade.
        - tags 3-6 kısa etiket. Etiketlerde yer tutucu kullanma.
        - Uydurma istatistik, tarih, fiyat, referans ya da alıntı kullanma.
        PROMPT;

        $user = <<<'PROMPT'
        Anahtar kelimeler: {{keywords}}
        Hizmet adı: {{title}}
        Hizmet türü: {{category}}
        İstenen uzunluk: {{length}}
        Ek notlar: {{notes}}

        Yukarıdaki anahtar kelimeleri doğal biçimde geçiren, bölgeye göre
        kişiselleşen bir hizmet sayfası metni üret. Hizmet adı verilmişse aynen
        onu kullan; verilmemişse anahtar kelimelerle uyumlu bir ad üret.
        Şehir adı geçmesi gereken her yerde gerçek ad yerine yer tutucu bas.
        PROMPT;

        AiPrompt::firstOrCreate(
            ['key' => 'service.content', 'name' => 'Bölgeye göre hizmet metni'],
            [
                'system_prompt' => $system,
                'user_prompt' => $user,
                'is_active' => true,
                'is_default' => true,
            ],
        );
    }
}
