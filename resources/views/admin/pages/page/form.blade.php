@extends('admin.layout.app')
@section('admin.title', $page ? 'Sayfayı Düzenle' : 'Yeni Sayfa')

@php
    $templates = config('pages.templates');
    $templateOptions = collect($templates)->map(fn ($template) => $template['label'])->all();
    $templateHints = collect($templates)->map(fn ($template) => $template['description'])->all();

    // SEO önizlemesinin ve adres satırının kök aldığı yol: yeni sayfada boş,
    // alt sayfada üst sayfanın yolu. Slug bunun arkasına eklenir.
    $basePath = $page?->parent?->path ?? '';
@endphp

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $page ? 'Sayfayı Düzenle' : 'Yeni Sayfa' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.page.index') }}" class="transition-all hover:text-primary-500">Sayfalar</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $page ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    <form id="page-form" data-id="{{ $page?->id }}"
        data-host="{{ rtrim(config('app.url'), '/') }}"
        data-parent-paths="{{ json_encode($parentPaths) }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">

            {{-- Sol sütun: içerik --}}
            <div class="lg:col-span-2">
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">İçerik</h5>
                        </div>
                        @can('ai.generate.form')
                            <button type="button" id="page-ai"
                                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[19px]">auto_awesome</i>
                                Yapay Zeka ile Oluştur
                            </button>
                        @endcan
                    </div>

                    <div class="trezo-card-content">
                        <x-admin::form.input name="title" label="Başlık" required :value="$page?->title"
                            placeholder="Örn. Kariyer" />

                        <x-admin::form.input name="slug" label="Kısa Ad (slug)" :value="$page?->slug"
                            placeholder="Boş bırakılırsa başlıktan üretilir" />

                        {{-- Canlı adres satırı: üst sayfa ya da kısa ad değiştikçe
                             pages/page/form.js burayı günceller. --}}
                        <div class="mb-[20px] md:mb-[25px] -mt-[12px] flex items-start gap-[6px] text-xs text-gray-500 dark:text-gray-400">
                            <i class="ri-link !text-[14px] mt-[1px] shrink-0"></i>
                            <span class="break-all">
                                Adres: <span data-page-url class="font-medium text-primary-500">—</span>
                            </span>
                        </div>

                        <x-admin::form.textarea name="excerpt" label="Özet" rows="3" :value="$page?->excerpt"
                            placeholder="Listelerde ve arama sonuçlarında görünecek kısa açıklama"
                            class="h-[90px]" />

                        <x-admin::form.editor name="content" label="İçerik" :value="$page?->content" :height="600" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">SEO</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.seo :model="$page" :path="$basePath" imageSource="cover_media_id" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Schema.org</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.schema :model="$page" wrapper="" />
                    </div>
                </div>
            </div>

            {{-- Sağ sütun: yayın, adres, etiketler, görsel, SSS --}}
            <div>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[10px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Yayın</h5>
                        </div>
                        @if ($page)
                            <a href="{{ $page->url() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-[4px] text-xs text-gray-500 dark:text-gray-400 transition-all hover:text-primary-500">
                                <i class="material-symbols-outlined !text-[16px]">open_in_new</i>
                                Görüntüle
                            </a>
                        @endif
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="status" label="Durum" required
                            :options="\App\Models\Page\Page::STATUSES"
                            :value="$page?->status ?? \App\Models\Page\Page::STATUS_DRAFT"
                            :placeholder="null" />

                        {{-- placeholder verilmiyor: form.date kendi placeholder'ını
                             (gg.aa.yyyy ss:dd) sabitliyor, ikinci bir değer yinelenen
                             HTML niteliği üretir ve tarayıcı ilkini kullanır. --}}
                        <x-admin::form.date name="published_at" label="Yayın Tarihi" :value="$page?->published_at" />

                        <p class="-mt-[12px] mb-[20px] md:mb-[25px] text-xs text-gray-500 dark:text-gray-400">
                            Boş bırakılırsa sayfa kaydedildiği anda yayınlanır. İleri bir tarih girilirse
                            “Yayında” işaretli kalır ama o tarihe kadar sitede görünmez.
                        </p>

                        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                            <a href="{{ route('admin.page.index') }}"
                                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Vazgeç
                            </a>
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                {{ $page ? 'Güncelle' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Adres ve Şablon</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        {{-- Seçeneklerde sayfanın kendisi ve altındaki her şey yok:
                             bir sayfa kendi torununun çocuğu olamaz. --}}
                        <x-admin::form.select name="parent_id" label="Üst Sayfa" :options="$parents"
                            :value="$page?->parent_id" placeholder="Üst seviye (site kökü)" />

                        <x-admin::form.select name="template" label="Şablon" required :options="$templateOptions"
                            :value="$page?->template ?? 'default'" :placeholder="null" wrapper="" />

                        <p data-template-hint data-descriptions="{{ json_encode($templateHints) }}"
                            class="mt-[8px] text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Etiketler</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.tags :model="$page" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Üst Görsel</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="cover_media_id" preset="page.cover"
                            :media="$page?->getFirstMedia('cover')" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Sıkça Sorulan Sorular</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.faqs :model="$page" wrapper="" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/page/form.js') }}"></script>
@endpush
