@extends('layouts.app')

@section('title', 'Pending Reports')

@push('style')
<style>
    .report-card{border:0;border-radius:13px}.report-table{margin-bottom:0;min-width:950px}.report-table thead th{background:#4b49ac;border:0;color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.report-table tbody td{border-color:#edf0f3;padding:15px 16px;vertical-align:middle}.student-chip{background:#e9f8fb;border-radius:16px;color:#1593a9;display:inline-block;font-size:11px;font-weight:700;padding:5px 9px}.pending-pill{border-radius:18px;font-size:11px;padding:6px 10px}.date-label{font-weight:600;white-space:nowrap}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Pending Reports</h3><p class="text-muted mb-0">Pembelajaran yang sudah selesai dan masih menunggu report Anda.</p></div><div class="d-flex align-items-center mt-2 mt-md-0"><span class="badge badge-warning px-3 py-2 mr-2">{{ $schedules->total() }} report tertunda</span><a href="{{ route('pending-reports.select') }}" class="btn btn-primary"><i class="ti-plus mr-1"></i>Buat Report</a></div></div>
@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card report-card">
    <div class="card-body border-bottom"><form method="GET" action="{{ route('pending-reports.index') }}" class="row align-items-end"><div class="col-md-9 form-group mb-md-0"><label for="search">Cari Report</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama student, course, kode course, atau materi"></div><div class="col-md-3"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i>Cari</button></div></form></div>
    @if($schedules->isNotEmpty())
        <div class="table-responsive"><table class="table report-table"><thead><tr><th>Course</th><th>Tanggal</th><th>Student</th><th>Materi Pembelajaran</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
            @foreach($schedules as $schedule)<tr>
                <td><strong class="d-block">{{ $schedule->transaction->course->name }}</strong><small class="text-muted">{{ $schedule->transaction->course->code }}</small></td>
                <td><span class="date-label">{{ $schedule->training_date->translatedFormat('d M Y') }}</span><small class="text-muted d-block">{{ substr($schedule->start_time,0,5) }}–{{ substr($schedule->end_time,0,5) }} WIB</small></td>
                <td><span class="student-chip">{{ $schedule->transaction->student->name }}</span><small class="text-muted d-block mt-1">{{ $schedule->transaction->student->email }}</small></td>
                <td><strong class="d-block">{{ $schedule->lesson?->title ?? 'Materi belum ditentukan' }}</strong><small class="text-primary">{{ $schedule->lesson?->section?->name ?? 'Kurikulum' }} · Pertemuan {{ $schedule->meeting_number ?? '-' }}</small></td>
                <td><span class="badge badge-warning pending-pill">Pending</span></td>
                <td><small class="text-muted">{{ $schedule->created_at->translatedFormat('d M Y H:i') }}</small></td>
                <td><a href="{{ route('pending-reports.create',$schedule) }}" class="btn btn-sm btn-outline-primary"><i class="ti-plus mr-1"></i>Isi Report</a></td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }} dari {{ $schedules->total() }} report</small>@if($schedules->hasPages()){{ $schedules->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-check-box h2 text-success"></i><h5 class="mt-3">Tidak ada pending report</h5><p class="text-muted mb-0">Semua pembelajaran yang sudah selesai telah memiliki report.</p></div>
    @endif
</div>
@endsection
