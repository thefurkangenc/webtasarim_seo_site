@php
    $user = auth()->user();
    $avatar = $user?->avatarUrl();
@endphp

{{--
    Header. Trezo şablonundan gelen ve bu projede bir karşılığı OLMAYAN parçalar
    kaldırıldı; hepsi tıklanınca hiçbir şey yapmıyordu:

      · Dil menüsü      -> proje tek dilli (bkz. CLAUDE.md "Yapılmayacaklar")
      · Apps menüsü     -> Figma/Dribbble/Spotify bağlantıları, işle ilgisiz
      · RTL modu        -> arayüz yalnızca Türkçe/LTR
      · Sahte bildirim  -> yerine gerçek bildirim merkezi (core/notifications.js)
      · "Olivia John"   -> yerine oturumdaki kullanıcı

    Kalanların hepsi çalışıyor: sidebar aç/kapa, arama (Ctrl+K), tam ekran,
    tema, bildirimler, profil.
--}}
<div class="header-area bg-white dark:bg-[#0c1427] py-[13px] px-[20px] md:px-[25px] fixed top-0 z-[6] rounded-b-md transition-all"
    id="header-area">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex items-center justify-center md:justify-normal">
            <div class="relative leading-none top-px ltr:mr-[13px] ltr:md:mr-[18px] ltr:lg:mr-[23px] rtl:ml-[13px] rtl:md:ml-[18px] rtl:lg:ml-[23px]">
                <button type="button" class="hide-sidebar-toggle transition-all inline-block hover:text-primary-500"
                    id="hide-sidebar-toggle" title="Menüyü daralt/genişlet">
                    <i class="material-symbols-outlined !text-[20px]">menu</i>
                </button>
            </div>

            {{-- Global arama. Sonuçlar AJAX ile gelir ve kullanıcının izni
                 olmayan modüller sunucu tarafında ayıklanır. --}}
            <div class="relative w-[220px] lg:w-[300px]" data-global-search>
                <input type="text" data-search-input autocomplete="off" placeholder="Ara... (Ctrl+K)"
                    class="bg-gray-50 border border-gray-50 h-[44px] rounded-md w-full block text-black pt-[11px] pb-[12px] ltr:pl-[13px] ltr:md:pl-[16px] ltr:pr-[40px] rtl:pr-[13px] rtl:md:pr-[16px] rtl:pl-[40px] placeholder:text-gray-500 outline-0 transition-all focus:border-primary-500 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">

                <span class="absolute text-primary-500 mt-[2px] ltr:right-[13px] ltr:md:right-[15px] rtl:left-[13px] rtl:md:left-[15px] top-1/2 -translate-y-1/2 leading-none">
                    <i class="material-symbols-outlined !text-[20px]" data-search-icon>search</i>
                </span>

                <div data-search-results hidden
                    class="absolute z-[5] top-full ltr:left-0 rtl:right-0 mt-[8px] w-[340px] lg:w-[420px] max-h-[460px] overflow-y-auto rounded-md bg-white dark:bg-[#0c1427] shadow-3xl border border-gray-100 dark:border-[#172036] py-[8px]">
                </div>
            </div>
        </div>

        <ul class="flex items-center justify-center md:justify-normal mt-[13px] md:mt-0">
            <li class="relative mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                <button type="button" title="Açık/koyu tema"
                    class="light-dark-toggle leading-none inline-block transition-all relative top-[2px] text-[#fe7a36]"
                    id="light-dark-toggle">
                    <i class="material-symbols-outlined !text-[20px] md:!text-[22px]">light_mode</i>
                </button>
            </li>

            <li class="relative mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                <button type="button" title="Tam ekran"
                    class="leading-none inline-block transition-all relative top-[2px] hover:text-primary-500"
                    id="fullscreenBtn">
                    <i class="material-symbols-outlined !text-[22px] md:!text-xl" id="fullscreenIcon">fullscreen</i>
                </button>
            </li>

            {{-- Bildirimler: içerik core/notifications.js tarafından doldurulur. --}}
            <li class="relative notifications-menu mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0"
                data-notifications>
                <button type="button" id="dropdownToggleBtn" title="Bildirimler"
                    class="leading-none inline-block transition-all relative top-[2px] hover:text-primary-500">
                    <i class="material-symbols-outlined !text-[22px] md:!text-xl">notifications</i>
                    {{-- Okunmamış sayacı; sıfırken hiç basılmaz. --}}
                    <span data-notification-badge hidden
                        class="absolute -top-[5px] ltr:-right-[6px] rtl:-left-[6px] min-w-[17px] h-[17px] px-[4px] rounded-full bg-danger-500 text-white text-[10px] font-semibold leading-[17px] text-center"></span>
                </button>

                <div class="notifications-menu-dropdown bg-white dark:bg-[#0c1427] transition-all shadow-3xl dark:shadow-none py-[17px] absolute mt-[17px] md:mt-[20px] w-[300px] md:w-[380px] z-[1] top-full ltr:-right-[120px] ltr:md:right-0 rtl:-left-[120px] rtl:md:left-0 rounded-md">
                    <div class="flex items-center justify-between px-[20px] pb-[14px]">
                        <span class="font-semibold text-black dark:text-white text-[15px]">
                            Bildirimler
                            <span data-notification-count class="text-gray-500 dark:text-gray-400 font-normal text-sm"></span>
                        </span>
                        <button type="button" data-notification-read-all
                            class="text-primary-500 text-xs transition-all hover:underline">
                            Tümünü okundu yap
                        </button>
                    </div>

                    <div data-notification-list class="max-h-[380px] overflow-y-auto border-t border-gray-100 dark:border-[#172036]">
                        <p class="!mb-0 py-[30px] text-center text-sm text-gray-400">Yükleniyor…</p>
                    </div>
                </div>
            </li>

            <li class="relative profile-menu mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                <button type="button" id="dropdownToggleBtn"
                    class="flex items-center -mx-[5px] relative ltr:pr-[14px] rtl:pl-[14px] text-black dark:text-white">
                    {{-- Avatar yoksa baş harfler. Sabit bir şablon görseli
                         basmak yanlış bir yüz göstermek olurdu. --}}
                    <span data-user-avatar
                        class="w-[35px] h-[35px] md:w-[42px] md:h-[42px] rounded-full ltr:md:mr-[2px] ltr:lg:mr-[8px] rtl:md:ml-[2px] rtl:lg:ml-[8px] border-[2px] border-primary-200 flex items-center justify-center shrink-0 bg-primary-50 dark:bg-[#15203c] text-primary-500 text-sm font-bold overflow-hidden">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="{{ $user->name }}" class="w-full h-full rounded-full object-cover">
                        @else
                            {{ $user?->initials() }}
                        @endif
                    </span>
                    <span data-user-name class="block font-semibold text-[0px] lg:text-base">{{ $user?->name }}</span>
                    <i class="ri-arrow-down-s-line text-[15px] absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2 mt-px"></i>
                </button>

                <div class="profile-menu-dropdown bg-white dark:bg-[#0c1427] transition-all shadow-3xl dark:shadow-none py-[22px] absolute mt-[13px] md:mt-[14px] w-[230px] z-[1] top-full ltr:right-0 rtl:left-0 rounded-md">
                    <div class="flex items-center border-b border-gray-100 dark:border-[#172036] pb-[12px] mx-[20px] mb-[10px]">
                        <span data-user-avatar
                            class="rounded-full w-[34px] h-[34px] ltr:mr-[9px] rtl:ml-[9px] border-2 border-primary-200 flex items-center justify-center shrink-0 bg-primary-50 dark:bg-[#15203c] text-primary-500 text-xs font-bold overflow-hidden">
                            @if ($avatar)
                                <img src="{{ $avatar }}" alt="" class="w-full h-full rounded-full object-cover">
                            @else
                                {{ $user?->initials() }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <span data-user-name class="block text-black dark:text-white font-medium truncate">{{ $user?->name }}</span>
                            <span class="block text-xs truncate">{{ $user?->roleLabel() }}</span>
                        </div>
                    </div>

                    <ul>
                        <li>
                            <a href="{{ route('admin.profile.edit') }}"
                                class="block relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-black dark:text-white transition-all hover:text-primary-500">
                                <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">account_circle</i>
                                Profilim
                            </a>
                        </li>
                        @can('lead.index')
                            <li>
                                <a href="{{ route('admin.lead.index') }}"
                                    class="block relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-black dark:text-white transition-all hover:text-primary-500">
                                    <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">inbox</i>
                                    Gelen Talepler
                                </a>
                            </li>
                        @endcan
                        @can('activity-log.index')
                            <li>
                                <a href="{{ route('admin.activity-log.index') }}"
                                    class="block relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-black dark:text-white transition-all hover:text-primary-500">
                                    <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">history</i>
                                    Log Kayıtları
                                </a>
                            </li>
                        @endcan
                    </ul>

                    <div class="border-t border-gray-100 dark:border-[#172036] mx-[20px] my-[9px]"></div>

                    <ul>
                        @can('setting.index')
                            <li>
                                <a href="{{ route('admin.setting.index') }}"
                                    class="block relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-black dark:text-white transition-all hover:text-primary-500">
                                    <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">settings</i>
                                    Site Ayarları
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a href="{{ url('/') }}" target="_blank"
                                class="block relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-black dark:text-white transition-all hover:text-primary-500">
                                <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">open_in_new</i>
                                Siteyi Gör
                            </a>
                        </li>
                        <li>
                            {{-- Çıkış POST olmak zorunda (CSRF); şablonun <a href="logout.html">
                                 bağlantısı yerine gerçek bir form. --}}
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full ltr:text-left rtl:text-right relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-danger-500 transition-all hover:text-danger-600">
                                    <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">logout</i>
                                    Çıkış Yap
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>
