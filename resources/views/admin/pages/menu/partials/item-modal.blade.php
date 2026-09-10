{{--
    "Yeni öğe ekle" ve "bağlantıyı değiştir" için ortak modal. Açma/kapama
    core/menu-builder.js'te; .add-new-popup + .active mekanizması template'e ait.

    Bağlantı tipi seçimine göre alanlar gösterilip gizlenir (data-when).
--}}
<div class="add-new-popup z-[1400] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]"
    data-menu-item-modal>
    <div class="popup-dialog flex transition-all max-w-[560px] min-h-full items-center mx-auto">
        <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                <div class="trezo-card-title">
                    <h5 class="!mb-0" data-menu-item-modal-title>Yeni Menü Öğesi</h5>
                </div>
                <button type="button" data-modal-close
                    class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                    <i class="ri-close-fill"></i>
                </button>
            </div>

            <div class="trezo-card-content">
                <form data-menu-item-form>
                    {{-- Bağlantı tipi --}}
                    <div class="mb-[20px]">
                        <x-admin::form.label>Bağlantı Tipi</x-admin::form.label>
                        <div class="grid grid-cols-3 gap-[8px]" data-menu-type-group>
                            @foreach (\App\Models\Menu\MenuItem::TYPES as $value => $label)
                                <button type="button" data-menu-type="{{ $value }}"
                                    class="py-[10px] px-[8px] rounded-md border text-sm font-medium transition-all border-gray-200 dark:border-[#172036] text-black dark:text-white hover:border-primary-500">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="link_type" value="url">
                    </div>

                    {{-- Özel bağlantı --}}
                    <div data-when="url">
                        <div class="mb-[20px]">
                            <x-admin::form.label for="menu-item-url">Adres</x-admin::form.label>
                            <input type="text" id="menu-item-url" name="url"
                                placeholder="/hakkimizda ya da https://..."
                                class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">
                            <span class="text-danger-500 text-xs mt-[6px] block" data-error="url"></span>
                        </div>
                    </div>

                    {{-- Hazır bağlantı --}}
                    <div data-when="route" hidden>
                        <div class="mb-[20px]">
                            <x-admin::form.label for="menu-item-route">Sayfa</x-admin::form.label>
                            <select id="menu-item-route" name="route_name" data-choices
                                class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all focus:border-primary-500">
                                <option value="">Seçin…</option>
                                @foreach ($workspace['routes'] as $name => $label)
                                    <option value="{{ $name }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger-500 text-xs mt-[6px] block" data-error="route_name"></span>
                        </div>
                    </div>

                    {{-- Kayda bağlı --}}
                    <div data-when="linkable" hidden>
                        <div class="mb-[20px]">
                            <x-admin::form.label for="menu-item-linkable">Kayıt</x-admin::form.label>
                            <select id="menu-item-linkable" data-menu-linkable data-choices
                                class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all focus:border-primary-500">
                                <option value="">Seçin…</option>
                                @foreach ($workspace['linkables'] as $key => $group)
                                    @if ($group['options'] !== [])
                                        <optgroup label="{{ $group['label'] }}">
                                            @foreach ($group['options'] as $option)
                                                <option value="{{ $key }}:{{ $option['id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            {{-- JS bunları ayrıştırıp gönderir --}}
                            <input type="hidden" name="linkable_type">
                            <input type="hidden" name="linkable_id">
                            <span class="text-danger-500 text-xs mt-[6px] block" data-error="linkable_id"></span>
                        </div>
                    </div>

                    {{-- Etiket --}}
                    <div class="mb-[20px]">
                        <x-admin::form.label for="menu-item-label">Menüde Görünen Ad</x-admin::form.label>
                        <input type="text" id="menu-item-label" name="label"
                            class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">
                        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block" data-menu-label-hint>
                            Kayda bağlı öğede boş bırakırsanız kaydın kendi adı kullanılır.
                        </span>
                        <span class="text-danger-500 text-xs mt-[6px] block" data-error="label"></span>
                    </div>

                    {{-- Açılış şekli --}}
                    <div class="mb-[25px]">
                        <x-admin::form.label for="menu-item-target">Açılış Şekli</x-admin::form.label>
                        <select id="menu-item-target" name="target" data-choices
                            class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all focus:border-primary-500">
                            <option value="_self">Aynı sekmede</option>
                            <option value="_blank">Yeni sekmede</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-[12px]">
                        <button type="button" data-modal-close
                            class="inline-block py-[10px] px-[22px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                            Vazgeç
                        </button>
                        <button type="submit"
                            class="inline-block py-[10px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
