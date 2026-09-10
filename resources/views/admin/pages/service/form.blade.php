@extends('admin.layout.app')
@section('admin.title', $service ? 'Hizmeti Düzenle' : 'Yeni Hizmet')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $service ? 'Hizmeti Düzenle' : 'Yeni Hizmet' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.service.index') }}" class="transition-all hover:text-primary-500">Hizmetler</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $service ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    <form id="service-form" data-id="{{ $service?->id }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">

            {{-- Sol sütun: içerik --}}
            <div class="lg:col-span-2">
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">İçerik</h5>
                        </div>
                        @can('ai.generate.form')
                            <button type="button" id="service-ai"
                                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[19px]">auto_awesome</i>
                                Yapay Zeka ile Oluştur
                            </button>
                        @endcan
                    </div>

                    <div class="trezo-card-content">
                        {{-- Yer tutucular: metin bir kez yazılır, her bölge sayfasında çözülür. --}}
                        <div class="py-[1rem] px-[1rem] mb-[20px] md:mb-[25px] text-primary-500 bg-primary-50 border border-primary-200 dark:bg-[#15203c] dark:border-[#15203c] rounded-md flex items-start gap-[5px]">
                            <i class="ri-information-line text-[20px]"></i>
                            <div>
                                Metinlerde <strong>@{{region}}</strong>, <strong>@{{city}}</strong> ve
                                <strong>@{{district}}</strong> yazabilirsiniz. Hizmetin her bölge sayfasında bunlar o
                                bölgenin adıyla değişir — örneğin Gaziantep › Şahinbey için sırasıyla
                                “Gaziantep Şahinbey”, “Gaziantep”, “Şahinbey”.
                            </div>
                        </div>

                        <x-admin::form.input name="title" help="service.title" label="Başlık" required :value="$service?->title"
                            placeholder="Hizmetin başlığı" />

                        <x-admin::form.input name="slug" help="common.slug" label="Kısa Ad (slug)" :value="$service?->slug"
                            placeholder="Boş bırakılırsa başlıktan üretilir" />

                        <x-admin::form.textarea name="excerpt" help="service.excerpt" label="Açıklama" rows="3" :value="$service?->excerpt"
                            placeholder="Listelerde ve arama sonuçlarında görünecek kısa açıklama"
                            class="h-[90px]" />

                        <x-admin::form.editor name="content" help="service.content" label="İçerik" :value="$service?->content" :height="560" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">SEO</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.seo :model="$service" path="hizmetler" imageSource="cover_media_id" analysisType="service" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Schema.org</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.schema :model="$service" wrapper="" />
                    </div>
                </div>
            </div>

            {{-- Sağ sütun: yayın, bölgeler, etiketler, görsel --}}
            <div>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Yayın</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="status" help="common.status" label="Durum" required
                            :options="\App\Models\Service\Service::STATUSES"
                            :value="$service?->status ?? \App\Models\Service\Service::STATUS_DRAFT"
                            :placeholder="null" />

                        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                            <a href="{{ route('admin.service.index') }}"
                                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Vazgeç
                            </a>
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                {{ $service ? 'Güncelle' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Hizmet Bölgeleri</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="service_regions" help="service.service_regions" label="Bölgeler" multiple
                            :options="$regions" :value="$service?->regions->pluck('id')->all() ?? []" wrapper="" />

                        {{-- Kısayollar: il kimlikleri sunucudan gelir, pages/service/form.js okur. --}}
                        <div class="mt-[10px] flex gap-[8px]">
                            <button type="button" id="service-regions-all"
                                data-city-ids="{{ json_encode($cityIds) }}"
                                class="py-[6px] px-[12px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Tüm illeri seç
                            </button>
                            <button type="button" id="service-regions-clear"
                                class="py-[6px] px-[12px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Temizle
                            </button>
                        </div>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Etiketler</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.tags :model="$service" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Kapak Görseli</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="cover_media_id" help="common.cover_media" preset="service.cover"
                            :media="$service?->getFirstMedia('cover')" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Sıkça Sorulan Sorular</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.faqs :model="$service" wrapper="" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/service/form.js') }}"></script>
@endpush
