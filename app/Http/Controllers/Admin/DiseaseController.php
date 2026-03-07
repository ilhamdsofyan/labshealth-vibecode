<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DiseaseController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $diseases = Disease::where('name', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        return response()->json($diseases->map(function ($d) {
            return [
                'id' => $d->id,
                'text' => $d->name,
            ];
        }));
    }

    public function index(Request $request): View
    {
        $query = Disease::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $diseases = $query->paginate(15)->withQueryString();

        return view('admin.master.diseases.index', compact('diseases'));
    }

    public function create(): View
    {
        return view('admin.master.diseases.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:diseases,name'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        Disease::create($validated);

        return redirect()->route('admin.master.diseases.index')
            ->with('success', 'Data penyakit berhasil ditambahkan.');
    }

    public function edit(Disease $disease): View
    {
        return view('admin.master.diseases.edit', compact('disease'));
    }

    public function update(Request $request, Disease $disease): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:diseases,name,' . $disease->id],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $disease->update($validated);

        return redirect()->route('admin.master.diseases.index')
            ->with('success', 'Data penyakit berhasil diperbarui.');
    }

    public function destroy(Disease $disease): RedirectResponse
    {
        $disease->delete();
        return redirect()->route('admin.master.diseases.index')
            ->with('success', 'Data penyakit berhasil dihapus.');
    }
}
