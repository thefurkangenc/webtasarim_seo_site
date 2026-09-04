{{--
    Ayar sekmeleri. Liste config/settings.php'den gelir; sidebar.blade.php
    elle düzenlenmez. Aktif sekme $group ile işaretlenir.
--}}
<div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
    <div class="trezo-card-content">
        <ul>
            @foreach ($groups as $key => $item)
                <li class="font-medium mb-[15px] md:mb-[19px] last:mb-0">
                    <a href="{{ route('admin.setting.edit', $key) }}"
                        class="relative flex items-center ltr:pl-[28px] rtl:pr-[28px] transition-all {{ $group === $key ? 'text-primary-500' : 'text-black dark:text-white hover:text-primary-500' }}">
                        <i class="material-symbols-outlined absolute !text-lg ltr:left-0 rtl:right-0 top-1/2 -translate-y-1/2 -mt-[.5px]">{{ $item['icon'] }}</i>
                        {{ $item['title'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
