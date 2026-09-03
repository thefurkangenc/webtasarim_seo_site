{{-- Klasör ağacının tek düğümü; alt klasörler için kendini çağırır. --}}
<li>
    <button type="button" data-folder-id="{{ $folder->id }}"
        class="media-folder w-full text-left rounded-md flex items-center gap-[7px] py-[7px] px-[10px] transition-all font-medium text-gray-500 dark:text-gray-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-[#15203c]"
        style="padding-inline-start: {{ 10 + $depth * 14 }}px">
        <i class="material-symbols-outlined !text-[18px] leading-none">folder</i>
        <span class="truncate">{{ $folder->name }}</span>
        @if ($folder->media_count ?? null)
            <span class="ltr:ml-auto rtl:mr-auto text-[10px] text-gray-400">{{ $folder->media_count }}</span>
        @endif
    </button>

    @if ($folder->children->isNotEmpty())
        <ul>
            @foreach ($folder->children as $child)
                @include('admin.pages.media.partials.folder-node', ['folder' => $child, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
