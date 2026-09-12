{{-- settings-nav: geniş ekranda sayfayla birlikte kayar. İzleme Kodları gibi
     uzun sekmelerde menü yukarıda kalıp görünmez oluyordu.

     Sekmeler config/settings.php > sections altında gruplanır; bölümü
     tanımlanmamış bir sekme en sona, "Diğer" başlığı altına düşer. --}}
@php
    $sections = config('settings.sections', []);
    // İKİNCİ parametre (preserveKeys) ŞART: groupBy varsayılan olarak alt
    // koleksiyonları yeniden indeksler (0,1,2...) — 'company','seo' gibi
    // dizi anahtarları kaybolur ve aşağıdaki $key sayısal bir indekse döner,
    // route('admin.setting.edit', $key) da /admin/setting/0 gibi yanlış bir
    // adres üretir. Bu proje tam olarak bu hatayı yaşadı.
    $grouped = collect($groups)->groupBy(fn ($item) => $item['section'] ?? 'other', true);
@endphp

<div class="settings-nav trezo-card bg-white dark:bg-[#0c1427] p-[15px] md:p-[20px] rounded-md">
    <div class="trezo-card-content">
        @foreach ($sections + ['other' => ['title' => 'Diğer', 'icon' => 'more_horiz']] as $sectionKey => $section)
            @php($items = $grouped->get($sectionKey))

            @if ($items)
                <div class="mb-[18px] last:mb-0">
                    <span class="flex items-center gap-[6px] text-[10px] font-medium uppercase tracking-[.6px] text-gray-400 mb-[8px] ltr:pl-[4px] rtl:pr-[4px]">
                        <i class="material-symbols-outlined !text-[14px]">{{ $section['icon'] }}</i>
                        {{ $section['title'] }}
                    </span>

                    <ul class="flex flex-col gap-[4px]">
                        @foreach ($items as $key => $item)
                            @php($active = $group === $key)
                            <li>
                                <a href="{{ route('admin.setting.edit', $key) }}"
                                    @if ($active) aria-current="page" @endif
                                    title="{{ $item['description'] ?? '' }}"
                                    class="flex items-center gap-[10px] w-full rounded-md font-medium py-[8px] ltr:pl-[10px] ltr:pr-[12px] rtl:pr-[10px] rtl:pl-[12px] text-sm transition-all {{ $active
                                        ? 'bg-primary-50 text-primary-500 dark:bg-primary-500/10'
                                        : 'text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]' }}">
                                    <span
                                        class="flex items-center justify-center rounded-md w-[30px] h-[30px] shrink-0 {{ $active
                                            ? 'bg-primary-500 text-white'
                                            : 'bg-gray-50 text-gray-500 dark:bg-[#15203c] dark:text-gray-400' }}">
                                        <i class="material-symbols-outlined !text-[18px] leading-none">{{ $item['icon'] }}</i>
                                    </span>
                                    <span class="leading-none">{{ $item['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
</div>
