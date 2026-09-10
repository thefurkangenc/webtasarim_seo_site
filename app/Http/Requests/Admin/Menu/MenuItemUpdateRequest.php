<?php

namespace App\Http\Requests\Admin\Menu;

/**
 * Kurallar ekleme ile birebir aynı — öğe güncellemede de aynı alanlar,
 * aynı koşullu zorunluluklar geçerli. Yalnızca izin adı ortak (menu.update).
 */
class MenuItemUpdateRequest extends MenuItemStoreRequest {}
