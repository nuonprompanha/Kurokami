<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Throwable;

class VisitorAnalyticsController extends Controller
{
    public function __invoke(Request $request, GoogleAnalyticsService $analytics)
    {
        $days = (int) $request->integer('days', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $report = null;
        $analyticsError = null;

        if ($analytics->configured()) {
            try {
                $report = $analytics->report($days);
            } catch (Throwable $exception) {
                report($exception);
                $analyticsError = 'Google Analytics data could not be loaded. Check the property ID, API access, and service-account permissions.';
            }
        }

        return view('admin.visitors.index', compact('days', 'report', 'analyticsError'));
    }
}
