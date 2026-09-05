<?php

namespace App\Http\Controllers;

use App\Models\CourseTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', 'pending');

        if (! in_array($status, ['pending', 'paid', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $statusCounts = CourseTransaction::query()
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $transactions = CourseTransaction::query()
            ->with(['student', 'course.category', 'verifier'])
            ->when($status !== 'all', fn ($query) => $query->where('payment_status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('student', fn ($student) => $student
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('course', fn ($course) => $course
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));

                    if (ctype_digit($search)) {
                        $query->orWhereKey((int) $search);
                    }
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.student-registrations.index', compact(
            'transactions', 'statusCounts', 'status', 'search'
        ));
    }

    public function proof(CourseTransaction $transaction): StreamedResponse
    {
        abort_unless(
            $transaction->payment_proof_path
            && Storage::exists($transaction->payment_proof_path),
            404,
            'Bukti pembayaran tidak ditemukan.'
        );

        return Storage::response(
            $transaction->payment_proof_path,
            'bukti-pembayaran-TRX-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT).'.'.pathinfo($transaction->payment_proof_path, PATHINFO_EXTENSION),
            ['Content-Disposition' => 'inline']
        );
    }

    public function verify(Request $request, CourseTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in(['paid', 'rejected'])],
            'verification_note' => [
                Rule::requiredIf($request->input('payment_status') === 'rejected'),
                'nullable', 'string', 'max:1000',
            ],
        ], [
            'verification_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $updated = CourseTransaction::query()
            ->whereKey($transaction->id)
            ->where('payment_status', 'pending')
            ->update([
            'payment_status' => $validated['payment_status'],
            'verification_note' => $validated['verification_note'] ?? null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $message = $validated['payment_status'] === 'paid'
            ? 'Pembayaran berhasil diverifikasi dan student terdaftar pada course.'
            : 'Pembayaran ditolak. Alasan penolakan dapat dilihat oleh student.';

        return back()->with('success', $message);
    }
}
