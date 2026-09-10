{{-- AJAX modal gövdesi. Gönderim pages/redirect/index.js tarafından devralınır. --}}
<form id="redirect-form" data-id="{{ $redirect?->id }}">
    {{-- 404 kaydından "Yönlendir" ile açıldıysa o kaydı çözüldü işaretlemek için. --}}
    <input type="hidden" name="not_found_id" value="{{ request('not_found') }}">

    <x-admin::form.input name="from_path" help="redirect.from_path" label="Kaynak Adres" required
        :value="$redirect?->from_path ?? request('from')"
        placeholder="Örn. /eski-sayfa" />

    <x-admin::form.select name="match_type" help="redirect.match_type" label="Eşleşme Tipi" required
        :options="$matchTypes"
        :value="$redirect?->match_type ?? 'exact'" :placeholder="null" />

    <p class="-mt-[12px] mb-[20px] md:mb-[25px] text-xs text-gray-500 dark:text-gray-400" data-match-hint></p>

    <x-admin::form.input name="to_url" help="redirect.to_url" label="Hedef"
        :value="$redirect?->to_url"
        placeholder="/yeni-sayfa ya da https://..." />

    <x-admin::form.select name="status_code" help="redirect.status_code" label="Durum Kodu" required
        :options="$statusCodes"
        :value="$redirect?->status_code ?? 301" :placeholder="null" />

    {{-- Canlı zincir/döngü uyarısı — pages/redirect/index.js doldurur. --}}
    <div data-chain-warning class="hidden mb-[20px] md:mb-[25px] p-[12px] rounded-md text-xs border"></div>

    <x-admin::form.textarea name="notes" help="redirect.notes" label="Not (opsiyonel)" rows="2" :value="$redirect?->notes"
        placeholder="Bu yönlendirme neden var?" class="h-[70px]" />

    <x-admin::form.switch name="is_active" help="common.active" label="Aktif" :checked="$redirect?->is_active ?? true" />

    <x-admin::form.actions :submit="$redirect ? 'Güncelle' : 'Ekle'" />
</form>
