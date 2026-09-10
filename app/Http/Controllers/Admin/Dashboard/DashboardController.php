<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analytics): View
    {
        return view('admin.pages.dashboard.index', [
            'analyticsReady' => $analytics->configured(),
        ]);
    }
}
