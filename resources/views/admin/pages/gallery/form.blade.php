@extends('admin.layout.app')
@section('admin.title', $gallery ? 'Galeriyi Düzenle' : 'Yeni Galeri')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $gallery ? 'Galeriyi Düzenle' : 'Yeni Galeri' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.gallery.index') }}" class="transition-all hover:text-primary-500">Foto Galeri</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $gallery ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    @if ($gallery)
        <div class="mb-[20px] flex items-center justify-end">
            <x-admin::revision-button :model="$gallery" />
        </div>
    @endif

    <form id="gallery-form" data-id="{{ $gallery?->id }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">

            <div class="lg:col-span-2">
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Galeri Bilgileri</h5>
                        </div>
                    </div>

                    <div class="trezo-card-content">
                        <x-admin::form.input name="title" help="gallery.title" label="Başlık" required
                            :value="$gallery?->title" placeholder="Örn. Ofisimizden Kareler" />

                        <x-admin::form.input name="slug" help="common.slug" label="Kısa Ad (slug)" :value="$gallery?->slug"
                            placeholder="Boş bırakılırsa başlıktan üretilir" />

                        <x-admin::form.textarea name="description" help="gallery.description" label="Açıklama" rows="4"
                            :value="$gallery?->description" class="h-[120px]"
                            placeholder="Galerinin neyi gösterdiğini 1-2 cümlede yazın" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0 inline-flex items-center">Fotoğraflar <x-admin::form.help topic="gallery.gallery_media_ids" /></h5>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Birden fazla yükleyin, sürükleyerek sıralayın. Yıldız ile kapak fotoğrafını seçin.
                            </span>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="gallery_media_ids" label=""
                            multiple preset="gallery.photo" :media="$gallery?->getMedia('gallery')" wrapper="" />
                    </div>
                </div>

                @can('gallery.seo')
                    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                        <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                            <div class="trezo-card-title">
                                <h5 class="!mb-0">SEO</h5>
                            </div>
                        </div>
                        <div class="trezo-card-content">
                            <x-admin::form.seo :model="$gallery" path="galeri" titleSource="title"
                                descriptionSource="description" contentSource="description" analysisType="gallery"
                                wrapper="" />
                        </div>
                    </div>
                @endcan

                @can('gallery.schema-org')
                    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                        <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                            <div class="trezo-card-title">
                                <h5 class="!mb-0">Schema.org</h5>
                            </div>
                        </div>
                        <div class="trezo-card-content">
                            <x-admin::form.schema :model="$gallery" wrapper="" />
                        </div>
                    </div>
                @endcan
            </div>

            <div>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Yayın</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="status" help="common.status" label="Durum" required
                            :options="\App\Models\Gallery\Gallery::STATUSES"
                            :value="$gallery?->status ?? \App\Models\Gallery\Gallery::STATUS_DRAFT"
                            :placeholder="null" />

                        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                            <a href="{{ route('admin.gallery.index') }}"
                                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Vazgeç
                            </a>
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                {{ $gallery ? 'Güncelle' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('assets/admin/js/pages/gallery/form.js') }}"></script>
@endpush
