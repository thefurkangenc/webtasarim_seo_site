<p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] md:mb-[25px]">
    Google Cloud’da Maps JavaScript API’yi açın. Anahtarı HTTP yönlendiricisi ile kısıtlamanız önerilir.
</p>

<x-admin::form.input name="api_key" label="API anahtarı" required
    :value="$values['api_key'] ?? null"
    placeholder="AIza..."
    autocomplete="off"
    wrapper="mb-0" />
