<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DiseaseController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $diseases = Disease::where('name', 'like', "%{$q}%")
            ->orderBy('created_at', 'desc')
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
        $categorySuggestions = Disease::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $diseases = $query->orderBy('created_at', 'desc')
            ->paginate(15)->withQueryString();

        return view('admin.master.diseases.index', compact('diseases', 'categorySuggestions'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master.diseases.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:diseases,name'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        Disease::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data penyakit berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.master.diseases.index')
            ->with('success', 'Data penyakit berhasil ditambahkan.');
    }

    public function edit(Disease $disease): RedirectResponse
    {
        return redirect()->route('admin.master.diseases.index');
    }

    public function update(Request $request, Disease $disease): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:diseases,name,' . $disease->id],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $disease->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data penyakit berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.master.diseases.index')
            ->with('success', 'Data penyakit berhasil diperbarui.');
    }

    public function destroy(Disease $disease): RedirectResponse|JsonResponse
    {
        $disease->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data penyakit berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.diseases.index')
            ->with('success', 'Data penyakit berhasil dihapus.');
    }
}
