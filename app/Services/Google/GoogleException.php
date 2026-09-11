<?php

namespace App\Services\Google;

use DomainException;

/**
 * Google API kimlik doğrulama / istek hatası. DomainException'dan türer;
 * bootstrap/app.php'deki kanca onu {success:false, message} 422'ye çevirir.
 * Mesajı kullanıcıya gösterilebilir (özel anahtar gibi sır içermez).
 */
class GoogleException extends DomainException {}
