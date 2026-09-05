@extends('layouts.app')

@section('title', 'Histories')

@push('style')
<style>
    .history-card{border:0;border-radius:13px}.history-table{margin-bottom:0;min-width:1450px}.history-table thead th{background:#4b49ac;border:0;color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 15px;text-transform:uppercase}.history-table tbody td{border-color:#edf0f3;padding:15px;vertical-align:top}.history-number{color:#4b49ac;font-size:11px;font-weight:700}.attendance-pill,.video-pill{border-radius:18px;font-size:11px;padding:6px 10px}.summary-text{max-width:300px;white-space:normal}.history-card .pagination{margin-bottom:0}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Histories</h3><p class="text-muted mb-0">Riwayat report pembelajaran yang telah Anda kirim.</p></div><span class="badge badge-primary px-3 py-2 mt-2 mt-md-0">{{ $histories->total() }} report terkirim</span></div>

<div class="card history-card">
    <div class="card-body border-bottom"><form method="GET" action="{{ route('teacher-histories.index') }}" class="row align-items-end"><div class="col-md-9 form-group mb-md-0"><label for="search">Cari History</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Student, course, kode course, materi, hasil pembelajaran, atau catatan"></div><div class="col-md-3"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i>Cari</button></div></form></div>
    @if($histories->isNotEmpty())
        <div class="table-responsive"><table class="table history-table"><thead><tr><th>No.</th><th>Report Dikirim</th><th>Jadwal Kelas</th><th>Student</th><th>Course</th><th>Pertemuan & Materi</th><th>Kehadiran</th><th>Hasil & Catatan</th><th>Recording</th><th>Honor</th></tr></thead><tbody>
            @foreach($histories as $history)<tr>
                <td><span class="history-number">RPT-{{ str_pad($history->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                <td><strong class="d-block text-nowrap">{{ $history->submitted_at->translatedFormat('d M Y') }}</strong><small class="text-muted">{{ $history->submitted_at->format('H:i') }} WIB</small></td>
                <td><strong class="d-block text-nowrap">{{ $history->schedule->training_date->translatedFormat('d M Y') }}</strong><small class="text-muted d-block">{{ $history->schedule->training_date->translatedFormat('l') }}</small><small class="text-muted">{{ substr($history->schedule->start_time,0,5) }}–{{ substr($history->schedule->end_time,0,5) }}</small></td>
                <td><strong class="d-block">{{ $history->student->name }}</strong><small class="text-muted">{{ $history->student->email }}</small></td>
                <td><strong class="d-block">{{ $history->course->name }}</strong><small class="text-muted">{{ $history->course->category->name }} · {{ $history->course->code }}</small></td>
                <td><strong class="d-block">Pertemuan {{ $history->schedule->meeting_number ?? '-' }}</strong><small class="text-muted d-block">{{ $history->lesson?->section?->name }}</small><span>{{ $history->lesson?->title ?? 'Materi' }}</span></td>
                <td>@if($history->is_present)<span class="badge badge-success attendance-pill"><i class="ti-check mr-1"></i>Hadir</span>@else<span class="badge badge-danger attendance-pill"><i class="ti-close mr-1"></i>Tidak Hadir</span>@endif</td>
                <td><div class="summary-text">{{ $history->learning_summary }}@if($history->notes)<small class="text-muted d-block mt-2"><strong>Catatan:</strong> {{ $history->notes }}</small>@endif</div></td>
                <td>@if($history->video_url)<a href="{{ $history->video_url }}" target="_blank" rel="noopener noreferrer" class="badge badge-info video-pill"><i class="ti-video-camera mr-1"></i>{{ $history->video_title ?: 'Lihat Video' }}</a>@else<span class="text-muted">Tidak ada video</span>@endif</td>
                <td><strong class="text-primary text-nowrap">Rp {{ number_format($history->salary?->amount ?? 0, 0, ',', '.') }}</strong><small class="text-success d-block">Masuk salary</small></td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $histories->firstItem() }}–{{ $histories->lastItem() }} dari {{ $histories->total() }} history</small>@if($histories->hasPages()){{ $histories->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-time h2 text-muted"></i><h5 class="mt-3">Belum ada history report</h5><p class="text-muted mb-0">Report yang Anda kirim dari Pending Reports akan otomatis tampil di sini.</p></div>
    @endif
</div>
@endsection
