<form action="{{ route('admin.media.update', $media) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="flex items-start gap-[15px] mb-[20px]">
        <div class="shrink-0 w-[120px] h-[90px] rounded-md overflow-hidden border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c]">
            @if ($media->isImage())
                <img src="{{ $media->url('thumb') }}" alt="{{ $media->alt }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400">
                    <i class="material-symbols-outlined !text-[30px]">draft</i>
                </div>
            @endif
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 leading-[1.9] min-w-0">
            <div class="truncate">{{ $media->original_name }}</div>
            <div>{{ strtoupper($media->extension) }} · {{ $media->humanSize() }}
                @if ($media->width) · {{ $media->width }}×{{ $media->height }} @endif
            </div>
            <a href="{{ $media->url() }}" target="_blank" class="text-primary-500 hover:underline">Yeni sekmede aç</a>
        </div>
    </div>

    <x-admin::form.input name="name" label="Dosya Adı" :value="$media->name" :required="true" />
    <x-admin::form.input name="alt" label="Alternatif Metin (alt)" :value="$media->alt"
        placeholder="Görselin içeriğini kısaca tarif edin" />
    <x-admin::form.input name="title" label="Başlık" :value="$media->title" />
    <x-admin::form.select name="folder_id" label="Klasör" :options="$folderOptions" :value="$media->folder_id"
        placeholder="Klasörsüz" />

    <x-admin::form.actions submit="Güncelle" />
</form>
