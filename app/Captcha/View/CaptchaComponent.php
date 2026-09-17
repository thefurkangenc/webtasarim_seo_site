<?php

namespace App\Captcha\View;

use App\Captcha\CaptchaManager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * <x-captcha form="contact" />
 *
 * Kendi css/js'ini de getirir; sayfanın layout'una hiçbir şey eklemek
 * gerekmez. Doğrulama kapalıysa bileşen hiçbir şey basmaz.
 */
class CaptchaComponent extends Component
{
    public function __construct(
        private readonly CaptchaManager $manager,
        public ?string $form = null,
        public string $name = CaptchaManager::FIELD,
        public string $label = 'Ben robot değilim',
    ) {}

    public function render(): View|string
    {
        return $this->manager->enabledFor($this->form)
            ? view('captcha::widget', ['driverView' => $this->manager->driver()->view()])
            : '';
    }
}
