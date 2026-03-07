<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $todayVisits = Visit::where('visit_date', $today)->count();
        $monthVisits = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])->count();

        $categoryStats = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('patient_category, COUNT(*) as count')
            ->groupBy('patient_category')
            ->pluck('count', 'patient_category')
            ->toArray();

        $recentVisits = Visit::with('creator')
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->limit(10)
            ->get();

        $monthlyTrend = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('visit_date, COUNT(*) as count')
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->pluck('count', 'visit_date')
            ->toArray();

        return view('dashboard', compact(
            'todayVisits',
            'monthVisits',
            'categoryStats',
            'recentVisits',
            'monthlyTrend'
        ));
    }
}
