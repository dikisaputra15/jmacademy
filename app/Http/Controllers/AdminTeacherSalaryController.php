<?php

namespace App\Http\Controllers;

use App\Models\TeacherPayout;
use App\Models\TeacherSalary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminTeacherSalaryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $teachers = User::role('guru')
            ->withSum(['salaries as unpaid_salary_total' => fn ($query) => $query->whereNull('teacher_payout_id')], 'amount')
            ->withCount(['salaries as unpaid_meetings_count' => fn ($query) => $query->whereNull('teacher_payout_id')])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->orderByDesc('unpaid_salary_total')->orderBy('name')
            ->paginate(10, ['*'], 'teacher_page')->withQueryString();

        $payouts = TeacherPayout::query()->with(['teacher', 'payer'])
            ->latest('transfer_date')->latest('id')
            ->paginate(10, ['*'], 'payout_page')->withQueryString();

        $totalUnpaid = TeacherSalary::whereNull('teacher_payout_id')->sum('amount');
        $totalPaid = TeacherPayout::sum('amount');
        $unpaidTeachers = TeacherSalary::whereNull('teacher_payout_id')->distinct()->count('teacher_id');

        return view('pages.admin-teacher-salaries.index', compact(
            'teachers', 'payouts', 'search', 'totalUnpaid', 'totalPaid', 'unpaidTeachers'
        ));
    }

    public function create(User $teacher): View
    {
        $this->ensureTeacher($teacher);
        $salaries = $teacher->salaries()->whereNull('teacher_payout_id')
            ->with(['student', 'course', 'schedule.lesson'])->oldest('earned_at')->get();
        abort_if($salaries->isEmpty(), 404, 'Tidak ada salary yang belum dibayar.');
        $total = $salaries->sum('amount');

        return view('pages.admin-teacher-salaries.create', compact('teacher', 'salaries', 'total'));
    }

    public function store(Request $request, User $teacher): RedirectResponse
    {
        $this->ensureTeacher($teacher);
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'transfer_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'transfer_proof.required' => 'Bukti transfer wajib diunggah.',
            'transfer_proof.mimes' => 'Bukti transfer harus berupa JPG, JPEG, PNG, atau PDF.',
            'transfer_proof.max' => 'Ukuran bukti transfer maksimal 2 MB.',
        ]);

        $proofPath = $request->file('transfer_proof')->store('teacher-payout-proofs');
        try {
            DB::transaction(function () use ($teacher, $request, $validated, $proofPath) {
                $salaries = TeacherSalary::where('teacher_id', $teacher->id)
                    ->whereNull('teacher_payout_id')->lockForUpdate()->get();
                if ($salaries->isEmpty()) {
                    throw ValidationException::withMessages(['salary' => 'Salary guru ini sudah dibayar oleh admin lain.']);
                }

                $payout = TeacherPayout::create([
                    'teacher_id' => $teacher->id,
                    'paid_by' => $request->user()->id,
                    'amount' => $salaries->sum('amount'),
                    'bank_name' => $validated['bank_name'],
                    'bank_account_number' => $validated['bank_account_number'],
                    'bank_account_holder' => $validated['bank_account_holder'],
                    'transfer_date' => $validated['transfer_date'],
                    'transfer_proof_path' => $proofPath,
                    'notes' => $validated['notes'] ?? null,
                ]);

                TeacherSalary::whereKey($salaries->modelKeys())
                    ->whereNull('teacher_payout_id')->update(['teacher_payout_id' => $payout->id]);
            });
        } catch (Throwable $exception) {
            Storage::delete($proofPath);
            throw $exception;
        }

        return to_route('admin-teacher-salaries.index')->with('success', 'Gaji guru berhasil dibayar dan bukti transfer tersimpan.');
    }

    public function proof(TeacherPayout $payout): StreamedResponse
    {
        abort_unless(Storage::exists($payout->transfer_proof_path), 404, 'Bukti transfer tidak ditemukan.');

        return Storage::response(
            $payout->transfer_proof_path,
            'bukti-gaji-PAY-'.str_pad((string) $payout->id, 6, '0', STR_PAD_LEFT).'.'.pathinfo($payout->transfer_proof_path, PATHINFO_EXTENSION),
            ['Content-Disposition' => 'inline']
        );
    }

    private function ensureTeacher(User $teacher): void
    {
        abort_unless($teacher->hasRole('guru'), 404);
    }
}
