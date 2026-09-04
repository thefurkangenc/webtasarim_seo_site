<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $service) {}

    public function preview(): View
    {
        return view('pages.maintenance', $this->service->pageData());
    }

    public function bypass(string $secret): RedirectResponse
    {
        abort_unless($this->service->validSecret($secret), 404);

        return redirect()
            ->route('anasayfa')
            ->cookie('maintenance_bypass', $this->service->cookieValue($secret), 60 * 24 * 7);
    }
}
