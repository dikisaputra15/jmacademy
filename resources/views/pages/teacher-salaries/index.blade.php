@extends('layouts.app')

@section('title', 'My Salary')

@push('style')
<style>
    .salary-stat,.salary-card{border:0;border-radius:13px}.salary-icon{align-items:center;border-radius:11px;display:flex;font-size:21px;height:48px;justify-content:center;width:48px}.salary-table{margin-bottom:0;min-width:1050px}.salary-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.salary-table tbody td{border-color:#edf0f3;padding:15px 16px;vertical-align:middle}.salary-amount{color:#4b49ac;font-weight:700;white-space:nowrap}.salary-code{color:#4b49ac;font-size:11px;font-weight:700}.earned-pill{border-radius:18px;font-size:11px;padding:6px 10px}.salary-card .pagination{margin-bottom:0}
</style>
@endpush

@section('main')
<div class="mb-4"><h3 class="font-weight-bold mb-1">My Salary</h3><p class="text-muted mb-0">Penghasilan dari setiap pembelajaran yang report-nya telah Anda selesaikan.</p></div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3"><div class="card salary-stat h-100"><div class="card-body d-flex align-items-center"><span class="salary-icon bg-primary text-white mr-3"><i class="ti-wallet"></i></span><div><small class="text-muted">Total Penghasilan</small><h5 class="mb-0 font-weight-bold">Rp {{ number_format($totalSalary, 0, ',', '.') }}</h5></div></div></div></div>
    <div class="col-lg-3 col-md-6 mb-3"><div class="card salary-stat h-100"><div class="card-body d-flex align-items-center"><span class="salary-icon bg-warning text-white mr-3"><i class="ti-time"></i></span><div><small class="text-muted">Belum Dibayar</small><h5 class="mb-0 font-weight-bold">Rp {{ number_format($unpaidSalary, 0, ',', '.') }}</h5></div></div></div></div>
    <div class="col-lg-3 col-md-6 mb-3"><div class="card salary-stat h-100"><div class="card-body d-flex align-items-center"><span class="salary-icon bg-success text-white mr-3"><i class="ti-check"></i></span><div><small class="text-muted">Sudah Dibayar</small><h5 class="mb-0 font-weight-bold">Rp {{ number_format($paidSalary, 0, ',', '.') }}</h5></div></div></div></div>
    <div class="col-lg-3 col-md-6 mb-3"><div class="card salary-stat h-100"><div class="card-body d-flex align-items-center"><span class="salary-icon bg-info text-white mr-3"><i class="ti-book"></i></span><div><small class="text-muted">Pertemuan Dilaporkan</small><h5 class="mb-0 font-weight-bold">{{ number_format($totalMeetings, 0, ',', '.') }} pertemuan</h5></div></div></div></div>
</div>

<div class="card salary-card">
    <div class="card-body border-bottom"><form method="GET" action="{{ route('teacher-salaries.index') }}" class="row align-items-end"><div class="col-md-9 form-group mb-md-0"><label for="search">Cari Riwayat Salary</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama student, course, kode course, atau materi"></div><div class="col-md-3"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i>Cari</button></div></form></div>
    @if($salaries->isNotEmpty())
        <div class="table-responsive"><table class="table salary-table"><thead><tr><th>ID</th><th>Tanggal Pertemuan</th><th>Course & Materi</th><th>Student</th><th>Report Dikirim</th><th>Honor</th><th>Status</th></tr></thead><tbody>
            @foreach($salaries as $salary)<tr>
                <td><span class="salary-code">SAL-{{ str_pad($salary->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                <td><strong class="d-block text-nowrap">{{ $salary->schedule->training_date->translatedFormat('d M Y') }}</strong><small class="text-muted">{{ substr($salary->schedule->start_time, 0, 5) }}–{{ substr($salary->schedule->end_time, 0, 5) }} WIB</small></td>
                <td><strong class="d-block">{{ $salary->course->name }}</strong><small class="text-muted">{{ $salary->course->code }} · {{ $salary->schedule->lesson?->title ?? 'Materi' }} · Pertemuan {{ $salary->schedule->meeting_number ?? '-' }}</small></td>
                <td><strong class="d-block">{{ $salary->student->name }}</strong><small class="text-muted">{{ $salary->student->email }}</small></td>
                <td><span class="text-nowrap">{{ $salary->earned_at->translatedFormat('d M Y') }}</span><small class="text-muted d-block">{{ $salary->earned_at->format('H:i') }} WIB</small></td>
                <td><span class="salary-amount">Rp {{ number_format($salary->amount, 0, ',', '.') }}</span></td>
                <td>@if($salary->payout)<span class="badge badge-success earned-pill"><i class="ti-check mr-1"></i>Sudah Dibayar</span><small class="text-muted d-block mt-1">{{ $salary->payout->transfer_date->translatedFormat('d M Y') }}</small><a href="{{ route('teacher-salaries.proof',$salary->payout) }}" target="_blank" class="small">Lihat bukti transfer</a>@else<span class="badge badge-warning earned-pill"><i class="ti-time mr-1"></i>Menunggu Pembayaran</span>@endif</td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $salaries->firstItem() }}–{{ $salaries->lastItem() }} dari {{ $salaries->total() }} penghasilan</small>@if($salaries->hasPages()){{ $salaries->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-wallet h2 text-muted"></i><h5 class="mt-3">Belum ada penghasilan</h5><p class="text-muted mb-0">Honor pertemuan otomatis masuk setelah Anda mengirim report pembelajaran.</p></div>
    @endif
</div>
@endsection
