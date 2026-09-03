@props(['name'])

{{-- core/form.js doğrulama mesajlarını buraya basar. --}}
<span class="text-danger-500 text-xs mt-[6px] block" data-error="{{ $name }}">{{ $errors->first($name) }}</span>
