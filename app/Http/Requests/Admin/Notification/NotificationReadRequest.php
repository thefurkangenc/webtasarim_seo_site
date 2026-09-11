<?php

namespace App\Http\Requests\Admin\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Türetilmiş bildirimin kararlı anahtarı ("lead:42", "health:ssl:critical").
            'key' => ['required', 'string', 'max:191'],
        ];
    }
}
