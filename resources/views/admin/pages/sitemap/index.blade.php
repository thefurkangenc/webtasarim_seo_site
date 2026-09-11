@extends('admin.layout.app')
@section('admin.title', 'Site Haritası')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Site Haritası</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Site Haritası
            </li>
        </ol>
    </div>

    <form id="sitemap-form" action="{{ route('admin.sitemap.update') }}" method="POST" data-generate-endpoint="{{ route('admin.sitemap.generate') }}">
        @csrf
        @method('PUT')

        {{-- Durum --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">Durum</h5>
                </div>
                @can('sitemap.generate')
                    <button type="button" id="sitemap-generate"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                        <i class="material-symbols-outlined !text-[18px]">autorenew</i>
                        Şimdi Yeniden Oluştur
                    </button>
                @endcan
            </div>
            <div class="trezo-card-content">
                <div data-sitemap-report>
                    @if ($report)
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-[12px] mb-[18px]">
                            <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[4px]">Toplam</span>
                                <span class="block text-lg font-bold text-black dark:text-white">{{ $report['total'] }}</span>
                            </div>
                            @foreach ($sources as $key => $label)
                                <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[4px] truncate" title="{{ $label }}">{{ $label }}</span>
                                    <span class="block text-lg font-bold text-black dark:text-white">{{ $report['sources'][$key]['count'] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Son üretim: {{ $report['generated_at']->diffForHumans() }}
                            ({{ $report['generated_at']->format('d.m.Y H:i') }})
                        </p>
                    @else
                        <div class="flex items-center gap-[10px] p-[14px] rounded-md bg-warning-50 border border-warning-200 dark:bg-[#15203c] dark:border-[#15203c] text-warning-600 text-sm">
                            <i class="material-symbols-outlined !text-[20px] shrink-0">info</i>
                            Henüz üretilmedi. “Şimdi Yeniden Oluştur”a basın; ya da bir içerik kaydedilince otomatik oluşur (kuyruk işçisinin çalışır olması gerekir).
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-[10px] mt-[18px] pt-[18px] border-t border-gray-100 dark:border-[#172036]">
                    <a href="{{ route('sitemap.index') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[16px]">open_in_new</i> sitemap.xml
                    </a>
                    @foreach ($sources as $key => $label)
                        @if (($report['sources'][$key]['files'] ?? []) !== [])
                            @foreach ($report['sources'][$key]['files'] as $file)
                                <a href="{{ url('/'.$file) }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-gray-500 dark:text-gray-400 transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                    <i class="material-symbols-outlined !text-[16px]">description</i> {{ $file }}
                                </a>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kaynaklar --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">
                        Kaynaklar
                        <x-admin::form.help topic="sitemap.sources" />
                    </h5>
                </div>
            </div>
            <div class="trezo-card-content">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
                    @foreach ($sources as $key => $label)
                        <x-admin::form.switch name="source_{{ $key }}" label="{{ $label }}"
                            :checked="$enabled[$key] ?? true" wrapper="mb-0" />
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Hariç tutulan / ek adresler --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">Elle Yönetilen Adresler</h5>
                </div>
            </div>
            <div class="trezo-card-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-[20px] md:gap-[25px]">
                    <x-admin::form.textarea name="excluded_urls" label="Hariç tutulan adresler" help="sitemap.excluded_urls"
                        :value="$excludedUrls" rows="5" placeholder="/ornek-kampanya" class="h-[130px]" wrapper="" />

                    <x-admin::form.textarea name="extra_urls" label="Ek adresler" help="sitemap.extra_urls"
                        :value="$extraUrls" rows="5" placeholder="https://siteniz.com/katalog.pdf" class="h-[130px]" wrapper="" />
                </div>
            </div>

        </div>

        {{-- robots.txt --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">
                        robots.txt
                        <x-admin::form.help topic="sitemap.robots_txt" />
                    </h5>
                </div>
                <a href="{{ route('robots') }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="material-symbols-outlined !text-[16px]">open_in_new</i> robots.txt
                </a>
            </div>
            <div class="trezo-card-content">
                <x-admin::form.textarea name="robots_txt" label="İçerik" :value="$robotsBody" rows="6"
                    class="h-[150px] font-mono" wrapper="" />

                <div class="flex items-start gap-[8px] mt-[14px] p-[12px] rounded-md bg-gray-50 dark:bg-[#15203c] text-xs text-gray-500 dark:text-gray-400">
                    <i class="material-symbols-outlined !text-[16px] shrink-0 text-primary-500">info</i>
                    <span>
                        Site haritası satırı çıktının sonuna kendiliğinden ekleniyor, elle yazmanız gerekmiyor:
                        <code class="text-black dark:text-white">Sitemap: {{ route('sitemap.index') }}</code>
                    </span>
                </div>
            </div>

            @can('sitemap.update')
                <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
                    <button type="submit"
                        class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        Kaydet
                    </button>
                </div>
            @endcan
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/sitemap/index.js') }}"></script>
@endpush
