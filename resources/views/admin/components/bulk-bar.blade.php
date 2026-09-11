@props([
    // config/bulk-actions.php'deki modül anahtarı.
    'module',
])

@php
    $actions = app(App\Services\Bulk\BulkService::class)->options($module);
@endphp

{{--
    Liste ekranlarının toplu işlem çubuğu. Satır seçilmeden gizlidir.

        <x-admin::bulk-bar module="blog" />

    Butonlar config'ten basılır; davranışı core/bulk.js verir. Sayfa JS'i
    yalnızca satır şablonuna `bulkCell(item)` ekler ve `new BulkBar(...)`
    kurar — modül başına arayüz kodu çoğaltılmaz.
--}}

@can($module.'.bulk')
    <div data-bulk-bar="{{ $module }}" data-bulk-endpoint="{{ url("admin/{$module}/bulk") }}"
        class="hidden items-center gap-[12px] flex-wrap mb-[20px] p-[14px] rounded-md bg-primary-50 dark:bg-[#15203c] border border-primary-100 dark:border-[#172036]">
        <span class="text-sm text-black dark:text-white whitespace-nowrap">
            <strong data-bulk-count>0</strong> kayıt seçildi
        </span>

        <div class="flex items-center gap-[8px] flex-wrap">
            @foreach ($actions as $action)
                @if ($action['input'] === 'select')
                    <select data-bulk-value="{{ $action['key'] }}"
                        class="h-[36px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[10px] outline-0 cursor-pointer">
                        <option value="">{{ $action['placeholder'] }}</option>
                        @foreach ($action['options'] ?? [] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif ($action['input'] === 'text')
                    <input type="text" data-bulk-value="{{ $action['key'] }}" placeholder="{{ $action['placeholder'] }}"
                        class="h-[36px] w-[200px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[10px] outline-0">
                @endif

                <button type="button" data-bulk-action="{{ $action['key'] }}"
                    @if ($action['danger']) data-bulk-danger="1" @endif
                    class="inline-flex items-center gap-[5px] py-[8px] px-[14px] text-sm rounded-md transition-all border {{ $action['danger'] ? 'text-danger-500 border-danger-200 hover:bg-danger-50 dark:border-[#172036] dark:hover:bg-[#0c1427]' : 'text-black dark:text-white border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-gray-50 dark:hover:bg-[#172036]' }}">
                    <i class="material-symbols-outlined !text-[17px]">{{ $action['icon'] }}</i>
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>

        <button type="button" data-bulk-clear
            class="text-sm text-gray-500 dark:text-gray-400 transition-all hover:text-primary-500 ltr:ml-auto rtl:mr-auto whitespace-nowrap">
            Seçimi temizle
        </button>
    </div>
@endcan
