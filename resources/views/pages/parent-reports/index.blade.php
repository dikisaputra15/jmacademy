@extends('layouts.app')

@section('title', 'Parent Reports')

@push('style')
<style>
    .parent-card{border:0;border-radius:13px}.parent-table{margin-bottom:0;min-width:950px}.parent-table thead th{background:#4b49ac;border:0;color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.parent-table tbody td{border-color:#edf0f3;padding:16px;vertical-align:middle}.ready-pill,.done-pill{border-radius:18px;font-size:11px;padding:6px 10px}.parent-card .pagination{margin-bottom:0}
</style>
@endpush

@section('main')
<div class="mb-4"><h3 class="font-weight-bold mb-1">Parent Reports</h3><p class="text-muted mb-0">Buat laporan akhir setelah seluruh pertemuan pada kelas selesai dilaporkan.</p></div>
@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card parent-card">
    <div class="card-body border-bottom"><form method="GET" action="{{ route('parent-reports.index') }}" class="row align-items-end"><div class="col-md-9 form-group mb-md-0"><label for="search">Cari Kelas</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama student, email, course, atau kode course"></div><div class="col-md-3"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i>Cari</button></div></form></div>
    @if($classes->isNotEmpty())
        <div class="table-responsive"><table class="table parent-table"><thead><tr><th>No.</th><th>Nama Siswa</th><th>Kelas / Pelajaran</th><th>Report Pertemuan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
            @foreach($classes as $class)<tr>
                <td>{{ $classes->firstItem() + $loop->index }}</td>
                <td><strong class="d-block">{{ $class->student->name }}</strong><small class="text-muted">{{ $class->student->email }}</small></td>
                <td><strong class="d-block">{{ $class->course->name }}</strong><small class="text-muted">{{ $class->course->category->name }} · {{ $class->course->code }}</small></td>
                <td><strong>{{ $class->paid_schedules_count }} dari {{ $class->paid_schedules_count }}</strong><small class="text-muted d-block">Semua pertemuan sudah dilaporkan</small></td>
                <td>@if($class->parentReport)<span class="badge badge-success done-pill">Selesai</span>@else<span class="badge badge-info ready-pill">Bisa Buat Laporan</span>@endif</td>
                <td>@if($class->parentReport)<span class="text-success"><i class="ti-check mr-1"></i>Terkirim {{ $class->parentReport->submitted_at->translatedFormat('d M Y') }}</span>@else<a href="{{ route('parent-reports.create', $class) }}" class="btn btn-sm btn-primary"><i class="ti-plus mr-1"></i>Buat Report</a>@endif</td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $classes->firstItem() }}–{{ $classes->lastItem() }} dari {{ $classes->total() }} kelas</small>@if($classes->hasPages()){{ $classes->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-clipboard h2 text-muted"></i><h5 class="mt-3">Belum ada kelas siap dilaporkan</h5><p class="text-muted mb-0">Kelas akan muncul setelah seluruh report pertemuan selesai diisi.</p></div>
    @endif
</div>
@endsection
