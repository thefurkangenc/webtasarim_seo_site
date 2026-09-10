<?php

namespace App\Services\Redirect;

use App\Support\UrlPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * RedirectResolver'ın çözdüğü eşleşme. HTTP yanıtına çevirmeyi kendi bilir —
 * 410 için Location'sız bir "Gone", diğer kodlar için normal yönlendirme.
 */
class RedirectResult
{
    public function __construct(
        public readonly string $target,
        public readonly int $statusCode,
        public readonly int $redirectId,
    ) {}

    public function toResponse(): RedirectResponse|Response
    {
        if ($this->statusCode === 410) {
            return response('', 410);
        }

        // Dış URL'de open-redirect'e karşı `away`; iç yolda göreli Location
        // (tarayıcı geçerli host'a göre çözer — canlıda/yerelde aynı çalışır).
        return UrlPath::isExternal($this->target)
            ? redirect()->away($this->target, $this->statusCode)
            : redirect('/'.ltrim($this->target, '/'), $this->statusCode);
    }
}
