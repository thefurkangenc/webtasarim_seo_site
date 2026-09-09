@props(['for' => null, 'required' => false])

<label @if ($for) for="{{ $for }}" @endif class="mb-[8px] text-sm text-black dark:text-white font-medium block">
    {{ $slot }}@if ($required)<span class="text-danger-500 ltr:ml-[2px] rtl:mr-[2px]">*</span>@endif
</label>
