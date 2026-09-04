@php
    $enabled = filter_var($values['enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN);
    $autoReply = filter_var($values['auto_reply_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN);
    $privacy = filter_var($values['privacy_required'] ?? '1', FILTER_VALIDATE_BOOLEAN);
@endphp

<form id="setting-form" action="{{ route('admin.setting.contact.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">toggle_on</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Durum</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Kapalıyken iletişim sayfasında form gizlenir; telefon ve e-posta kutuları durur.</p>
            </div>
        </div>

        <x-admin::form.switch name="enabled" label="İletişim formunu göster"
            :checked="$enabled"
            wrapper="mb-0" />
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">forward_to_inbox</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Teslimat</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Mesajlar önce kaydedilir, sonra bu adrese postalanır. Konuda {name}, {email}, {phone} kullanabilirsiniz.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
            <x-admin::form.input name="to_email" type="email" label="Alıcı e-posta"
                :value="$values['to_email'] ?? null"
                placeholder="Boşsa firma e-postası kullanılır"
                wrapper="" />

            <x-admin::form.input name="cc_email" type="email" label="Bilgi kopyası"
                :value="$values['cc_email'] ?? null"
                placeholder="İsteğe bağlı"
                wrapper="" />
        </div>

        <x-admin::form.input name="subject" label="E-posta konusu" required
            :value="$values['subject'] ?? null"
            wrapper="mb-0 mt-[20px] md:mt-[25px]" />
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">chat</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Ziyaretçiye gösterilenler</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Formun üstündeki başlık ile gönderim sonrası mesajlar.</p>
            </div>
        </div>

        <x-admin::form.input name="heading" label="Form başlığı"
            :value="$values['heading'] ?? null" />

        <x-admin::form.textarea name="intro" label="Form açıklaması"
            :value="$values['intro'] ?? null"
            rows="3" />

        <x-admin::form.textarea name="success_message" label="Başarı mesajı" required
            :value="$values['success_message'] ?? null"
            rows="2" />

        <x-admin::form.textarea name="error_message" label="Hata mesajı" required
            :value="$values['error_message'] ?? null"
            rows="2"
            wrapper="mb-0" />
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">reply</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Otomatik yanıt</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Ziyaretçiye giden teşekkür e-postası. Metinde {name}, {email}, {phone}, {message} kullanılabilir.</p>
            </div>
        </div>

        <x-admin::form.switch name="auto_reply_enabled" label="Otomatik yanıt gönder"
            :checked="$autoReply"
            data-contact-auto-reply />

        <div data-auto-reply-fields class="{{ $autoReply ? '' : 'hidden' }}">
            <x-admin::form.input name="auto_reply_subject" label="Yanıt konusu"
                :value="$values['auto_reply_subject'] ?? null" />

            <x-admin::form.textarea name="auto_reply_body" label="Yanıt metni"
                :value="$values['auto_reply_body'] ?? null"
                rows="6"
                wrapper="mb-0" />
        </div>
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">policy</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">KVKK onayı</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">{kvkk} yerine aydınlatma metni bağlantısı konur. Metin İçerikler sekmesinden yönetilir.</p>
            </div>
        </div>

        <x-admin::form.switch name="privacy_required" label="Formda onay kutusu iste"
            :checked="$privacy"
            data-contact-privacy />

        <div data-privacy-fields class="{{ $privacy ? '' : 'hidden' }}">
            <x-admin::form.textarea name="privacy_text" label="Onay metni"
                :value="$values['privacy_text'] ?? null"
                rows="3"
                wrapper="mb-0" />
        </div>
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
