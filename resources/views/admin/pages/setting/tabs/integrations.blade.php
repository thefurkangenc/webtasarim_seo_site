@php
    $canUpdate = auth()->user()->can('integration.update');
    $activeCount = collect($integrations)->where('enabled', true)->where('ready', true)->count();
@endphp

{{-- Ne olduğu ve Search Console gibi "okuyan" sayfalarla karışmaması için kısa bir giriş. --}}
<div class="flex items-start gap-[12px] p-[16px] rounded-md bg-primary-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px]">
    <span class="shrink-0 w-[36px] h-[36px] rounded-full bg-primary-500 text-white flex items-center justify-center">
        <i class="material-symbols-outlined !text-[19px]">extension</i>
    </span>
    <div class="text-xs text-gray-600 dark:text-gray-300 leading-[1.75]">
        <strong class="block text-sm text-black dark:text-white mb-[4px]">
            Ziyaretçinin sitede gördüğü eklentiler
        </strong>
        Buradaki her kart, sitenize eklenen küçük bir araçtır: sohbet balonu, arama butonu, harita.
        Bir eklentiyi çalıştırmak için <strong>iki şey</strong> gerekir — anahtarı açmak
        <em>ve</em> gerekli bilgiyi (telefon, hesap kodu…) girmek. Biri eksikse sitede görünmez.
        <span class="block mt-[6px] text-gray-500 dark:text-gray-400">
            Şu an <strong class="text-black dark:text-white">{{ $activeCount }}</strong> eklenti sitede yayında.
        </span>
    </div>
</div>

<div id="integration-grid" data-can-update="{{ $canUpdate ? '1' : '0' }}"
    class="grid grid-cols-1 sm:grid-cols-2 gap-[15px] md:gap-[20px]">
    @foreach ($integrations as $item)
        @php
            // Üç durum var: yayında / açık ama bilgi eksik / kapalı.
            $live = $item['enabled'] && $item['ready'];
            $incomplete = $item['enabled'] && ! $item['ready'];
        @endphp

        <div data-integration="{{ $item['key'] }}" data-title="{{ $item['title'] }}"
            class="trezo-card bg-white dark:bg-[#0c1427] p-[18px] rounded-md border transition-all h-full flex flex-col
                {{ $live ? 'border-success-200 dark:border-[#172036]' : ($incomplete ? 'border-warning-300 dark:border-[#172036]' : 'border-gray-100 dark:border-[#172036]') }}">

            <div class="flex items-start gap-[12px] mb-[12px]">
                <span class="w-[46px] h-[46px] rounded-[12px] shrink-0 flex items-center justify-center bg-gray-50 dark:bg-[#15203c]">
                    <img src="{{ asset('admin/assets/images/icons/integrations/'.$item['icon']) }}" alt=""
                        class="w-[24px] h-[24px] object-contain">
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-[8px] flex-wrap">
                        <span class="font-semibold text-black dark:text-white">{{ $item['title'] }}</span>
                        <span data-integration-badge
                            class="px-[8px] py-[2px] inline-block rounded-sm font-medium text-[10px]
                                {{ $live ? 'bg-success-100 dark:bg-[#15203c] text-success-600'
                                   : ($incomplete ? 'bg-warning-100 dark:bg-[#15203c] text-warning-600'
                                   : 'bg-gray-100 dark:bg-[#15203c] text-gray-500 dark:text-gray-400') }}">
                            {{ $live ? 'Yayında' : ($incomplete ? 'Bilgi eksik' : 'Kapalı') }}
                        </span>
                    </div>
                    <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400 mt-[4px] leading-[1.6]">{{ $item['description'] }}</p>
                </div>

                @if ($canUpdate)
                    <x-admin::form.switch bare :checked="$item['enabled']" wrapper="shrink-0 mt-[2px]"
                        data-integration-toggle />
                @endif
            </div>

            @if ($item['placement'])
                <span class="flex items-center gap-[6px] text-[11px] text-gray-400 mb-[12px]">
                    <i class="material-symbols-outlined !text-[14px]">my_location</i>
                    {{ $item['placement'] }}
                </span>
            @endif

            {{-- Açık ama bilgisi eksikse ziyaretçi bunu göremez; sessiz kalmak yerine söyleniyor. --}}
            <div data-integration-warning
                class="{{ $incomplete ? '' : 'hidden' }} flex items-start gap-[7px] p-[10px] rounded-md bg-warning-50 dark:bg-[#15203c] text-[11px] text-warning-600 leading-[1.6] mb-[12px]">
                <i class="material-symbols-outlined !text-[15px] shrink-0">warning</i>
                <span>Anahtar açık ama gerekli bilgiler girilmemiş — eklenti sitede <strong>görünmüyor</strong>. “Ayarla”ya basıp tamamlayın.</span>
            </div>

            @if ($canUpdate)
                <div data-integration-edit-wrap class="mt-auto pt-[12px] border-t border-gray-100 dark:border-[#172036]">
                    <button type="button" data-integration-edit
                        class="inline-flex items-center gap-[6px] py-[7px] px-[14px] text-xs text-black dark:text-white rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c] transition-all">
                        <i class="material-symbols-outlined !text-[16px]">tune</i>
                        {{ $item['ready'] ? 'Ayarları düzenle' : 'Ayarla' }}
                    </button>
                </div>
            @endif
        </div>
    @endforeach
</div>
