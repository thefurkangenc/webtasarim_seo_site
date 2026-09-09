
<div class="trezo-card bg-white dark:bg-[#0c1427] p-[15px] md:p-[20px] rounded-md">
    <div class="trezo-card-content">
        <ul class="flex flex-col gap-[6px]">
            @foreach ($groups as $key => $item)
                @php($active = $group === $key)
                <li>
                    <a href="{{ route('admin.setting.edit', $key) }}"
                        @if ($active) aria-current="page" @endif
                        class="flex items-center gap-[10px] w-full rounded-md font-medium py-[9px] ltr:pl-[12px] ltr:pr-[14px] rtl:pr-[12px] rtl:pl-[14px] text-sm transition-all {{ $active
                            ? 'bg-primary-50 text-primary-500 dark:bg-primary-500/10'
                            : 'text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]' }}">
                        <span
                            class="flex items-center justify-center rounded-md w-[32px] h-[32px] shrink-0 {{ $active
                                ? 'bg-primary-500 text-white'
                                : 'bg-gray-50 text-gray-500 dark:bg-[#15203c] dark:text-gray-400' }}">
                            <i class="material-symbols-outlined !text-[20px] leading-none">{{ $item['icon'] }}</i>
                        </span>
                        <span class="leading-none">{{ $item['title'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
