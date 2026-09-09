@php
    $canUpdate = auth()->user()->can('integration.update');
@endphp

<p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] md:mb-[25px]">
    Açmak için anahtarı kullanın. Gerekli bilgiler kaydedilmeden entegrasyon sitede görünmez.
</p>

<div id="integration-grid" data-can-update="{{ $canUpdate ? '1' : '0' }}"
    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[20px] md:gap-[25px]">
    @foreach ($integrations as $item)
        <div data-integration="{{ $item['key'] }}" data-title="{{ $item['title'] }}"
            class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] h-full">
            <div class="flex items-start justify-between gap-[15px] mb-[15px]">
                <span
                    class="w-[48px] h-[48px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/integrations/'.$item['icon']) }}" alt=""
                        class="w-[24px] h-[24px] object-contain">
                </span>

                @if ($canUpdate)
                    <x-admin::form.switch bare :checked="$item['enabled']" wrapper="shrink-0"
                        data-integration-toggle />
                @endif
            </div>

            <div class="flex items-center gap-[10px] mb-[8px]">
                <span class="font-semibold text-black dark:text-white">{{ $item['title'] }}</span>
                <span data-integration-badge
                    class="px-[8px] py-[3px] inline-block rounded-sm font-medium text-xs {{ $item['enabled'] ? 'bg-success-100 dark:bg-[#15203c] text-success-600' : 'bg-danger-100 dark:bg-[#15203c] text-danger-500' }}">
                    {{ $item['enabled'] ? 'Aktif' : 'Pasif' }}
                </span>
            </div>

            <p class="!mb-0 text-sm text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>

            @if ($canUpdate)
                <div data-integration-edit-wrap
                    class="{{ $item['enabled'] ? '' : 'hidden' }} mt-[20px] pt-[15px] border-t border-gray-100 dark:border-[#172036]">
                    <button type="button" data-integration-edit
                        class="inline-flex items-center gap-[6px] text-sm text-primary-500 leading-none transition-all hover:underline">
                        <i class="material-symbols-outlined !text-md">edit</i>
                        Düzenle
                    </button>
                </div>
            @endif
        </div>
    @endforeach
</div>
