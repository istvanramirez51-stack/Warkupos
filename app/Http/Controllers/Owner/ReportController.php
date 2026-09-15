<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        // Validasi format sederhana
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = today()->format('Y-m-d');
        }

        $report = app(ReportService::class)->daily($date);
        $topMenus = app(ReportService::class)->topMenus($date, $date);

        return Inertia::render('Owner/Reports/Daily', [
            'report'   => $report,
            'topMenus' => $topMenus,
        ]);
    }

    public function monthly(Request $request)
    {
        $month = $request->input('month', today()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = today()->format('Y-m');
        }

        $start = $month . '-01';
        $end   = date('Y-m-t', strtotime($start));

        $report = app(ReportService::class)->monthly($month);
        $topMenus = app(ReportService::class)->topMenus($start, $end);

        return Inertia::render('Owner/Reports/Monthly', [
            'report'   => $report,
            'topMenus' => $topMenus,
        ]);
    }
}