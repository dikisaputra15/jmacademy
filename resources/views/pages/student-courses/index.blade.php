@extends('layouts.app')

@section('title', 'All Courses')

@push('style')
<style>
    .course-card { border: 1px solid #e3e8ef; border-radius: 12px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
    .course-card:hover { box-shadow: 0 8px 24px rgba(75, 73, 172, .12); transform: translateY(-2px); }
    .course-top { background: linear-gradient(135deg, #4b49ac, #7978d8); color: #fff; min-height: 126px; padding: 22px; }
    .course-code { background: rgba(255,255,255,.18); border-radius: 20px; display: inline-block; font-size: 11px; font-weight: 700; padding: 5px 10px; }
    .course-meta { color: #6c757d; font-size: 12px; }
    .curriculum-toggle { color: #4b49ac; font-weight: 600; text-decoration: none !important; }
    .curriculum-section { border-left: 3px solid #4b49ac; margin-bottom: 15px; padding-left: 14px; }
    .lesson-item { align-items: center; border-top: 1px solid #edf0f3; display: flex; justify-content: space-between; padding: 9px 0; }
    .filter-card { border: 0; border-radius: 12px; }
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h3 class="font-weight-bold mb-1">All Courses</h3>
        <p class="text-muted mb-0">Jelajahi course yang tersedia beserta kurikulumnya.</p>
    </div>
    <span class="badge badge-primary px-3 py-2">{{ $courses->total() }} course tersedia</span>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('student-courses.index') }}" class="row align-items-end">
            <div class="col-md-6 form-group mb-md-0">
                <label for="search">Cari Course</label>
                <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Nama, kode, atau deskripsi course">
            </div>
            <div class="col-md-4 form-group mb-md-0">
                <label for="category">Kategori</label>
                <select id="category" name="category" class="form-control">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-block"><i class="ti-search mr-1"></i> Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    @forelse ($courses as $course)
        @php
            $lessonTotal = $course->sections->sum(fn ($section) => $section->lessons->count());
            $meetingTotal = $course->sections->sum(fn ($section) => $section->lessons->sum('meetings'));
            $curriculumId = 'curriculum-'.$course->id;
        @endphp
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card course-card h-100">
                <div class="course-top">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="course-code">{{ $course->code }}</span>
                        <span class="badge badge-light">{{ $course->category->name }}</span>
                    </div>
                    <h4 class="mb-1">{{ $course->name }}</h4>
                    @if ($course->age_min || $course->age_max)
                        <small>Usia {{ $course->age_min ?? '?' }}–{{ $course->age_max ?? '?' }} tahun</small>
                    @endif
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted">{{ $course->description ?: 'Deskripsi course belum tersedia.' }}</p>
                    <div class="course-meta d-flex justify-content-between border-top border-bottom py-3 mb-3">
                        <span><i class="ti-layout-list-thumb mr-1"></i> {{ $course->sections->count() }} section</span>
                        <span><i class="ti-book mr-1"></i> {{ $lessonTotal }} lesson</span>
                        <span><i class="ti-time mr-1"></i> {{ $meetingTotal }} pertemuan</span>
                    </div>
                    <div class="small mb-3">
                        <strong>Guru:</strong>
                        @forelse ($course->teachers as $teacher)
                            <span class="badge badge-info ml-1">{{ $teacher->name }}</span>
                        @empty
                            <span class="text-muted">Belum ditentukan</span>
                        @endforelse
                    </div>
                    <a class="curriculum-toggle mt-auto" data-toggle="collapse" href="#{{ $curriculumId }}" role="button" aria-expanded="false" aria-controls="{{ $curriculumId }}">
                        <i class="ti-list mr-1"></i> Lihat Kurikulum <i class="ti-angle-down float-right"></i>
                    </a>
                </div>
                <div class="collapse" id="{{ $curriculumId }}">
                    <div class="card-body border-top pt-4">
                        @forelse ($course->sections as $section)
                            <div class="curriculum-section">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ $loop->iteration }}. {{ $section->name }}</strong>
                                    <small class="text-muted">{{ $section->lessons->sum('meetings') }} pertemuan</small>
                                </div>
                                @forelse ($section->lessons as $lesson)
                                    <div class="lesson-item">
                                        <span><span class="badge badge-light mr-1">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</span> {{ $lesson->title }}</span>
                                        <small class="text-muted ml-2">{{ $lesson->meetings }}×</small>
                                    </div>
                                @empty
                                    <small class="text-muted">Belum ada lesson.</small>
                                @endforelse
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">Kurikulum belum tersedia.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card"><div class="card-body text-center py-5">
                <i class="ti-book h2 text-muted"></i>
                <h5 class="mt-3">Course tidak ditemukan</h5>
                <p class="text-muted">Belum ada course aktif atau coba ubah filter pencarian.</p>
                @if ($search || $categoryId)<a href="{{ route('student-courses.index') }}" class="btn btn-outline-primary">Reset Filter</a>@endif
            </div></div>
        </div>
    @endforelse
</div>

@if ($courses->hasPages())
    <div class="d-flex justify-content-center mt-3">{{ $courses->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
