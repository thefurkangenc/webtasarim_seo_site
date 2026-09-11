@extends('admin.layout.app')
@section('admin.title', 'Hızlı İndeksleme')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">
            Hızlı İndeksleme
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(IndexNow)</span>
        </h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Hızlı İndeksleme</li>
        </ol>
    </div>

    <div data-indexnow
        data-submit="{{ route('admin.indexnow.submit') }}"
        data-submit-all="{{ route('admin.indexnow.submit-all') }}"
        data-key-endpoint="{{ route('admin.indexnow.regenerate-key') }}">

        {{-- Ne işe yarar --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content">
                <div class="flex items-start gap-[12px]">
                    <span class="shrink-0 w-[38px] h-[38px] rounded-full bg-primary-500 text-white flex items-center justify-center">
                        <i class="material-symbols-outlined !text-[20px]">bolt</i>
                    </span>
                    <div class="text-sm text-gray-600 dark:text-gray-300 leading-[1.7]">
                        <p class="mb-[8px]">
                            Normalde bir sayfayı yayınladığınızda arama motorunun onu kendi kendine bulmasını
                            beklersiniz — bu günler sürebilir. <strong>IndexNow</strong>, “şu adres değişti,
                            gel bak” diyerek bu beklemeyi ortadan kaldırır.
                        </p>
                        <p class="mb-0">
                            Bildirimi alan motorlar:
                            @foreach ($engines as $engine)
                                <span class="text-[11px] font-medium py-[1px] px-[8px] text-primary-600 bg-primary-100 dark:bg-[#ffffff14] inline-block rounded-sm">{{ $engine }}</span>
                            @endforeach
                            <br>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <strong>Google bu protokolü desteklemiyor</strong> — Google tarafı için
                                <a href="{{ route('admin.search-console.index') }}" class="text-primary-500 hover:underline">Search Console</a>
                                ekranından site haritanızı bildirmeniz yeterlidir. İkisi birbirinin yerine geçmez, birlikte çalışır.
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ayarlar --}}
        <form id="indexnow-form" action="{{ route('admin.indexnow.update') }}" method="POST"
            class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            @csrf
            @method('PUT')

            <div class="trezo-card-header mb-[20px]">
                <div class="trezo-card-title"><h5 class="!mb-0">Ayarlar</h5></div>
            </div>
            <div class="trezo-card-content">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
                    <x-admin::form.switch name="enabled" label="IndexNow açık" help="indexnow.enabled"
                        :checked="$enabled" wrapper="mb-0" />
                    <x-admin::form.switch name="auto_submit" label="İçerik kaydedilince kendiliğinden bildir"
                        help="indexnow.auto_submit" :checked="$autoSubmit" wrapper="mb-0" />
                </div>

                <div class="mt-[20px] p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                    <div class="flex items-start justify-between gap-[12px] flex-wrap">
                        <div class="min-w-0">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[4px]">
                                Doğrulama anahtarı
                                <x-admin::form.help topic="indexnow.key" />
                            </span>
                            <code class="text-xs text-black dark:text-white break-all" data-indexnow-key>{{ $key }}</code>
                            <a href="{{ $keyUrl }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-[4px] text-xs text-primary-500 hover:underline mt-[6px]"
                                data-indexnow-key-url>
                                <i class="material-symbols-outlined !text-[15px]">open_in_new</i>
                                Anahtar dosyasını aç
                            </a>
                        </div>
                        @can('indexnow.regenerate-key')
                            <button type="button" id="indexnow-new-key"
                                class="inline-flex items-center gap-[6px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-white dark:hover:bg-[#0c1427]">
                                <i class="material-symbols-outlined !text-[16px]">autorenew</i>
                                Anahtarı yenile
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            @can('indexnow.update')
                <div class="trezo-card-footer flex items-center justify-end -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
                    <button type="submit"
                        class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        Kaydet
                    </button>
                </div>
            @endcan
        </form>

        {{-- Elle bildirim --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] flex items-center justify-between gap-[12px] flex-wrap">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">
                        Elle bildirim
                        <x-admin::form.help topic="indexnow.manual" />
                    </h5>
                </div>
                @can('indexnow.submit-all')
                    <button type="button" id="indexnow-submit-all"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px]">playlist_add_check</i>
                        Tüm adresleri bildir ({{ $sitemapUrlCount }})
                    </button>
                @endcan
            </div>
            <div class="trezo-card-content">
                <x-admin::form.textarea name="urls" label="Adresler" :value="null" rows="4"
                    placeholder="/hakkimizda&#10;/blog/yeni-yazi" class="h-[110px]" wrapper="" />

                @can('indexnow.submit')
                    <div class="flex justify-end mt-[15px]">
                        <button type="button" id="indexnow-submit"
                            class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            <i class="material-symbols-outlined !text-[18px]">send</i>
                            Bildir
                        </button>
                    </div>
                @endcan
            </div>
        </div>

        {{-- Geçmiş --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px]">
                <div class="trezo-card-title"><h5 class="!mb-0">Gönderim geçmişi</h5></div>
            </div>
            <div class="trezo-card-content">
                @if ($history === [])
                    <span class="text-sm text-gray-400">Henüz bildirim gönderilmedi.</span>
                @else
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-black dark:text-white">
                                <tr>
                                    <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] first:rounded-tl-md">Zaman</th>
                                    <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c]">Kaynak</th>
                                    <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Adres</th>
                                    <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] last:rounded-tr-md">Sonuç</th>
                                </tr>
                            </thead>
                            <tbody class="text-black dark:text-white">
                                @foreach ($history as $entry)
                                    <tr class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                                        <td class="py-[9px] px-[12px] whitespace-nowrap">
                                            {{ $entry['at']->format('d.m.Y H:i') }}
                                            <span class="block text-[11px] text-gray-400">{{ $entry['at']->diffForHumans() }}</span>
                                        </td>
                                        <td class="py-[9px] px-[12px] whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $entry['source'] }}</td>
                                        <td class="py-[9px] px-[12px] text-right font-medium whitespace-nowrap">{{ $entry['count'] }}</td>
                                        <td class="py-[9px] px-[12px]">
                                            @if ($entry['ok'])
                                                <span class="text-[10px] font-medium py-[1px] px-[8px] text-success-600 bg-success-100 dark:bg-[#ffffff14] inline-block rounded-sm">Başarılı</span>
                                            @else
                                                <span class="text-[10px] font-medium py-[1px] px-[8px] text-danger-500 bg-danger-100 dark:bg-[#ffffff14] inline-block rounded-sm">Hata</span>
                                            @endif
                                            <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-[3px]">{{ $entry['message'] }}</span>
                                            @if ($entry['sample'] !== [])
                                                <span class="block text-[11px] text-gray-400 truncate max-w-[320px]">{{ implode(', ', $entry['sample']) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/indexnow/index.js') }}"></script>
@endpush
