<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.upload');
    }

    /** Kırpım verisi FormData içinde JSON metni olarak gelir. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('crop'))) {
            $this->merge(['crop' => json_decode($this->input('crop'), true)]);
        }
    }

    /**
     * Buradaki üst sınır yalnızca kaba bir elektir: gerçek sınır uzantıya göre
     * değişir (video daha büyük olabilir) ve MediaService::guard() içinde tek
     * yerde uygulanır. Burada en gevşek değer kullanılır, yoksa izin verilen
     * bir video doğrulamada daha erken reddedilirdi.
     */
    private function sizeLimit(): int
    {
        return max(
            (int) config('media.max_size'),
            ...array_map('intval', array_values(config('media.max_size_by_extension', []))),
        );
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:'.$this->sizeLimit()],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'preset' => ['nullable', 'string', 'max:100'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],

            'crop' => ['nullable', 'array'],
            'crop.x' => ['required_with:crop', 'numeric'],
            'crop.y' => ['required_with:crop', 'numeric'],
            'crop.width' => ['required_with:crop', 'numeric', 'min:1'],
            'crop.height' => ['required_with:crop', 'numeric', 'min:1'],
            'crop.rotate' => ['nullable', 'numeric'],
            'crop.scaleX' => ['nullable', 'numeric'],
            'crop.scaleY' => ['nullable', 'numeric'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.required' => 'Yüklenecek dosyayı seçin.',
            'file.max' => 'Dosya boyutu en fazla '.round($this->sizeLimit() / 1024, 1).' MB olabilir.',
            'folder_id.exists' => 'Seçilen klasör bulunamadı.',
        ];
    }
}
