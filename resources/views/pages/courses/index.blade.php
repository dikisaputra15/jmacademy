@extends('layouts.app')
@section('title', 'Roadmap Curriculum')
@push('style')
<style>
    .category-tabs{display:flex;gap:8px;overflow:auto;padding-bottom:6px}.category-tab{border:1px solid #dbe3ea;border-radius:8px;color:#4b6280;padding:10px 18px;white-space:nowrap}.category-tab.active{background:#4b49ac;border-color:#4b49ac;color:#fff}.roadmap-card{border:1px solid #dce4eb;border-left:4px solid #4b49ac;border-radius:9px;margin-bottom:18px;overflow:hidden}.roadmap-head{align-items:center;background:#eef2f6;display:flex;justify-content:space-between;padding:16px}.roadmap-body{padding:12px 18px}.section-block{border-left:2px solid #ccd6df;margin:6px 0 15px;padding-left:14px}.lesson-row{align-items:center;border-top:1px solid #edf0f3;display:flex;justify-content:space-between;padding:9px 12px}.course-actions form{display:inline-block}.status-pill{border-radius:20px;font-size:10px;font-weight:700;padding:5px 9px}.empty-roadmap{border:2px dashed #dce4eb;border-radius:12px;padding:55px;text-align:center}
</style>
@endpush
@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Roadmap Curriculum</h3><p class="text-muted mb-0">Susun alur belajar berdasarkan category, course, section, dan lesson.</p></div><a href="{{ route('courses.create') }}" class="btn btn-primary mt-3 mt-md-0"><i class="ti-plus mr-1"></i> Tambah Course</a></div>
@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
<div class="card"><div class="card-body">
    <div class="category-tabs mb-4">@foreach($categories as $category)<a href="{{ route('courses.index', ['category' => $category->id]) }}" class="category-tab {{ optional($selectedCategory)->id === $category->id ? 'active' : '' }}">{{ $category->name }}</a>@endforeach</div>
    @if($selectedCategory)<div class="d-flex justify-content-between border-bottom pb-3 mb-3"><span><strong>{{ $selectedCategory->name }}</strong> &nbsp; <span class="text-muted">{{ $courses->total() }} course · {{ $courses->sum(fn($course) => $course->sections->sum(fn($section) => $section->lessons->sum('meetings'))) }} pertemuan pada halaman ini</span></span><a href="{{ route('course-categories.index') }}" class="btn btn-sm btn-outline-secondary">Kelola Category</a></div>@endif
    @forelse($courses as $course)
        @php($meetingTotal = $course->sections->sum(fn($section) => $section->lessons->sum('meetings')))
        <div class="roadmap-card"><div class="roadmap-head"><div><small class="text-muted font-weight-bold">COURSE · {{ $course->code }}</small><h5 class="mb-0 mt-1">{{ $course->name }} @if($course->age_min || $course->age_max)<small class="text-muted">({{ $course->age_min ?? '?' }}–{{ $course->age_max ?? '?' }} tahun)</small>@endif</h5></div><div class="text-right"><span class="status-pill {{ $course->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $course->is_active ? 'AKTIF' : 'NONAKTIF' }}</span><div class="mt-2 text-muted small">{{ $meetingTotal }} pertemuan</div></div></div>
            <div class="roadmap-body">
                @forelse($course->sections as $section)<div class="section-block"><div class="d-flex justify-content-between py-2"><strong><small class="text-muted">SECTION {{ $loop->iteration }}</small> &nbsp; {{ $section->name }}</strong><span class="text-muted small">{{ $section->lessons->sum('meetings') }} pertemuan</span></div>@forelse($section->lessons as $lesson)<div class="lesson-row"><span><span class="badge badge-light mr-2">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</span>{{ $lesson->title }}</span><span class="text-muted small">{{ $lesson->meetings }} pertemuan</span></div>@empty<div class="text-muted small py-2">Belum ada lesson.</div>@endforelse</div>@empty<div class="text-muted py-3">Curriculum belum disusun.</div>@endforelse
                <div class="d-flex flex-wrap align-items-center justify-content-between border-top pt-3"><div class="small"><strong class="text-muted mr-2">Guru:</strong>@forelse($course->teachers as $teacher)<span class="badge badge-info mr-1">{{ $teacher->name }}</span>@empty<span class="text-muted">Belum ditentukan</span>@endforelse</div><div class="course-actions text-right mt-2 mt-md-0"><a href="{{ route('courses.edit', $course) }}#teachers" class="btn btn-sm btn-outline-info"><i class="ti-user mr-1"></i> Kelola Guru</a><a href="{{ route('courses.curriculum', $course) }}" class="btn btn-sm btn-primary"><i class="ti-list mr-1"></i> Kelola Curriculum</a><a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-primary"><i class="ti-pencil"></i></a><form action="{{ route('courses.toggle-status', $course) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning" title="Ubah status"><i class="ti-power-off"></i></button></form><form class="delete-form" action="{{ route('courses.destroy', $course) }}" method="POST" data-name="{{ $course->name }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti-trash"></i></button></form></div></div>
            </div></div>
    @empty<div class="empty-roadmap"><i class="ti-map-alt h2 text-muted"></i><h5 class="mt-3">Belum ada course</h5><p class="text-muted">Tambahkan course pada category ini untuk mulai menyusun roadmap.</p><a href="{{ route('courses.create') }}" class="btn btn-primary">Tambah Course</a></div>@endforelse

    @if($courses->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
            <span class="text-muted small mb-3 mb-md-0">
                Menampilkan {{ $courses->firstItem() }}–{{ $courses->lastItem() }} dari {{ $courses->total() }} course
            </span>
            {{ $courses->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div></div>
@endsection
@push('scripts')
<script src="{{ asset('vendors/sweetalert/sweetalert.min.js') }}"></script><script>document.querySelectorAll('.delete-form').forEach(function(form){form.addEventListener('submit',function(event){event.preventDefault();swal({title:'Hapus course?',text:'Course "'+form.dataset.name+'" beserta seluruh curriculum akan dihapus permanen.',icon:'warning',buttons:{cancel:{text:'Batal',value:null,visible:true,className:'btn btn-light'},confirm:{text:'Ya, hapus',value:true,visible:true,className:'btn btn-danger'}},dangerMode:true}).then(function(ok){if(ok)form.submit()})})});</script>
@endpush
