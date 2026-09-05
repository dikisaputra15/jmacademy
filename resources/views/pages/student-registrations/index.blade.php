@extends('layouts.app')

@section('title', 'Student Register')

@push('style')
<style>
    .stat-card{border:0;border-radius:12px}.stat-icon{align-items:center;border-radius:10px;display:flex;font-size:20px;height:44px;justify-content:center;width:44px}.register-card{border:0;border-radius:12px;overflow:hidden}.register-table{margin-bottom:0;min-width:1180px}.register-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.register-table tbody td{border-color:#edf0f3;padding:16px;vertical-align:middle}.trx-code{color:#4b49ac;font-size:12px;font-weight:700}.amount{color:#4b49ac;font-weight:700;white-space:nowrap}.status-pill{border-radius:20px;font-size:11px;font-weight:700;padding:7px 11px;white-space:nowrap}.action-form{display:inline-block}.reject-panel{background:#fff6f6;border:1px solid #f3cccc;border-radius:8px;margin-top:10px;padding:10px;width:290px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div><h3 class="font-weight-bold mb-1">Student Register</h3><p class="text-muted mb-0">Verifikasi pembayaran student sebelum terdaftar pada course.</p></div>
    <span class="badge badge-warning px-3 py-2">{{ (int) ($statusCounts['pending'] ?? 0) }} menunggu verifikasi</span>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger"><strong>Verifikasi belum dapat disimpan.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row mb-4">
    @foreach ([
        ['pending', 'Menunggu', 'warning', 'ti-time'],
        ['paid', 'Diterima', 'success', 'ti-check'],
        ['rejected', 'Ditolak', 'danger', 'ti-close'],
    ] as [$key, $label, $color, $icon])
        <div class="col-md-4 mb-3 mb-md-0">
            <a href="{{ route('student-registrations.index', ['status' => $key]) }}" class="text-decoration-none text-dark">
                <div class="card stat-card {{ $status === $key ? 'border border-'.$color : '' }}"><div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-{{ $color }} text-white mr-3"><i class="{{ $icon }}"></i></div>
                    <div><div class="h4 mb-0">{{ (int) ($statusCounts[$key] ?? 0) }}</div><small class="text-muted">{{ $label }}</small></div>
                </div></div>
            </a>
        </div>
    @endforeach
</div>

<div class="card register-card">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('student-registrations.index') }}" class="row align-items-end">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-md-8 form-group mb-md-0"><label for="search">Cari transaksi</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama/email student, course, kode course, atau nomor transaksi"></div>
            <div class="col-md-2"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i> Cari</button></div>
            <div class="col-md-2"><a href="{{ route('student-registrations.index', ['status' => $status]) }}" class="btn btn-light btn-block">Reset</a></div>
        </form>
        <div class="mt-3">
            @foreach (['pending' => 'Menunggu', 'paid' => 'Diterima', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $key => $label)
                <a href="{{ route('student-registrations.index', ['status' => $key, 'search' => $search ?: null]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-secondary' }} mr-1">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if ($transactions->isNotEmpty())
        <div class="table-responsive">
            <table class="table register-table">
                <thead><tr><th>Transaksi</th><th>Student</th><th>Course</th><th>Data Transfer</th><th>Nominal</th><th>Bukti</th><th>Status / Verifikator</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach ($transactions as $transaction)
                    @php
                        $statusInfo = [
                            'pending' => ['Menunggu', 'warning', 'ti-time'],
                            'paid' => ['Diterima', 'success', 'ti-check'],
                            'rejected' => ['Ditolak', 'danger', 'ti-close'],
                        ][$transaction->payment_status] ?? [ucfirst($transaction->payment_status), 'secondary', 'ti-info-alt'];
                    @endphp
                    <tr>
                        <td><span class="trx-code">TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</span><small class="text-muted d-block">{{ $transaction->created_at->format('d M Y, H:i') }}</small></td>
                        <td><strong class="d-block">{{ $transaction->student->name }}</strong><small class="text-muted">{{ $transaction->student->email }}</small><small class="text-muted d-block">{{ $transaction->student->phone ?: 'No. HP belum diisi' }}</small></td>
                        <td><strong class="d-block">{{ $transaction->course->name }}</strong><small class="text-muted">{{ $transaction->course->category->name }} · {{ $transaction->course->code }}</small></td>
                        <td><strong class="d-block">{{ $transaction->sender_name }}</strong><small class="text-muted">{{ $transaction->sender_bank }}</small><small class="text-muted d-block">Transfer {{ $transaction->transfer_date?->format('d M Y') }}</small></td>
                        <td><span class="amount">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span></td>
                        <td><a href="{{ route('student-registrations.proof', $transaction) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info"><i class="ti-image mr-1"></i> Lihat Bukti</a></td>
                        <td><span class="badge badge-{{ $statusInfo[1] }} status-pill"><i class="{{ $statusInfo[2] }} mr-1"></i>{{ $statusInfo[0] }}</span>@if($transaction->verifier)<small class="text-muted d-block mt-2">{{ $transaction->verifier->name }}<br>{{ $transaction->verified_at?->format('d M Y, H:i') }}</small>@endif @if($transaction->verification_note)<small class="text-danger d-block mt-1">{{ $transaction->verification_note }}</small>@endif</td>
                        <td>
                            @if ($transaction->payment_status === 'pending')
                                <form method="POST" action="{{ route('student-registrations.verify', $transaction) }}" class="action-form approve-form">@csrf @method('PATCH')<input type="hidden" name="payment_status" value="paid"><button class="btn btn-sm btn-success"><i class="ti-check mr-1"></i> Verifikasi</button></form>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="collapse" data-target="#reject-{{ $transaction->id }}"><i class="ti-close mr-1"></i> Tolak</button>
                                <div class="collapse" id="reject-{{ $transaction->id }}"><form method="POST" action="{{ route('student-registrations.verify', $transaction) }}" class="reject-panel">@csrf @method('PATCH')<input type="hidden" name="payment_status" value="rejected"><label class="small font-weight-bold" for="note-{{ $transaction->id }}">Alasan penolakan</label><textarea id="note-{{ $transaction->id }}" name="verification_note" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" required></textarea><button class="btn btn-sm btn-danger btn-block">Konfirmasi Penolakan</button></form></div>
                            @else
                                <span class="text-muted small">Sudah diproses</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-3"><span class="text-muted small">Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi</span>@if($transactions->hasPages())<div>{{ $transactions->links('pagination::bootstrap-4') }}</div>@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-receipt h2 text-muted"></i><h5 class="mt-3">Tidak ada transaksi</h5><p class="text-muted mb-0">Tidak ada pembayaran yang sesuai dengan filter saat ini.</p></div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendors/sweetalert/sweetalert.min.js') }}"></script>
<script>
document.querySelectorAll('.approve-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        swal({title:'Verifikasi pembayaran?',text:'Student akan dinyatakan terdaftar pada course ini.',icon:'warning',buttons:{cancel:{text:'Batal',value:null,visible:true,className:'btn btn-light'},confirm:{text:'Ya, verifikasi',value:true,visible:true,className:'btn btn-success'}}}).then(function(ok){if(ok) form.submit();});
    });
});
</script>
@endpush
