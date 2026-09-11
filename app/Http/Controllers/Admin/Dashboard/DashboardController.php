<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Services\Dashboard\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboard, AnalyticsService $analytics): View
    {
        return view('admin.pages.dashboard.index', [
            // GA4 ağa çıkar; sayfa onu beklemez, yalnızca bağlı mı diye sorar.
            'analyticsReady' => $analytics->configured(),
            'alerts' => $dashboard->alerts(),
            'counters' => $dashboard->counters(),
            'content' => $dashboard->content(),
            'media' => $dashboard->media(),
            'recentLeads' => $dashboard->recentLeads(),
            'activity' => $dashboard->recentActivity(),
            'seoWeakest' => $dashboard->seoWeakest(),
            'production' => $dashboard->productionTrend(),
            'leadStatuses' => $dashboard->leadStatuses(),
        ]);
    }
}
