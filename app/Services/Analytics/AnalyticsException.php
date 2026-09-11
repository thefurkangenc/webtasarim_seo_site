<?php

namespace App\Services\Analytics;

use App\Services\Google\GoogleException;

/**
 * GA4 kimlik doğrulama / rapor hatası. Paylaşılan GoogleException'dan türer,
 * o da DomainException'dan — bootstrap/app.php'deki kanca onu
 * {success:false, message} 422'ye çevirir. Mesajı kullanıcıya gösterilebilir.
 */
class AnalyticsException extends GoogleException {}
