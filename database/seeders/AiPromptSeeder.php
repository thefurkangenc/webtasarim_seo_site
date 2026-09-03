<?php

namespace Database\Seeders;

use App\Models\Ai\AiPrompt;
use Illuminate\Database\Seeder;

/**
 * Blog için çalışır durumda bir başlangıç şablonu. Panelden düzenlenebilir;
 * seeder mevcut kaydı ezmez, yalnızca yoksa oluşturur.
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
    }
}
