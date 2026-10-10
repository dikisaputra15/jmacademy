@extends('layouts.app')

@section('title', 'Paid Schedules')

@push('style')
<style>
    .schedule-list-card{border:0;border-radius:12px}.schedule-table{margin-bottom:0;min-width:1000px}.schedule-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.schedule-table tbody td{border-color:#edf0f3;padding:15px 16px;vertical-align:middle}.date-box{background:#4b49ac;border-radius:8px;color:#fff;display:inline-block;min-width:58px;padding:7px;text-align:center}.date-box strong{display:block;font-size:18px;line-height:18px}.meeting-pill{border-radius:20px;font-size:11px;padding:6px 10px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div><h3 class="font-weight-bold mb-1">Paid Schedules</h3><p class="text-muted mb-0">Daftar jadwal training yang diatur melalui Student Register.</p></div>
    <span class="badge badge-success px-3 py-2">{{ $paidTransactionCount }} pendaftaran terverifikasi</span>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Jadwal belum dapat dibuat.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card schedule-list-card">
    <div class="card-body border-bottom"><div class="d-flex flex-wrap justify-content-between align-items-end"><div><h5 class="font-weight-bold mb-1">Daftar Jadwal Training</h5><small class="text-muted">Jadwal diurutkan berdasarkan tanggal terbaru.</small></div><form method="GET" action="{{ route('paid-schedules.index') }}" class="form-inline mt-3 mt-md-0"><input name="search" value="{{ $search }}" class="form-control form-control-sm mr-2" placeholder="Student, course, atau guru"><button class="btn btn-sm btn-outline-primary">Cari</button>@if($search)<a href="{{ route('paid-schedules.index') }}" class="btn btn-sm btn-light ml-1">Reset</a>@endif</form></div></div>
    @if($schedules->isNotEmpty())
        <div class="table-responsive"><table class="table schedule-table"><thead><tr><th>Tanggal</th><th>Waktu</th><th>Student</th><th>Course / Materi</th><th>Guru</th><th>Zoom</th><th>Catatan</th></tr></thead><tbody>
            @foreach($schedules as $schedule)
                <tr>
                    <td><div class="date-box"><strong>{{ $schedule->training_date->format('d') }}</strong><small>{{ strtoupper($schedule->training_date->translatedFormat('M')) }}</small></div><small class="text-muted d-block mt-1">{{ $schedule->training_date->format('Y') }}</small></td>
                    <td><strong>{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}</strong><small class="text-muted d-block">WIB</small></td>
                    <td><strong class="d-block">{{ $schedule->transaction->student->name }}</strong><small class="text-muted">{{ $schedule->transaction->student->email }}</small></td>
                    <td><strong class="d-block">{{ $schedule->transaction->course->name }}</strong>@if($schedule->lesson)<small class="text-primary d-block">{{ $schedule->lesson->section->name }} · {{ $schedule->lesson->title }} (Pertemuan {{ $schedule->meeting_number }})</small>@endif<small class="text-muted">{{ $schedule->transaction->course->category->name }} · {{ $schedule->transaction->course->code }}</small></td>
                    <td><span class="badge badge-info meeting-pill"><i class="ti-user mr-1"></i>{{ $schedule->teacher->name }}</span></td>
                    <td>@if($schedule->zoom_url && $schedule->training_date->isBefore(today()))<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Tanggal jadwal sudah lewat"><i class="ti-video-camera mr-1"></i> Buka Zoom</button>@elseif($schedule->zoom_url)<a href="{{ $schedule->zoom_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="ti-video-camera mr-1"></i> Buka Zoom</a>@else<span class="text-muted">—</span>@endif</td>
                    <td><span class="small text-muted">{{ $schedule->notes ?: '—' }}</span></td>
                </tr>
            @endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }} dari {{ $schedules->total() }} jadwal</small>@if($schedules->hasPages()){{ $schedules->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Belum ada jadwal training</h5><p class="text-muted mb-0">Atur guru dan jadwal melalui menu Student Register.</p></div>
    @endif
</div>
@endsection
