@extends('admin.layout.app')
@section('admin.title', 'Tanıtım Alanı')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Tanıtım Alanı</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Tanıtım Alanı
            </li>
        </ol>
    </div>

    {{-- Tekil kayıt: liste ve modal yok, sayfanın kendisi formdur. --}}
    <form id="hero-form" action="{{ route('admin.hero.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">Ana Sayfa Tanıtım Bölümü</h5>
                </div>
                {{-- Tekil kayıt olduğu için satır bazlı "Geçmiş" yok; modül logu burada. --}}
                <x-admin::activity-log-button module="hero" />
            </div>
            <div class="trezo-card-content">
                <x-admin::form.input name="badge" help="hero.badge" label="Üst Etiket" :value="$hero->badge"
                    placeholder="Örn. Dijital Ajans" />

                <x-admin::form.input name="title" help="hero.title" label="Başlık" :value="$hero->title"
                    placeholder="Örn. Markanızı dijitalde büyütüyoruz" />

                <x-admin::form.textarea name="description" help="hero.description" label="Açıklama" :value="$hero->description"
                    placeholder="Başlığın altında görünecek kısa tanıtım metni" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px] mb-[20px] md:mb-[25px]">
                    <x-admin::form.input name="button_text" help="hero.button_text" label="Buton Yazısı" :value="$hero->button_text"
                        placeholder="Örn. Teklif Alın" wrapper="" />

                    <x-admin::form.input name="button_url" help="hero.button_url" label="Buton Bağlantısı" :value="$hero->button_url"
                        placeholder="Örn. /iletisim veya https://..." wrapper="" />
                </div>

                <x-admin::form.image name="gallery_media_ids" help="hero.gallery_media_ids" label="Görseller" multiple {{-- preset="hero.gallery" --}}
                    :media="$hero->getMedia('gallery')"
                    hint="Birden fazla görsel ekleyebilirsiniz; yıldız ile kapak görselini seçin." />
            </div>
        </div>

        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">Arka Plan Görseli</h5>
                </div>
            </div>
            <div class="trezo-card-content">
                <x-admin::form.image name="background_media_id" help="hero.background_media_id" preset="hero.background"
                    :media="$hero->getFirstMedia('background')" wrapper="" />
            </div>
            <div
                class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
                <button type="submit"
                    class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                    Kaydet
                </button>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/hero/index.js') }}"></script>
@endpush
