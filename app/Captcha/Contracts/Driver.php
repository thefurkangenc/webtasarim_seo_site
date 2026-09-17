<?php

namespace App\Captcha\Contracts;

use App\Captcha\Support\Challenge;

interface Driver
{
    /** config'teki sürücü anahtarı ("puzzle" gibi). */
    public function key(): string;

    /** Bileşenin basacağı blade adı ("captcha::drivers.puzzle" gibi). */
    public function view(): string;

    /** Yeni bir bulmaca üretir. */
    public function challenge(): Challenge;

    /**
     * Tarayıcıdan gelen cevabı jetondaki sırla karşılaştırır.
     *
     * @param  array<string, mixed>  $answer
     * @param  array<string, mixed>  $secret
     */
    public function solved(array $answer, array $secret): bool;

    /** Tarayıcıdan gelen cevap için doğrulama kuralları. */
    public function rules(): array;
}
