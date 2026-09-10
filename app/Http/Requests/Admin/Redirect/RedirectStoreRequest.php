<?php

namespace App\Http\Requests\Admin\Redirect;

use App\Models\Redirect\Redirect;
use App\Services\Redirect\RedirectService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RedirectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('redirect.store');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $gone = (int) $this->input('status_code') === 410;

        return [
            'from_path' => [
                'required', 'string', 'max:191',
                Rule::unique('redirects', 'from_path')->ignore($this->route('redirect')),
            ],
            'match_type' => ['required', Rule::in(array_keys((array) config('redirects.match_types')))],
            // 410 (kalkmış) dışında hedef zorunlu.
            'to_url' => [Rule::requiredIf(! $gone), 'nullable', 'string', 'max:2000'],
            'status_code' => ['required', Rule::in(array_keys((array) config('redirects.status_codes')))],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * İki ek kontrol: regex tipinde desen geçerli mi ve bu kayıt bir
     * yönlendirme döngüsü yaratıyor mu.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('match_type') === Redirect::MATCH_REGEX) {
                    $pattern = '#'.str_replace('#', '\#', (string) $this->input('from_path')).'#iu';

                    if (@preg_match($pattern, '') === false) {
                        $validator->errors()->add('from_path', 'Geçersiz düzenli ifade.');

                        return;
                    }
                }

                $analysis = app(RedirectService::class)->analyze(
                    (string) $this->input('from_path'),
                    $this->input('to_url'),
                    $this->route('redirect')?->id,
                );

                if ($analysis['loop']) {
                    $validator->errors()->add('to_url', 'Bu hedef bir yönlendirme döngüsü oluşturur.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'from_path' => 'kaynak adres',
            'match_type' => 'eşleşme tipi',
            'to_url' => 'hedef',
            'status_code' => 'durum kodu',
            'notes' => 'not',
        ];
    }
}
