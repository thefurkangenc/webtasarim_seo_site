{{-- AJAX modal gövdesi. Gönderim pages/ai-prompt/index.js tarafından devralınır. --}}
<form id="prompt-form" data-id="{{ $prompt?->id }}">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
        <x-admin::form.input name="name" label="Şablon Adı" required :value="$prompt?->name"
            placeholder="Örn. Detaylı rehber yazısı" wrapper="mb-[20px] md:mb-[25px]" />

        <x-admin::form.input name="key" label="Modül Anahtarı" required :value="$prompt?->key ?? 'blog.content'"
            placeholder="blog.content" wrapper="mb-[20px] md:mb-[25px]" />
    </div>

    <x-admin::form.select name="ai_provider_id" label="Sağlayıcı"
        :value="$prompt?->ai_provider_id" :options="$providers->all()"
        placeholder="Varsayılan sağlayıcıyı kullan" />

    <x-admin::form.textarea name="system_prompt" label="Sistem Prompt" required rows="6"
        :value="$prompt?->system_prompt"
        placeholder="Modelin rolünü ve çıktı biçimini anlatın." class="h-[160px]" />

    <x-admin::form.textarea name="user_prompt" label="Kullanıcı Prompt" required rows="8"
        :value="$prompt?->user_prompt"
        placeholder="Üretim isteğini yazın; {{ '{{keywords}}' }} gibi değişkenler kullanabilirsiniz." class="h-[200px]" />

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[15px] mb-[20px] md:mb-[25px]">
        <p class="!mb-[8px] font-medium text-black dark:text-white text-sm">Kullanılabilir değişkenler</p>
        <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400 leading-[1.8]">
            <code>{{ '{{keywords}}' }}</code> anahtar kelimeler ·
            <code>{{ '{{title}}' }}</code> başlık (boş olabilir) ·
            <code>{{ '{{category}}' }}</code> kategori ·
            <code>{{ '{{length}}' }}</code> uzunluk ·
            <code>{{ '{{notes}}' }}</code> ek notlar
            <br>
            Doldurulmayan değişkenler prompt'tan silinir. Blog için beklenen JSON anahtarları:
            <code>title</code>, <code>excerpt</code>, <code>content</code>, <code>tags</code>,
            <code>meta_description</code>, <code>meta_keywords</code>.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px] mb-[20px] md:mb-[25px]">
        <x-admin::form.switch name="is_active" label="Aktif" :checked="$prompt?->is_active ?? true" wrapper="" />
        <x-admin::form.switch name="is_default" label="Varsayılan"
            :checked="$prompt?->is_default ?? false"
            hint="Bu anahtar için üretim modalında önce bu seçili gelir." wrapper="" />
    </div>

    <x-admin::form.actions :submit="$prompt ? 'Güncelle' : 'Ekle'" />
</form>
