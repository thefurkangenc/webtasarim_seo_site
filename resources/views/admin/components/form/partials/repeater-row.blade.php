{{-- <x-admin::form.repeater> satırı. Hem sunucu render'ı hem JS'in çoğalttığı
     <template> aynı dosyayı kullanır; ikisi ayrı yazılırsa zamanla ayrışır. --}}
<div data-repeater-row class="flex items-start gap-[8px]">
    <span data-repeater-handle
        class="shrink-0 w-[18px] h-[38px] flex items-center justify-center cursor-grab text-gray-300 dark:text-gray-600 transition-all hover:text-primary-500"
        title="Sürükleyip sırayı değiştirin">
        <i class="material-symbols-outlined !text-[18px]">drag_indicator</i>
    </span>

    <div class="flex-1 flex flex-col sm:flex-row gap-[8px]">
        @foreach ($columns as $column)
            @php
                $key = $column['key'];
                $value = $row[$key] ?? ($column['default'] ?? '');
            @endphp

            <div class="{{ $column['width'] ?? 'flex-1' }}">
                {{-- Mobilde başlık satırı gizli; etiket burada tekrar eder. --}}
                <span class="sm:hidden block text-[11px] text-gray-400 mb-[4px]">{{ $column['label'] }}</span>

                @if (($column['type'] ?? 'text') === 'select')
                    <select name="{{ $field }}[{{ $rowIndex }}][{{ $key }}]" class="{{ $selectClass }}">
                        @foreach ($column['options'] ?? [] as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="{{ $field }}[{{ $rowIndex }}][{{ $key }}]" value="{{ $value }}"
                        placeholder="{{ $column['placeholder'] ?? '' }}" class="{{ $inputClass }}">
                @endif
            </div>
        @endforeach
    </div>

    <button type="button" data-repeater-remove
        class="shrink-0 w-[30px] h-[38px] flex items-center justify-center rounded-md text-gray-400 transition-all hover:bg-danger-50 hover:text-danger-500 dark:hover:bg-[#15203c]"
        title="Satırı kaldır">
        <i class="material-symbols-outlined !text-[18px]">close</i>
    </button>
</div>
