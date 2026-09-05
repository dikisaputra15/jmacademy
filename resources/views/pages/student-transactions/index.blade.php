@extends('layouts.app')

@section('title', 'My Transactions')

@push('style')
<style>
    .transaction-table-card{border:0;border-radius:12px;overflow:hidden}.transaction-table{margin-bottom:0;min-width:950px}.transaction-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.transaction-table tbody td{border-color:#edf0f3;padding:16px;vertical-align:middle}.transaction-code{color:#4b49ac;font-size:12px;font-weight:700}.transaction-amount{color:#4b49ac;font-weight:700;white-space:nowrap}.status-pill{border-radius:20px;font-size:11px;font-weight:700;padding:7px 12px;white-space:nowrap}.pagination-info{color:#6c757d;font-size:13px}.transaction-table-card .pagination{margin-bottom:0}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div><h3 class="font-weight-bold mb-1">My Transactions</h3><p class="text-muted mb-0">Pantau status pembayaran kelas Anda.</p></div>
    <a href="{{ route('student-courses.index') }}" class="btn btn-primary mt-3 mt-md-0"><i class="ti-plus mr-1"></i> Order Kelas Lagi</a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

@if ($transactions->isNotEmpty())
    <div class="card transaction-table-card">
        <div class="table-responsive">
            <table class="table transaction-table">
                <thead><tr><th>No. Transaksi</th><th>Course</th><th>Data Transfer</th><th>Tanggal Order</th><th>Nominal</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        @php
                            $status = [
                                'pending' => ['Menunggu Verifikasi', 'warning', 'ti-time'],
                                'paid' => ['Pembayaran Diterima', 'success', 'ti-check'],
                                'rejected' => ['Pembayaran Ditolak', 'danger', 'ti-close'],
                            ][$transaction->payment_status] ?? [ucfirst($transaction->payment_status), 'secondary', 'ti-info-alt'];
                        @endphp
                        <tr>
                            <td><span class="transaction-code">TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                            <td><strong class="d-block">{{ $transaction->course->name }}</strong><small class="text-muted">{{ $transaction->course->category->name }} · {{ $transaction->course->code }}</small></td>
                            <td><strong class="d-block">{{ $transaction->sender_name }}</strong><small class="text-muted">{{ $transaction->sender_bank }} · {{ $transaction->transfer_date?->format('d M Y') }}</small></td>
                            <td><span class="text-nowrap">{{ $transaction->created_at->format('d M Y') }}</span><small class="text-muted d-block">{{ $transaction->created_at->format('H:i') }}</small></td>
                            <td><span class="transaction-amount">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span></td>
                            <td><span class="badge badge-{{ $status[1] }} status-pill"><i class="{{ $status[2] }} mr-1"></i>{{ $status[0] }}</span>@if($transaction->verification_note)<small class="text-danger d-block mt-2">{{ $transaction->verification_note }}</small>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
            <span class="pagination-info mb-2 mb-md-0">Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi</span>
            @if ($transactions->hasPages())
                <div>{{ $transactions->links('pagination::bootstrap-4') }}</div>
            @endif
        </div>
    </div>
@else
    <div class="card"><div class="card-body text-center py-5"><i class="ti-receipt h2 text-muted"></i><h5 class="mt-3">Belum ada transaksi</h5><p class="text-muted">Pilih kelas dan kirim bukti pembayaran untuk membuat transaksi pertama.</p><a href="{{ route('student-courses.index') }}" class="btn btn-primary">Lihat Semua Kelas</a></div></div>
@endif
@endsection
