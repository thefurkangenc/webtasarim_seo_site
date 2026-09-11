<?php

namespace App\Services\SearchConsole;

use App\Services\Google\GoogleException;

/** Search Console kimlik doğrulama / istek hatası; mesajı kullanıcıya gösterilebilir. */
class SearchConsoleException extends GoogleException {}
