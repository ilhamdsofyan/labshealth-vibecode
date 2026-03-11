<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\ClinicAgenda;
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

        $recentVisits = Visit::with('creator')
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->limit(5)
            ->get();

        $monthlyTrend = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('visit_date, COUNT(*) as count')
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->pluck('count', 'visit_date')
            ->toArray();

        $agendas = ClinicAgenda::query()
            ->visibleTo($request->user())
            ->whereBetween('agenda_date', [$startOfMonth, $endOfMonth])
            ->orderBy('agenda_date')
            ->orderBy('agenda_time')
            ->limit(6)
            ->get();

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

        $beds = Bed::query()->where('is_active', true)->orderBy('id')->get();
        $activeBedVisits = Visit::with(['bed', 'student', 'employee'])
            ->where('is_rest', true)
            ->where('is_acc_pulang', false)
            ->whereNotNull('bed_id')
            ->get()
            ->keyBy('bed_id');

        $sickBayCapacity = $beds->count();
        $sickBayFilled = $activeBedVisits->count();

        return view('dashboard', compact(
            'todayVisits',
            'monthVisits',
            'categoryStats',
            'recentVisits',
            'monthlyTrend',
            'agendas',
            'selectedMonth',
            'selectedYear',
            'availableYears',
            'beds',
            'activeBedVisits',
            'sickBayFilled',
            'sickBayCapacity'
        ));
    }
}
