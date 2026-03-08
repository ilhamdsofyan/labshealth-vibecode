<?php

namespace App\Http\Controllers;

use App\Models\ClinicAgenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicAgendaController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClinicAgenda::query()->orderByDesc('agenda_date')->orderByDesc('agenda_time');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $agendas = $query->paginate(15)->withQueryString();

        return view('agendas.index', compact('agendas'));
    }

    public function create(): View
    {
        return view('agendas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agenda_date' => ['required', 'date'],
            'agenda_time' => ['nullable', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = auth()->id();

        ClinicAgenda::create($validated);

        return redirect()->route('agendas.index')->with('success', 'Agenda klinik berhasil ditambahkan.');
    }
}
