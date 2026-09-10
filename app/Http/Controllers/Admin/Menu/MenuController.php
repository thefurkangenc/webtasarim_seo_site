<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\MenuItemStoreRequest;
use App\Http\Requests\Admin\Menu\MenuItemUpdateRequest;
use App\Http\Requests\Admin\Menu\MenuTreeRequest;
use App\Http\Requests\Admin\Menu\MenuUpdateRequest;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use App\Services\Menu\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly MenuService $service) {}

    /** Konum verilmezse ilk menüye yönlendirir. */
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.menu.edit', Menu::orderBy('id')->firstOrFail());
    }

    public function edit(Menu $menu): View
    {
        return view('admin.pages.menu.index', ['workspace' => $this->service->workspace($menu)]);
    }

    /** İşlem sonrası ağacı tazelemek için — JS tam sayfa yenilemez. */
    public function tree(Menu $menu): JsonResponse
    {
        return $this->success(data: $this->service->tree($menu));
    }

    public function updateMenu(MenuUpdateRequest $request, Menu $menu): JsonResponse
    {
        $menu->update($request->validated());

        return $this->success('Menü başlığı güncellendi.', $menu->toPayload());
    }

    public function storeItem(MenuItemStoreRequest $request, Menu $menu): JsonResponse
    {
        $item = $this->service->createItem($menu, $request->validated());

        return $this->success('Menü öğesi eklendi.', $item->toPayload());
    }

    public function updateItem(MenuItemUpdateRequest $request, MenuItem $menuItem): JsonResponse
    {
        return $this->success('Menü öğesi güncellendi.', $this->service->updateItem($menuItem, $request->validated())->toPayload());
    }

    public function destroyItem(MenuItem $menuItem): JsonResponse
    {
        $this->service->deleteItem($menuItem);

        return $this->success('Menü öğesi silindi.');
    }

    public function saveTree(MenuTreeRequest $request, Menu $menu): JsonResponse
    {
        $this->service->saveTree($menu, $request->validated('nodes'));

        return $this->success('Menü sıralaması kaydedildi.', $this->service->tree($menu));
    }
}
