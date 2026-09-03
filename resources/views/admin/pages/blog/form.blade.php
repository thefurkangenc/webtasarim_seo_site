@extends('admin.layout.app')
@section('admin.title', $blog ? 'Yazıyı Düzenle' : 'Yeni Yazı')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $blog ? 'Yazıyı Düzenle' : 'Yeni Yazı' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.blog.index') }}" class="transition-all hover:text-primary-500">Blog</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $blog ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    <form id="blog-form" data-id="{{ $blog?->id }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">

            {{-- Sol sütun: içerik --}}
            <div class="lg:col-span-2">
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">İçerik</h5>
                        </div>
                        @can('ai.generate')
                            <button type="button" id="blog-ai"
                                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[19px]">auto_awesome</i>
                                Yapay Zeka ile Oluştur
                            </button>
                        @endcan
                    </div>

                    <div class="trezo-card-content">
                        <x-admin::form.input name="title" label="Başlık" required :value="$blog?->title"
                            placeholder="Yazının başlığı" />

                        <x-admin::form.input name="slug" label="Kısa Ad (slug)" :value="$blog?->slug"
                            placeholder="Boş bırakılırsa başlıktan üretilir" />

                        <x-admin::form.textarea name="excerpt" label="Özet" rows="3" :value="$blog?->excerpt"
                            placeholder="Listelerde ve arama sonuçlarında görünecek kısa açıklama"
                            class="h-[90px]" />

                        <x-admin::form.editor name="content" label="İçerik" :value="$blog?->content" :height="560" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">SEO</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.seo :model="$blog" path="blog" wrapper="" />
                    </div>
                </div>
            </div>

            {{-- Sağ sütun: yayın ve sınıflandırma --}}
            <div>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Yayın</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="status" label="Durum" required
                            :options="\App\Models\Blog\Blog::STATUSES"
                            :value="$blog?->status ?? \App\Models\Blog\Blog::STATUS_DRAFT"
                            :placeholder="null" />

                        <x-admin::form.input name="published_at" type="datetime-local" label="Yayın Tarihi"
                            :value="$blog?->published_at?->format('Y-m-d\TH:i')" />

                        <x-admin::form.switch name="is_featured" label="Öne çıkar"
                            :checked="$blog?->is_featured ?? false" />

                        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                            <a href="{{ route('admin.blog.index') }}"
                                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Vazgeç
                            </a>
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                {{ $blog ? 'Güncelle' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Sınıflandırma</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.select name="blog_category_id" label="Kategori"
                            :options="$categories->all()" :value="$blog?->blog_category_id"
                            placeholder="Kategorisiz" />

                        <x-admin::form.tags :model="$blog" wrapper="" />
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Kapak Görseli</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="cover_media_id" preset="blog.cover"
                            :media="$blog?->getFirstMedia('cover')" wrapper="" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/blog/form.js') }}"></script>
@endpush
