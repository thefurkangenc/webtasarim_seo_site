<p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] md:mb-[25px]">
    Tawk.to panosundan Direct Chat Link veya embed kodundaki
    <span class="text-black dark:text-white">embed.tawk.to/PROPERTY/WIDGET</span>
    adresini Property ID alanına yapıştırabilirsiniz.
</p>

<x-admin::form.input name="property_id" label="Property ID" required
    :value="$values['property_id'] ?? null"
    placeholder="64abc... veya embed.tawk.to adresi" />

<x-admin::form.input name="widget_id" label="Widget ID" required
    :value="$values['widget_id'] ?? null"
    placeholder="1hxyz veya default"
    maxlength="40"
    wrapper="mb-0" />
