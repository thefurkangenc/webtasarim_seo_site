@props([
    // config/form-help.php anahtarı (nokta notasyonu): "schema.search_url"
    'topic' => null,
    // topic verilmezse: elle başlık + slot gövdesi
    'title' => null,
])

@php
    $entry = $topic ? data_get(config('form-help'), $topic) : null;
    $helpTitle = $entry['title'] ?? $title;
    $helpBody = $entry['body'] ?? (trim($slot) ?: null);
@endphp

@if ($helpBody)
    {{-- Label metninin yanında küçük (?) ikonu. Popover'ı core/help-popover.js
         <body>'ye taşıyıp konumlar; içerik aşağıdaki <template>'te bekler. --}}
    <span class="help-tip inline-flex align-middle ltr:ml-[5px] rtl:mr-[5px]">
        <button type="button" data-help-trigger aria-expanded="false"
            aria-label="{{ $helpTitle ? $helpTitle.' — yardım' : 'Yardım' }}"
            class="inline-flex items-center justify-center w-[16px] h-[16px] rounded-full text-gray-400 hover:text-primary-500 transition-all cursor-help">
            <i class="material-symbols-outlined !text-[15px] !leading-[16px]">help</i>
        </button>
        <template data-help-content><div class="help-pop">@if ($helpTitle)<h4>{{ $helpTitle }}</h4>@endif{!! $helpBody !!}</div></template>
    </span>
@endif
