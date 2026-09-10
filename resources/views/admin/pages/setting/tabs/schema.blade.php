@php
    $schemaDays = [
        'mon' => 'Pazartesi', 'tue' => 'Salı', 'wed' => 'Çarşamba', 'thu' => 'Perşembe',
        'fri' => 'Cuma', 'sat' => 'Cumartesi', 'sun' => 'Pazar',
    ];
    $schemaHours = json_decode((string) ($values['opening_hours'] ?? ''), true);
    $schemaHours = is_array($schemaHours) ? $schemaHours : [];

    $businessTypes = [
        'Organization' => 'Organization — genel kurum (adres/saat yayınlanmaz)',
        'ProfessionalService' => 'ProfessionalService — profesyonel hizmet işletmesi (önerilen)',
        'LocalBusiness' => 'LocalBusiness — yerel işletme',
        'Corporation' => 'Corporation — şirket / A.Ş.',
    ];

    $priceRanges = ['₺' => '₺', '₺₺' => '₺₺', '₺₺₺' => '₺₺₺', '₺₺₺₺' => '₺₺₺₺'];

    $inputClass = 'h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all focus:border-primary-500';
@endphp

<form id="setting-form" action="{{ route('admin.setting.schema.update') }}" method="POST">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] md:mb-[25px] leading-relaxed">
        Bu ayarlar ön yüzdeki her sayfaya otomatik eklenen <strong>JSON-LD (Schema.org)</strong>
        işaretlemesini besler. Adres, telefon, e-posta, logo ve koordinat
        <a href="{{ route('admin.setting.edit', 'company') }}" class="text-primary-500 hover:underline">Firma Bilgileri</a>'nden;
        sosyal profiller <a href="{{ route('admin.setting.edit', 'social') }}" class="text-primary-500 hover:underline">Sosyal Medya</a>
        kayıtlarından otomatik gelir — burada tekrar girmeyin. Üretilen çıktıyı görmek ve
        doğrulamak için <a href="{{ route('admin.schema.index') }}" class="text-primary-500 hover:underline">Schema.org doğrulama ekranı</a>.
    </p>

    <x-admin::form.select name="business_type" label="İşletme türü" required
        :options="$businessTypes" :value="$values['business_type'] ?? 'ProfessionalService'" :placeholder="null" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
        <x-admin::form.input name="founding_year" label="Kuruluş yılı" type="number"
            :value="$values['founding_year'] ?? null" placeholder="Örn. 2015" wrapper="mb-[20px] md:mb-[25px]" />

        <x-admin::form.select name="price_range" label="Fiyat aralığı"
            :options="$priceRanges" :value="$values['price_range'] ?? '₺₺'"
            placeholder="Belirtilmesin" wrapper="mb-[20px] md:mb-[25px]" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
        <x-admin::form.input name="tax_id" label="Vergi / MERSİS no"
            :value="$values['tax_id'] ?? null" placeholder="Örn. 1234567890" wrapper="mb-[20px] md:mb-[25px]" />

        <x-admin::form.input name="tax_office" label="Vergi dairesi"
            :value="$values['tax_office'] ?? null" placeholder="Örn. Şahinbey" wrapper="mb-[20px] md:mb-[25px]" />
    </div>

    <x-admin::form.textarea name="area_served" label="Hizmet verilen bölge"
        :value="$values['area_served'] ?? null"
        placeholder="Tek satır (örn. Türkiye) ya da her satıra bir il"
        class="h-[80px]" />

    <x-admin::form.textarea name="description" label="Kısa tanım (schema description)"
        :value="$values['description'] ?? null"
        placeholder="Boş bırakılırsa Firma Bilgileri'ndeki kısa açıklama kullanılır"
        class="h-[80px]" />

    <x-admin::form.textarea name="same_as" label="Ek profil adresleri (sameAs)"
        :value="$values['same_as'] ?? null"
        placeholder="Her satıra bir URL. Sosyal Medya kayıtları zaten otomatik ekleniyor; buraya yalnızca ek olanları yazın."
        class="h-[80px]" />

    <x-admin::form.input name="search_url" label="Site içi arama adresi (opsiyonel)"
        :value="$values['search_url'] ?? null"
        placeholder="Örn. https://site.com/ara?q={query}" />
    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-[14px] mb-[20px] md:mb-[25px]">
        Doldurulursa <code>WebSite</code> düğümüne <code>SearchAction</code> eklenir. Aranan kelimenin
        geçtiği yeri <code>{query}</code> ile işaretleyin. Sitede arama yoksa boş bırakın.
    </p>

    <div class="mb-[10px] text-black dark:text-white font-medium">Çalışma saatleri</div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-[15px]">
        Yalnızca <strong>ProfessionalService / LocalBusiness</strong> türlerinde yayınlanır.
    </p>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] divide-y divide-gray-100 dark:divide-[#172036] mb-[20px] md:mb-[25px]"
        data-schema-hours>
        @foreach ($schemaDays as $key => $label)
            @php($row = $schemaHours[$key] ?? [])
            <div class="flex flex-wrap items-center gap-x-[16px] gap-y-[10px] p-[12px]" data-hour-row>
                <span class="w-[90px] text-sm font-medium text-black dark:text-white">{{ $label }}</span>

                <label class="flex items-center gap-[8px] cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                    <input type="hidden" name="hours[{{ $key }}][closed]" value="0">
                    <input type="checkbox" name="hours[{{ $key }}][closed]" value="1" data-hour-closed
                        @checked(! empty($row['closed']))
                        class="w-[16px] h-[16px] rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                    Kapalı
                </label>

                <div class="flex items-center gap-[8px]" data-hour-times>
                    <input type="time" name="hours[{{ $key }}][opens]" value="{{ $row['opens'] ?? '09:00' }}"
                        class="h-[38px] w-[120px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[10px] outline-0 focus:border-primary-500">
                    <span class="text-gray-400">–</span>
                    <input type="time" name="hours[{{ $key }}][closes]" value="{{ $row['closes'] ?? '18:00' }}"
                        class="h-[38px] w-[120px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[10px] outline-0 focus:border-primary-500">
                </div>
            </div>
        @endforeach
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
