@extends('layouts.app')

@section('title', 'Report Study')

@push('style')
<style>
    .report-study-card{border:0;border-radius:13px}.report-study-table{margin-bottom:0;min-width:1050px}.report-study-table thead th{background:#4b49ac;border:0;color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.report-study-table tbody td{border-color:#edf0f3;padding:16px;vertical-align:middle}.grade-badge{align-items:center;background:#ecebff;border-radius:50%;color:#4b49ac;display:flex;font-size:20px;font-weight:700;height:46px;justify-content:center;width:46px}.report-study-card .pagination{margin-bottom:0}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Report Study</h3><p class="text-muted mb-0">Laporan akhir pembelajaran dan sertifikat course Anda.</p></div><span class="badge badge-primary px-3 py-2 mt-2 mt-md-0">{{ $reports->total() }} laporan diterima</span></div>

<div class="card report-study-card">
    <div class="card-body border-bottom"><form method="GET" action="{{ route('student-reports.index') }}" class="row align-items-end"><div class="col-md-9 form-group mb-md-0"><label for="search">Cari Report</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Course, kode course, guru, atau predikat"></div><div class="col-md-3"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i>Cari</button></div></form></div>
    @if($reports->isNotEmpty())
        <div class="table-responsive"><table class="table report-study-table"><thead><tr><th>Predikat</th><th>Course</th><th>Guru</th><th>Kompetensi</th><th>Tanggal Diterima</th><th>Aksi</th></tr></thead><tbody>
            @foreach($reports as $report)<tr>
                <td><span class="grade-badge">{{ $report->grade }}</span></td>
                <td><strong class="d-block">{{ $report->course->name }}</strong><small class="text-muted">{{ $report->course->category->name }} · {{ $report->course->code }}</small></td>
                <td><strong class="d-block">{{ $report->teacher->name }}</strong><small class="text-muted">{{ $report->teacher->email }}</small></td>
                <td>@php($skills = collect([['Pemahaman',$report->understanding],['Logika',$report->logic],['Kreativitas',$report->creativity]])->filter(fn($skill) => $skill[1]))@forelse($skills as $skill)<span class="badge badge-success mr-1 mb-1">{{ $skill[0] }}</span>@empty<span class="text-muted">Belum ditandai</span>@endforelse</td>
                <td><strong class="d-block text-nowrap">{{ $report->submitted_at->translatedFormat('d M Y') }}</strong><small class="text-muted">{{ $report->submitted_at->format('H:i') }} WIB</small></td>
                <td class="text-nowrap"><a href="{{ route('student-reports.show', $report) }}" class="btn btn-sm btn-outline-primary mr-1"><i class="ti-eye mr-1"></i>Lihat Report</a><a href="{{ route('student-reports.certificate', $report) }}" class="btn btn-sm btn-primary"><i class="ti-medall mr-1"></i>Lihat Sertifikat</a></td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $reports->firstItem() }}–{{ $reports->lastItem() }} dari {{ $reports->total() }} laporan</small>@if($reports->hasPages()){{ $reports->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-clipboard h2 text-muted"></i><h5 class="mt-3">Belum ada Report Study</h5><p class="text-muted mb-0">Report dan sertifikat akan tersedia setelah guru menyelesaikan Parent Report.</p></div>
    @endif
</div>
@endsection
