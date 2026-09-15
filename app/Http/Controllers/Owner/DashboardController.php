<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Table;
use App\Models\Setting;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data dari database
        $totalMenus = Menu::count();
        $activeMenus = Menu::where('is_available', true)->count();
        $totalTables = Table::count();
        $availableTables = Table::where('status', 'available')->count();
        
        // Ambil setting warung (default '0' jika belum ada)
        $warungName = Setting::where('key', 'warung_name')->first()?->value ?? 'WarkuPos';
        $isOpen = Setting::where('key', 'is_open')->first()?->value === '1';

        return Inertia::render('Owner/Dashboard', [
            'stats' => [
                'totalMenus' => $totalMenus,
                'activeMenus' => $activeMenus,
                'totalTables' => $totalTables,
                'availableTables' => $availableTables,
            ],
            'warung' => [
                'name' => $warungName,
                'is_open' => $isOpen,
            ]
        ]);
    }
}