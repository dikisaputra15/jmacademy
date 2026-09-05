@extends('layouts.app')

@section('title', 'Course Saya')

@push('style')
<style>
    .teacher-course-card{border:1px solid #e1e6ee;border-radius:13px;overflow:hidden;transition:box-shadow .2s,transform .2s}.teacher-course-card:hover{box-shadow:0 8px 24px rgba(75,73,172,.11);transform:translateY(-2px)}.course-cover{background:linear-gradient(135deg,#4b49ac,#7978d8);color:#fff;min-height:135px;padding:22px}.course-code{background:rgba(255,255,255,.18);border-radius:20px;font-size:11px;font-weight:700;padding:5px 10px}.metric{color:#6c757d;font-size:12px}.curriculum-section{border-left:3px solid #4b49ac;margin-bottom:15px;padding-left:13px}.lesson-row{border-top:1px solid #edf0f3;display:flex;justify-content:space-between;padding:8px 0}.student-chip{background:#eef0ff;border-radius:18px;color:#4b49ac;display:inline-block;font-size:11px;margin:2px;padding:5px 9px}.inactive-course{opacity:.75}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div><h3 class="font-weight-bold mb-1">Course Saya</h3><p class="text-muted mb-0">Course dan kurikulum yang ditugaskan kepada Anda oleh admin.</p></div>
    <span class="badge badge-primary px-3 py-2">{{ $courses->total() }} course ditugaskan</span>
</div>

<div class="card border-0 mb-4"><div class="card-body"><form method="GET" action="{{ route('teacher-courses.index') }}" class="row align-items-end"><div class="col-md-6 form-group mb-md-0"><label for="search">Cari Course</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama, kode, atau deskripsi course"></div><div class="col-md-4 form-group mb-md-0"><label for="category">Category</label><select id="category" name="category" class="form-control"><option value="">Semua category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i> Cari</button></div></form></div></div>

<div class="row">
    @forelse($courses as $course)
        @php
            $meetingTotal = $course->sections->sum(fn($section) => $section->lessons->sum('meetings'));
            $lessonTotal = $course->sections->sum(fn($section) => $section->lessons->count());
            $students = $course->transactions->pluck('student')->filter()->unique('id');
            $curriculumId = 'teacher-course-'.$course->id;
        @endphp
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card teacher-course-card h-100 {{ $course->is_active ? '' : 'inactive-course' }}">
                <div class="course-cover"><div class="d-flex justify-content-between align-items-start mb-3"><span class="course-code">{{ $course->code }}</span><span class="badge {{ $course->is_active ? 'badge-light' : 'badge-secondary' }}">{{ $course->is_active ? 'AKTIF' : 'NONAKTIF' }}</span></div><h4 class="mb-1">{{ $course->name }}</h4><small>{{ $course->category->name }}@if($course->age_min || $course->age_max) · Usia {{ $course->age_min ?? '?' }}–{{ $course->age_max ?? '?' }} tahun @endif</small></div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted">{{ $course->description ?: 'Deskripsi course belum tersedia.' }}</p>
                    <div class="d-flex justify-content-between border-top border-bottom py-3 mb-3"><span class="metric"><i class="ti-layout-list-thumb mr-1"></i>{{ $course->sections->count() }} section</span><span class="metric"><i class="ti-book mr-1"></i>{{ $lessonTotal }} materi</span><span class="metric"><i class="ti-time mr-1"></i>{{ $meetingTotal }} pertemuan</span></div>
                    <div class="mb-3"><small class="font-weight-bold d-block mb-1">Student terverifikasi ({{ $students->count() }})</small>@forelse($students as $student)<span class="student-chip"><i class="ti-user mr-1"></i>{{ $student->name }}</span>@empty<span class="text-muted small">Belum ada student.</span>@endforelse</div>
                    <a class="btn btn-outline-primary mt-auto" data-toggle="collapse" href="#{{ $curriculumId }}" role="button" aria-expanded="false" aria-controls="{{ $curriculumId }}"><i class="ti-list mr-1"></i> Lihat Kurikulum</a>
                </div>
                <div class="collapse" id="{{ $curriculumId }}"><div class="card-body border-top">
                    @forelse($course->sections as $section)
                        <div class="curriculum-section"><div class="d-flex justify-content-between mb-2"><strong>{{ $loop->iteration }}. {{ $section->name }}</strong><small class="text-muted">{{ $section->lessons->sum('meetings') }} pertemuan</small></div>@forelse($section->lessons as $lesson)<div class="lesson-row"><span><span class="badge badge-light mr-1">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</span>{{ $lesson->title }}</span><small class="text-muted ml-2">{{ $lesson->meetings }}×</small></div>@empty<small class="text-muted">Belum ada materi.</small>@endforelse</div>
                    @empty<div class="text-muted text-center py-3">Kurikulum belum tersedia.</div>@endforelse
                </div></div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card border-0"><div class="card-body text-center py-5"><i class="ti-book h2 text-muted"></i><h5 class="mt-3">Belum ada course</h5><p class="text-muted mb-0">Course akan muncul setelah admin menugaskannya kepada Anda.</p></div></div></div>
    @endforelse
</div>

@if($courses->hasPages())<div class="d-flex justify-content-center mt-3">{{ $courses->links('pagination::bootstrap-4') }}</div>@endif
@endsection
