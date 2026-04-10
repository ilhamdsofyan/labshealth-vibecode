<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $medications = Medication::where('name', 'like', "%{$q}%")                                                  
            ->limit(10)
            ->get();

        return response()->json($medications->map(function ($m) {
            return [
                'id' => $m->id,
                'text' => $m->name,
            ];
        }));
    }

    public function index(Request $request): View
    {
        $query = Medication::query()->with('stock');
        $categorySuggestions = Medication::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        }

        $medications = $query->orderBy('created_at', 'desc')
                        ->paginate(15)->withQueryString();

        return view('admin.master.medications.index', compact('medications', 'categorySuggestions'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master.medications.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:medications,name'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        Medication::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data obat berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.master.medications.index')
            ->with('success', 'Data obat berhasil ditambahkan.');
    }

    public function edit(Medication $medication): RedirectResponse
    {
        return redirect()->route('admin.master.medications.index');
    }

    public function show(Medication $medication): RedirectResponse
    {
        return redirect()->route('admin.master.medications.index');
    }

    public function update(Request $request, Medication $medication): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:medications,name,' . $medication->id],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $medication->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data obat berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.master.medications.index')
            ->with('success', 'Data obat berhasil diperbarui.');
    }

    public function restock(Request $request, Medication $medication): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'action_type' => ['required', 'in:in,out,adjust'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'min_threshold' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $stock = $medication->stock()->firstOrCreate(
            [],
            ['quantity' => 0, 'unit' => $validated['unit'], 'min_threshold' => $validated['min_threshold']]
        );

        $oldQuantity = $stock->quantity;
        $changeQuantity = $validated['quantity'];
        $actualChangeType = $validated['action_type'];
        
        if ($validated['action_type'] === 'in') {
            $stock->quantity += $changeQuantity;
            $stock->last_restocked_at = now();
        } elseif ($validated['action_type'] === 'out') {
            $stock->quantity -= $changeQuantity;
        } elseif ($validated['action_type'] === 'adjust') {
            if ($validated['quantity'] > $oldQuantity) {
                $actualChangeType = 'in';
                $changeQuantity = $validated['quantity'] - $oldQuantity;
            } elseif ($validated['quantity'] < $oldQuantity) {
                $actualChangeType = 'out';
                $changeQuantity = $oldQuantity - $validated['quantity'];
            } else {
                $actualChangeType = 'adjust';
                $changeQuantity = 0;
            }
            $stock->quantity = $validated['quantity'];
        }

        $stock->unit = $validated['unit'];
        $stock->min_threshold = $validated['min_threshold'];
        $stock->save();

        if ($changeQuantity > 0 || $actualChangeType === 'adjust') {
            $medication->stockLogs()->create([
                'change_type' => $actualChangeType,
                'quantity' => $changeQuantity,
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Stok obat berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.master.medications.index')
            ->with('success', 'Stok obat berhasil diperbarui.');
    }

    public function destroy(Medication $medication): RedirectResponse|JsonResponse
    {
        $medication->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data obat berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.medications.index')
            ->with('success', 'Data obat berhasil dihapus.');
    }
}
