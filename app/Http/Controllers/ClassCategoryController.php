<?php

namespace App\Http\Controllers;

use App\Models\ClassCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ClassCategoryController extends Controller
{
    public function index(): View
    {
        return view('pages.class-categories.index', [
            'classCategories' => ClassCategory::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ClassCategory::create($this->validatedData($request));

        return to_route('class-categories.index')->with('success', 'Kategori kelas berhasil ditambahkan.');
    }

    public function update(Request $request, ClassCategory $classCategory): RedirectResponse
    {
        $classCategory->update($this->validatedData($request, $classCategory));

        return to_route('class-categories.index')->with('success', 'Kategori kelas berhasil diperbarui.');
    }

    public function destroy(ClassCategory $classCategory): RedirectResponse
    {
        if ($classCategory->courses()->exists() || $classCategory->transactions()->exists()) {
            return to_route('class-categories.index')->withErrors([
                'category' => 'Kategori kelas tidak dapat dihapus karena sudah digunakan pada course atau transaksi.',
            ]);
        }

        $classCategory->delete();

        return to_route('class-categories.index')->with('success', 'Kategori kelas berhasil dihapus.');
    }

    private function validatedData(Request $request, ?ClassCategory $classCategory = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('class_categories', 'name')->ignore($classCategory)],
            'capacity' => ['required', 'integer', 'min:1', 'max:255'],
            'fee_per_meeting' => ['required', 'integer', 'min:0', 'max:999999999'],
        ]);
    }
}
