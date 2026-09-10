@extends('admin.layout.app')
@section('admin.title', 'Menüler — ' . $workspace['menu']['name'])

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Menüler</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Menüler
            </li>
        </ol>
    </div>

    {{-- Konum sekmeleri --}}
    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[10px] rounded-md">
        <div class="flex flex-wrap gap-[6px]">
            @foreach ($workspace['menus'] as $location)
                <a href="{{ route('admin.menu.edit', $location['id']) }}"
                    @class([
                        'inline-flex items-center gap-[8px] py-[9px] px-[16px] rounded-md text-sm font-medium transition-all',
                        'bg-primary-500 text-white' => $location['id'] === $workspace['menu']['id'],
                        'text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]' => $location['id'] !== $workspace['menu']['id'],
                    ])>
                    <i class="material-symbols-outlined !text-[18px]">{{ $location['key'] === 'header' ? 'menu' : 'bottom_navigation' }}</i>
                    {{ $location['name'] }}
                    <span @class([
                        'text-[11px] py-[1px] px-[6px] rounded-full',
                        'bg-white/20' => $location['id'] === $workspace['menu']['id'],
                        'bg-gray-100 dark:bg-[#15203c] text-gray-500 dark:text-gray-400' => $location['id'] !== $workspace['menu']['id'],
                    ])>{{ $location['items_count'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">
        <div class="lg:col-span-2">
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Menü Yapısı</h5>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                            Öğeleri sürükleyip bırakarak sıralayın; sağa çekince alt menü olur.
                            En fazla {{ $workspace['maxDepth'] }} seviye.
                        </p>
                    </div>
                    <button type="button" data-menu-add
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        <i class="material-symbols-outlined !text-[19px]">add</i>
                        Yeni Öğe Ekle
                    </button>
                </div>

                <div class="trezo-card-content">
                    {{-- Ağaç core/menu-builder.js tarafından basılır. --}}
                    <div data-menu-tree></div>

                    <div data-menu-empty class="hidden text-center py-[40px] text-gray-500 dark:text-gray-400">
                        <i class="material-symbols-outlined !text-[40px] opacity-40">list</i>
                        <p class="mt-[8px] text-sm">Bu menüde henüz öğe yok. “Yeni Öğe Ekle” ile başlayın.</p>
                    </div>

                    <div data-menu-saving class="hidden items-center gap-[8px] text-xs text-gray-500 dark:text-gray-400 mt-[14px]">
                        <i class="material-symbols-outlined !text-[16px] animate-spin">progressActivity</i>
                        Sıralama kaydediliyor…
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Konum Ayarı</h5>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <form data-menu-settings>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-[16px]">
                            <strong class="text-black dark:text-white">{{ $workspace['menu']['name'] }}</strong> —
                            @if ($workspace['menu']['key'] === 'header')
                                sitenin üst navigasyonu. Başlık gösterilmez.
                            @else
                                footer bağlantı sütunu. Aşağıdaki başlık sütunun üstünde görünür.
                            @endif
                        </p>

                        @if ($workspace['menu']['key'] !== 'header')
                            <x-admin::form.input name="title" label="Sütun Başlığı"
                                :value="$workspace['menu']['title']" placeholder="Örn. Hızlı Erişim" />

                            <button type="submit"
                                class="inline-block py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                Başlığı Kaydet
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[15px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Nasıl Çalışır?</h5>
                    </div>
                </div>
                <div class="trezo-card-content text-xs text-gray-500 dark:text-gray-400 space-y-[8px]">
                    <p><strong class="text-black dark:text-white">Kayda bağlı</strong> öğeler, bağlı olduğu sayfa/hizmet/blog kaydının adresini kullanır — kaydın kısa adı değişse de bağ kopmaz.</p>
                    <p>Yayından kaldırılan ya da silinen bir kayda bağlı öğe sitede otomatik gizlenir.</p>
                    <p>Mobil menü ayrıca yönetilmez; üst menüden otomatik türetilir.</p>
                </div>
            </div>
        </div>
    </div>

    @include('admin.pages.menu.partials.item-modal')

    <script type="application/json" data-menu-workspace>@json($workspace)</script>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/menu/index.js') }}"></script>
@endpush
