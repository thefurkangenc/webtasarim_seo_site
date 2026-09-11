@extends('admin.layout.app')
@section('admin.title', $project ? 'Projeyi Düzenle' : 'Yeni Proje')

@section('content')
    @php($directions = \App\Models\Project\Project::DIRECTIONS)

    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $project ? 'Projeyi Düzenle' : 'Yeni Proje' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.project.index') }}" class="transition-all hover:text-primary-500">Neler Yaptık</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $project ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    @if ($project)
        <div class="mb-[20px] flex items-center justify-end">
            <x-admin::revision-button :model="$project" />
        </div>
    @endif

    <form id="project-form" data-id="{{ $project?->id }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">

            {{-- Sol sütun: anlatım --}}
            <div class="lg:col-span-2">
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Proje Anlatımı</h5>
                        </div>
                        @can('ai.generate.form')
                            <button type="button" id="project-ai"
                                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[19px]">auto_awesome</i>
                                Yapay Zeka ile Oluştur
                            </button>
                        @endcan
                    </div>

                    <div class="trezo-card-content">
                        <x-admin::form.input name="title" help="project.title" label="Proje Başlığı" required
                            :value="$project?->title" placeholder="Örn. Akdeniz Yapı Kurumsal Web Sitesi" />

                        <x-admin::form.input name="slug" help="common.slug" label="Kısa Ad (slug)" :value="$project?->slug"
                            placeholder="Boş bırakılırsa başlıktan üretilir" />

                        <x-admin::form.textarea name="excerpt" help="project.excerpt" label="Kısa Açıklama" rows="3"
                            :value="$project?->excerpt" class="h-[90px]"
                            placeholder="Listede ve arama sonuçlarında görünecek 1-2 cümle" />

                        <x-admin::form.editor name="content" help="project.content" label="Proje Anlatımı"
                            :value="$project?->content" :height="520" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Proje Künyesi</h5>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Ön yüzde proje sayfasının yanındaki bilgi kutusunu besler. Boş alanlar hiç basılmaz.
                            </span>
                        </div>
                    </div>

                    <div class="trezo-card-content">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[20px]">
                            <x-admin::form.input name="client_name" help="project.client_name" label="Müşteri / Marka"
                                :value="$project?->client_name" placeholder="Örn. Akdeniz Yapı A.Ş." />

                            <x-admin::form.input name="sector" help="project.sector" label="Sektör"
                                :value="$project?->sector" placeholder="Örn. İnşaat" />
                        </div>

                        <x-admin::form.input name="project_url" type="url" help="project.project_url"
                            label="Yayındaki Site Adresi" :value="$project?->project_url"
                            placeholder="https://akdenizyapi.com" />

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-[20px]">
                            <x-admin::form.date name="started_at" help="project.dates" label="Başlangıç"
                                :value="$project?->started_at" :time="false" />

                            <x-admin::form.date name="completed_at" label="Bitiş"
                                :value="$project?->completed_at" :time="false" />

                            <x-admin::form.input name="duration" label="Süre" :value="$project?->duration"
                                placeholder="Örn. 6 hafta" />
                        </div>

                        <x-admin::form.chips name="technologies" help="project.technologies"
                            label="Kullanılan Teknolojiler / Kapsam" :values="$project?->technologies ?? []"
                            placeholder="Laravel, SEO danışmanlığı..."
                            hint="Enter ya da virgül ile ekleyin. Künyede liste olarak görünür." />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Sonuçlar</h5>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Her satır proje sayfasında bir rakam kutusu olur. Elinizde ölçüm yoksa boş bırakın.
                            </span>
                        </div>
                    </div>

                    <div class="trezo-card-content">
                        <x-admin::form.repeater name="results" help="project.results" :rows="$project?->results ?? []"
                            add-label="Sonuç ekle" wrapper=""
                            empty-text="Henüz bir sonuç eklenmedi — “Sonuç ekle” ile başlayın."
                            :columns="[
                                ['key' => 'label', 'label' => 'Ölçüm', 'placeholder' => 'Organik trafik', 'width' => 'flex-1'],
                                ['key' => 'value', 'label' => 'Değer', 'placeholder' => '+%140', 'width' => 'sm:w-[140px]'],
                                ['key' => 'direction', 'label' => 'Yön', 'type' => 'select', 'options' => $directions, 'default' => 'up', 'width' => 'sm:w-[130px]'],
                            ]" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Proje Galerisi</h5>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Ekran görüntüleri, öncesi/sonrası, detaylar. Sürükleyerek sıralayın.
                            </span>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="gallery_media_ids" label="" multiple preset="project.gallery"
                            :media="$project?->getMedia('gallery')" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Video</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.video name="video_url" media-name="video_media_id" label=""
                            help="project.video_url" :value="$project?->video_url"
                            :media="$project?->getFirstMedia('video')" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">SEO</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.seo :model="$project" path="neler-yaptik" imageSource="cover_media_id"
                            analysisType="project" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Schema.org</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.schema :model="$project" wrapper="" />
                    </div>
                </div>
            </div>

            {{-- Sağ sütun: yayın, sınıflandırma, görsel, bağlantılar --}}
            <div>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Yayın</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="status" help="common.status" label="Durum" required
                            :options="\App\Models\Project\Project::STATUSES"
                            :value="$project?->status ?? \App\Models\Project\Project::STATUS_DRAFT"
                            :placeholder="null" />

                        <x-admin::form.select name="project_category_id" help="project.project_category_id"
                            label="Kategori" :options="$categories" :value="$project?->project_category_id"
                            placeholder="Kategori seçin" />

                        <x-admin::form.switch name="is_featured" help="project.is_featured" label="Öne çıkan proje"
                            :checked="$project?->is_featured ?? false" />

                        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                            <a href="{{ route('admin.project.index') }}"
                                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Vazgeç
                            </a>
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                {{ $project ? 'Güncelle' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Kapak Görseli</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="cover_media_id" help="common.cover_media" preset="project.cover"
                            :media="$project?->getFirstMedia('cover')" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Bağlı Hizmetler</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="services" help="project.services" label="" multiple
                            :options="$services" :value="$project?->services->pluck('id')->all() ?? []"
                            placeholder="Hizmet seçin" wrapper="" />

                        <p class="!mb-0 mt-[10px] text-xs text-gray-500 dark:text-gray-400">
                            Seçtiğiniz hizmetin sayfasında bu proje “yaptığımız işler” olarak gösterilebilir.
                        </p>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Müşteri Yorumu</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="testimonial_id" help="project.testimonial_id" label=""
                            :options="$testimonials" :value="$project?->testimonial_id"
                            placeholder="Yorum seçin (opsiyonel)" wrapper="" />

                        <a href="{{ route('admin.testimonial.index') }}" target="_blank"
                            class="inline-flex items-center gap-[5px] mt-[10px] text-xs text-primary-500 hover:underline">
                            <i class="material-symbols-outlined !text-[15px]">open_in_new</i>
                            Yorumları yönet
                        </a>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Etiketler</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.tags :model="$project" label="" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Sıkça Sorulan Sorular</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.faqs :model="$project" wrapper="" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/project/form.js') }}"></script>
@endpush
