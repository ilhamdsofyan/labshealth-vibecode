<?php

namespace App\Http\Controllers;

use App\Models\ClinicAgenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicAgendaController extends Controller
{
    public function index(Request $request): View
    {
        $canChooseVisibility = $request->user()?->isSuperAdmin() || $request->user()?->hasRole('admin');

        $query = ClinicAgenda::query()
            ->visibleTo($request->user())
            ->orderByDesc('agenda_date')
            ->orderByDesc('agenda_time');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $agendas = $query->paginate(15)->withQueryString();

        return view('agendas.index', compact('agendas', 'canChooseVisibility'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('agendas.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'agenda_date' => ['required', 'date'],
            'agenda_time' => ['nullable', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $validated['created_by'] = auth()->id();
        $canChooseVisibility = auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin');
        $validated['is_public'] = $canChooseVisibility
            ? (bool) ($request->boolean('is_public'))
            : false;

        ClinicAgenda::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Agenda klinik berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('agendas.index')->with('success', 'Agenda klinik berhasil ditambahkan.');
    }
}
