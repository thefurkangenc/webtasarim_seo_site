<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Services\Seo\SeoHealthService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analytics, SeoHealthService $seo): View
    {
        return view('admin.pages.dashboard.index', [
            'analyticsReady' => $analytics->configured(),
            'seoOverview' => $seo->overview(),
        ]);
    }
}
