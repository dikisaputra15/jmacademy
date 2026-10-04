<?php

namespace App\Http\Controllers;

use App\Models\ClassCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassCategoryController extends Controller
{
    public function index(): View
    {
        return view('pages.class-categories.index', [
            'classCategories' => ClassCategory::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, ClassCategory $classCategory): RedirectResponse
    {
        $validated = $request->validate([
            'fee_per_meeting' => ['required', 'integer', 'min:0', 'max:999999999'],
        ]);

        $classCategory->update($validated);

        return back()->with('success', 'Biaya kategori kelas berhasil diperbarui.');
    }
}
