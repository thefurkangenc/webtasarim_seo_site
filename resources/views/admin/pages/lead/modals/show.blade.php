@php
    $meta = [
        ['icon' => 'schedule', 'label' => 'Geldiği zaman', 'value' => $lead->created_at?->format('d.m.Y H:i')],
        ['icon' => 'inbox', 'label' => 'Kaynak', 'value' => $lead->sourceLabel()],
        ['icon' => 'description', 'label' => 'Gönderildiği sayfa', 'value' => $lead->page_url],
        ['icon' => 'router', 'label' => 'IP adresi', 'value' => $lead->ip_address],
        ['icon' => 'devices', 'label' => 'Tarayıcı', 'value' => $lead->user_agent],
        ['icon' => 'drafts', 'label' => 'Okundu', 'value' => $lead->read_at?->format('d.m.Y H:i')],
        ['icon' => 'reply', 'label' => 'Yanıtlandı', 'value' => $lead->replied_at?->format('d.m.Y H:i')],
    ];
    $assigneeOptions = $users;
@endphp

<div data-lead-detail data-id="{{ $lead->id }}">

    {{-- Gönderen --}}
    <div class="flex flex-wrap items-start justify-between gap-[12px] pb-[18px] mb-[18px] border-b border-gray-100 dark:border-[#172036]">
        <div class="min-w-0">
            <h6 class="!mb-[4px] text-black dark:text-white">{{ $lead->name }}</h6>
            <div class="flex flex-wrap items-center gap-x-[14px] gap-y-[4px] text-sm">
                <a href="mailto:{{ $lead->email }}" class="text-primary-500 hover:underline break-all">{{ $lead->email }}</a>
                @if (filled($lead->phone))
                    <a href="tel:{{ $lead->phone }}" class="text-primary-500 hover:underline">{{ $lead->phone }}</a>
                @endif
            </div>
        </div>
        <span class="text-[10px] font-medium py-[2px] px-[9px] text-{{ $lead->statusColor() }}-600 bg-{{ $lead->statusColor() }}-100 dark:bg-[#ffffff14] inline-block rounded-sm shrink-0">
            {{ $lead->statusLabel() }}
        </span>
    </div>

    {{-- Mesaj --}}
    <div class="mb-[20px]">
        @if (filled($lead->subject))
            <span class="block text-sm font-medium text-black dark:text-white mb-[6px]">{{ $lead->subject }}</span>
        @endif
        <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c] text-sm text-black dark:text-white whitespace-pre-wrap break-words">{{ $lead->message }}</div>
    </div>

    {{-- Teknik bilgi --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[10px] mb-[22px]">
        @foreach ($meta as $row)
            @if (filled($row['value']))
                <div class="flex items-start gap-[8px] text-xs">
                    <i class="material-symbols-outlined !text-[16px] text-gray-400 shrink-0">{{ $row['icon'] }}</i>
                    <span class="text-gray-500 dark:text-gray-400 shrink-0">{{ $row['label'] }}:</span>
                    <span class="text-black dark:text-white break-all">{{ $row['value'] }}</span>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Durum / atama / iç not --}}
    <form data-lead-update-form action="{{ route('admin.lead.update', $lead) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
            <x-admin::form.select name="status" label="Durum" help="lead.status"
                :options="collect(config('leads.statuses'))->map(fn ($meta) => $meta['label'])->all()"
                :value="$lead->status" wrapper="mb-0" />

            <x-admin::form.select name="assigned_to" label="İlgilenen kişi" help="lead.assigned_to"
                :options="$assigneeOptions" :value="$lead->assigned_to" placeholder="Atanmadı" wrapper="mb-0" />
        </div>

        <x-admin::form.textarea name="note" label="İç not" help="lead.note" :value="$lead->note" rows="3"
            placeholder="Yalnızca panelde görünür — müşteriye gitmez." class="h-[90px]"
            wrapper="mt-[15px] mb-0" />

        <div class="flex items-center justify-between gap-[10px] mt-[15px]">
            <div class="flex items-center gap-[8px]">
                @can('lead.read')
                    <button type="button" data-lead-toggle-read data-url="{{ route('admin.lead.read', $lead) }}"
                        class="inline-flex items-center gap-[5px] py-[7px] px-[12px] text-xs text-black dark:text-white rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c] transition-all">
                        <i class="material-symbols-outlined !text-[16px]">{{ $lead->isRead() ? 'mark_email_unread' : 'mark_email_read' }}</i>
                        {{ $lead->isRead() ? 'Okunmadı işaretle' : 'Okundu işaretle' }}
                    </button>
                @endcan
                @can('lead.destroy')
                    <button type="button" data-lead-delete data-url="{{ route('admin.lead.destroy', $lead) }}"
                        class="inline-flex items-center gap-[5px] py-[7px] px-[12px] text-xs text-danger-500 rounded-md border border-danger-200 dark:border-[#172036] hover:bg-danger-50 dark:hover:bg-[#15203c] transition-all">
                        <i class="material-symbols-outlined !text-[16px]">delete</i> Sil
                    </button>
                @endcan
            </div>

            @can('lead.update')
                <button type="submit"
                    class="inline-block py-[9px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                    Kaydet
                </button>
            @endcan
        </div>
    </form>

    {{-- Yanıt --}}
    @can('lead.reply')
        <div class="mt-[22px] pt-[18px] border-t border-gray-100 dark:border-[#172036]">
            <div class="flex items-center justify-between gap-[10px] mb-[12px]">
                <span class="text-sm font-medium text-black dark:text-white">
                    E-posta ile yanıtla
                    <x-admin::form.help topic="lead.reply" />
                </span>
                <button type="button" data-lead-reply-toggle
                    class="inline-flex items-center gap-[5px] py-[6px] px-[12px] text-xs text-black dark:text-white rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c] transition-all">
                    <i class="material-symbols-outlined !text-[16px]">reply</i> Yanıt yaz
                </button>
            </div>

            <form data-lead-reply-form action="{{ route('admin.lead.reply', $lead) }}" method="POST" class="hidden">
                @csrf

                <x-admin::form.input name="subject" label="Konu"
                    :value="'Re: '.(filled($lead->subject) ? $lead->subject : 'Mesajınız hakkında')" wrapper="mb-[15px]" />

                <x-admin::form.textarea name="body" label="Mesaj" rows="5" class="h-[140px]"
                    placeholder="Merhaba, mesajınız için teşekkürler…" wrapper="mb-[15px]" />

                <p class="text-xs text-gray-500 dark:text-gray-400 mb-[12px]">
                    Yanıt <strong>{{ $lead->email }}</strong> adresine, Ayarlar → Posta bölümünde tanımlı
                    gönderici hesabı üzerinden gider. Alıcının kendi mesajı yanıtın altına eklenir.
                </p>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[22px] bg-success-500 text-white transition-all hover:bg-success-400 rounded-md border border-success-500 hover:border-success-400">
                        <i class="material-symbols-outlined !text-[18px]">send</i> Gönder
                    </button>
                </div>
            </form>
        </div>
    @endcan
</div>
