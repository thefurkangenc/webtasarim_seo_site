@props([
    'name',
    'label' => null,
    'value' => null,
    'height' => 500,
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);
    $id = \App\Support\Field::id($name);
@endphp

@once
    @push('admin.scripts')
        <script src="{{ asset('admin/assets/js/vendor/tinymce/tinymce.min.js') }}"></script>
        <script type="module" src="{{ asset('admin/assets/js/core/editor.js') }}"></script>
    @endpush
@endonce

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$id" :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    {{-- core/editor.js bu alanı TinyMCE'ye çevirir; içerik gönderimden önce
         textarea'ya geri yazılır. --}}
    <textarea data-editor data-editor-height="{{ $height }}"
        name="{{ $field }}" id="{{ $id }}"
        {{ $attributes->merge(['class' => 'w-full']) }}>{{ old($name, $value) }}</textarea>

    <x-admin::form.error :name="$name" />
</div>
