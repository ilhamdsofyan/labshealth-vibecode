<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentClassHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = StudentClassHistory::with('student');

        if ($search = $request->input('search')) {
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            })->orWhere('class_name', 'like', "%{$search}%");
        }

        $histories = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.class_histories.index', compact('histories'));
    }
}
