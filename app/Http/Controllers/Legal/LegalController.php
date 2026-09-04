<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function cookie(): View
    {
        return view('pages.legal.show', [
            'title' => 'Çerez Politikası',
            'content' => Settings::get('contents.cookie_content'),
        ]);
    }

    public function kvkk(): View
    {
        return view('pages.legal.show', [
            'title' => 'KVKK Aydınlatma Metni',
            'content' => Settings::get('contents.kvkk_content'),
        ]);
    }
}
