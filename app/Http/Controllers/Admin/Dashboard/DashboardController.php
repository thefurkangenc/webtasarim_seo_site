<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Services\Health\HealthService;
use App\Services\Lead\LeadService;
use App\Services\Seo\SeoHealthService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        AnalyticsService $analytics,
        SeoHealthService $seo,
        LeadService $leads,
        HealthService $health,
    ): View {
        $leadStats = $leads->stats();

        return view('admin.pages.dashboard.index', [
            'analyticsReady' => $analytics->configured(),
            'seoOverview' => $seo->overview(),
            'leadUnread' => $leadStats['unread'],
            'leadToday' => $leadStats['today'],
            // Yalnızca cache'teki rapor; dashboard uğruna kontrol çalıştırılmaz.
            'healthReport' => $health->cached(),
        ]);
    }
}
