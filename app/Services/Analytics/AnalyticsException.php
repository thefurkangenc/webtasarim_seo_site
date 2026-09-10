<?php

namespace App\Services\Analytics;

use DomainException;

/**
 * GA4 kimlik doğrulama / rapor hatası. DomainException'dan türer; böylece
 * bootstrap/app.php'deki mevcut kanca onu {success:false, message} 422'ye çevirir.
 * Mesajı kullanıcıya gösterilebilir (private key gibi sır içermez).
 */
class AnalyticsException extends DomainException {}
