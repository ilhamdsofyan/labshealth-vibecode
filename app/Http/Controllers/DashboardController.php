<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = (int) $request->input('month', now()->month);
        $selectedYear = (int) $request->input('year', now()->year);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = now()->month;
        }

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = now()->year;
        }

        $selectedDate = now()->setYear($selectedYear)->setMonth($selectedMonth);
        $today = now()->toDateString();
        $startOfMonth = $selectedDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $selectedDate->copy()->endOfMonth()->toDateString();

        $todayVisits = Visit::where('visit_date', $today)->count();
        $monthVisits = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])->count();

        $categoryStats = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('patient_category, COUNT(*) as count')
            ->groupBy('patient_category')
            ->pluck('count', 'patient_category')
            ->toArray();

        $recentVisits = Visit::with(['creator', 'student', 'employee'])
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

        $calendarCounts = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('visit_date, COUNT(*) as count')
            ->groupBy('visit_date')
            ->pluck('count', 'visit_date')
            ->toArray();

        $clinicAgenda = Visit::with('disease')
            ->whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->limit(3)
            ->get()
            ->map(function (Visit $visit) {
                return [
                    'date' => $visit->visit_date,
                    'time' => $visit->visit_time,
                    'title' => $visit->disease?->name ?: 'Pemeriksaan Umum',
                    'subtitle' => $visit->patient_name,
                ];
            })
            ->toArray();

        $availableYears = Visit::selectRaw('YEAR(visit_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$selectedYear];
        }

        $sickBayFilled = min($todayVisits, 8);
        $sickBayCapacity = 8;

        return view('dashboard', compact(
            'todayVisits',
            'monthVisits',
            'categoryStats',
            'recentVisits',
            'monthlyTrend',
            'calendarCounts',
            'selectedMonth',
            'selectedYear',
            'availableYears',
            'sickBayFilled',
            'sickBayCapacity',
            'clinicAgenda'
        ));
    }
}
