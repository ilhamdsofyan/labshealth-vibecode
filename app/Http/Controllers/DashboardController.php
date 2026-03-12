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
        $now = now();
        $selectedMonth = (int) $request->input('month', $now->month);
        $selectedYear = (int) $request->input('year', $now->year);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = $now->month;
        }

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = $now->year;
        }

        $selectedDate = $now->copy()->setYear($selectedYear)->setMonth($selectedMonth);
        $today = $now->toDateString();
        $startOfMonth = $selectedDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $selectedDate->copy()->endOfMonth()->toDateString();

        $todayVisits = Visit::where('visit_date', $today)->count();

        $categoryStats = Visit::whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('patient_category, COUNT(*) as count')
            ->groupBy('patient_category')
            ->pluck('count', 'patient_category')
            ->toArray();

        $recentVisits = Visit::query()
            ->select(['id', 'visit_date', 'visit_time', 'patient_name', 'patient_category'])
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->limit(5)
            ->get();

        $agendas = ClinicAgenda::query()
            ->select(['id', 'agenda_date', 'agenda_time', 'title', 'location', 'is_public', 'created_by'])
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

        $beds = Bed::query()
            ->select(['id', 'code', 'name'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $activeBedVisits = Visit::query()
            ->select(['id', 'bed_id', 'patient_name'])
            ->where('is_rest', true)
            ->where('is_acc_pulang', false)
            ->whereNotNull('bed_id')
            ->get()
            ->keyBy('bed_id');

        $sickBayCapacity = $beds->count();
        $sickBayFilled = $activeBedVisits->count();

        return view('dashboard', compact(
            'todayVisits',
            'categoryStats',
            'recentVisits',
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
