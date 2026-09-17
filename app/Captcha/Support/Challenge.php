<?php

namespace App\Captcha\Support;

/**
 * Bir sürücünün ürettiği bulmaca.
 *
 * `payload` tarayıcıya gider (görseller, ölçüler), `secret` ise sunucuda
 * cache'te tutulur ve tarayıcıya HİÇ gönderilmez — doğru cevap oradadır.
 */
final class Challenge
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $secret
     */
    public function __construct(
        public readonly array $payload,
        public readonly array $secret,
    ) {}
}
