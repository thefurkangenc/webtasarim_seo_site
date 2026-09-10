<?php

namespace App\Http\Requests\Admin\Menu;

use App\Models\Menu\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('menu.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $type = $this->input('link_type');

        return [
            // Kayda bağlı öğede etiket boşsa kaydın adı kullanılır; diğer
            // tiplerde etiket zorunlu.
            'label' => [Rule::requiredIf($type !== MenuItem::TYPE_LINKABLE), 'nullable', 'string', 'max:120'],

            'link_type' => ['required', Rule::in(array_keys(MenuItem::TYPES))],

            // Tam URL de göreli yol da (/hakkimizda) kabul edilir; 'url' kuralı yok.
            'url' => [Rule::requiredIf($type === MenuItem::TYPE_URL), 'nullable', 'string', 'max:2000'],

            'route_name' => [
                Rule::requiredIf($type === MenuItem::TYPE_ROUTE),
                'nullable',
                Rule::in(array_keys((array) config('menus.routes'))),
            ],

            'linkable_type' => [
                Rule::requiredIf($type === MenuItem::TYPE_LINKABLE),
                'nullable',
                Rule::in($this->linkableModels()),
            ],
            'linkable_id' => [Rule::requiredIf($type === MenuItem::TYPE_LINKABLE), 'nullable', 'integer'],

            'target' => ['required', Rule::in(['_self', '_blank'])],
            'status' => ['boolean'],
        ];
    }

    /**
     * linkable_id'nin verilen türde gerçekten var olduğunu doğrular —
     * (type, id) çifti tek `exists` kuralıyla ifade edilemez.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                if ($this->input('link_type') !== MenuItem::TYPE_LINKABLE) {
                    return;
                }

                if ($validator->errors()->hasAny(['linkable_type', 'linkable_id'])) {
                    return;
                }

                $model = $this->input('linkable_type');
                $exists = $model::query()->whereKey($this->input('linkable_id'))->exists();

                if (! $exists) {
                    $validator->errors()->add('linkable_id', 'Seçilen kayıt bulunamadı.');
                }
            },
        ];
    }

    /** @return array<int, string> */
    protected function linkableModels(): array
    {
        return array_column((array) config('menus.linkables'), 'model');
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'label' => 'etiket',
            'link_type' => 'bağlantı tipi',
            'route_name' => 'hazır bağlantı',
            'linkable_type' => 'kayıt türü',
            'linkable_id' => 'kayıt',
            'target' => 'açılış şekli',
        ];
    }
}
