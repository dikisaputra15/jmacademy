@extends('layouts.app')

@section('title', 'Kelola Curriculum')

@push('style')
    <style>
        .section-card {
            border: 1px solid #dce4eb;
            border-left: 4px solid #4b49ac;
            border-radius: 8px;
            margin-bottom: 18px;
            overflow: hidden;
        }

        .section-header {
            background: #eef2f6;
            padding: 15px;
        }

        .lesson-item {
            align-items: center;
            border-top: 1px solid #edf0f3;
            display: flex;
            justify-content: space-between;
            padding: 11px 15px;
        }

        .inline-delete {
            display: inline-block;
        }

        .add-lesson {
            background: #fafbfd;
            border-top: 1px solid #edf0f3;
            padding: 14px;
        }

        .section-form {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr 100px auto;
        }

        .lesson-form {
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(240px, 1fr) 110px minmax(180px, 220px) 90px auto;
        }

        @media (max-width: 991px) {
            .lesson-form {
                grid-template-columns: 1fr 110px minmax(180px, 1fr);
            }
        }

        @media (max-width: 767px) {
            .section-form,
            .lesson-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('main')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a
                href="{{ route('courses.index', ['category' => $course->course_category_id]) }}"
                class="btn btn-sm btn-light mr-3"
            >
                <i class="ti-arrow-left"></i>
            </a>

            <div>
                <h3 class="font-weight-bold mb-1">Curriculum: {{ $course->name }}</h3>
                <p class="text-muted mb-0">
                    {{ $course->category->name }} ·
                    {{ $course->code }} ·
                    {{ $course->sections->sum(fn ($section) => $section->lessons->sum('meetings')) }} pertemuan
                </p>
            </div>
        </div>

        <a href="{{ route('courses.edit', $course) }}" class="btn btn-outline-primary mt-3 mt-md-0">
            <i class="ti-pencil mr-1"></i>
            Edit Course
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Tambah Section</h5>

            <form
                class="section-form"
                method="POST"
                action="{{ route('curriculum.sections.store', $course) }}"
            >
                @csrf

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    placeholder="Nama section, contoh: Basic Programming"
                    required
                >

                <input
                    type="number"
                    name="sort_order"
                    min="0"
                    value="{{ $course->sections->count() + 1 }}"
                    class="form-control"
                    title="Urutan"
                    required
                >

                <button class="btn btn-primary">
                    <i class="ti-plus mr-1"></i>
                    Section
                </button>
            </form>
        </div>
    </div>

    @forelse ($course->sections as $section)
        <div class="section-card">
            <div class="section-header">
                <form
                    class="section-form"
                    method="POST"
                    action="{{ route('curriculum.sections.update', $section) }}"
                >
                    @csrf
                    @method('PUT')

                    <input
                        type="text"
                        name="name"
                        value="{{ $section->name }}"
                        class="form-control font-weight-bold"
                        required
                    >

                    <input
                        type="number"
                        name="sort_order"
                        min="0"
                        value="{{ $section->sort_order }}"
                        class="form-control"
                        required
                    >

                    <div>
                        <button class="btn btn-sm btn-outline-primary" title="Simpan section">
                            <i class="ti-save"></i>
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-trigger"
                            data-form="delete-section-{{ $section->id }}"
                            data-message="Section {{ $section->name }} beserta lesson-nya akan dihapus."
                        >
                            <i class="ti-trash"></i>
                        </button>
                    </div>
                </form>

                <form
                    id="delete-section-{{ $section->id }}"
                    method="POST"
                    action="{{ route('curriculum.sections.destroy', $section) }}"
                >
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            @forelse ($section->lessons as $lesson)
                <div class="lesson-item">
                    <span>
                        <span class="badge badge-light mr-2">
                            {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                        </span>
                        {{ $lesson->title }}
                    </span>

                    <span class="text-nowrap">
                        <span class="text-muted small mr-3">
                            {{ $lesson->meetings }} pertemuan ·
                            Rp {{ number_format($lesson->fee_per_meeting, 0, ',', '.') }}/pertemuan
                        </span>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-trigger"
                            data-form="delete-lesson-{{ $lesson->id }}"
                            data-message="Lesson {{ $lesson->title }} akan dihapus."
                        >
                            <i class="ti-trash"></i>
                        </button>

                        <form
                            id="delete-lesson-{{ $lesson->id }}"
                            class="inline-delete"
                            method="POST"
                            action="{{ route('curriculum.lessons.destroy', $lesson) }}"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    </span>
                </div>
            @empty
                <div class="lesson-item text-muted">
                    Belum ada lesson pada section ini.
                </div>
            @endforelse

            <div class="add-lesson">
                <form
                    class="lesson-form"
                    method="POST"
                    action="{{ route('curriculum.lessons.store', $section) }}"
                >
                    @csrf

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="Judul lesson"
                        required
                    >

                    <input
                        type="number"
                        name="meetings"
                        min="1"
                        max="999"
                        value="1"
                        class="form-control"
                        title="Jumlah pertemuan"
                        aria-label="Jumlah pertemuan"
                        required
                    >

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>

                        <input
                            type="number"
                            name="fee_per_meeting"
                            min="0"
                            max="999999999"
                            step="1"
                            value="{{ old('fee_per_meeting') }}"
                            class="form-control"
                            placeholder="Biaya/pertemuan"
                            title="Biaya setiap pertemuan"
                            aria-label="Biaya setiap pertemuan"
                            required
                        >
                    </div>

                    <input
                        type="number"
                        name="sort_order"
                        min="0"
                        value="{{ $section->lessons->count() + 1 }}"
                        class="form-control"
                        title="Urutan"
                        required
                    >

                    <button class="btn btn-outline-primary">
                        <i class="ti-plus mr-1"></i>
                        Lesson
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                Belum ada section. Tambahkan section pertama untuk mulai menyusun curriculum.
            </div>
        </div>
    @endforelse
@endsection

@push('scripts')
    <script src="{{ asset('vendors/sweetalert/sweetalert.min.js') }}"></script>
    <script>
        document.querySelectorAll('.delete-trigger').forEach(function (button) {
            button.addEventListener('click', function () {
                swal({
                    title: 'Yakin ingin menghapus?',
                    text: button.dataset.message,
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: 'Batal',
                            value: null,
                            visible: true,
                            className: 'btn btn-light'
                        },
                        confirm: {
                            text: 'Ya, hapus',
                            value: true,
                            visible: true,
                            className: 'btn btn-danger'
                        }
                    },
                    dangerMode: true
                }).then(function (confirmed) {
                    if (confirmed) {
                        document.getElementById(button.dataset.form).submit();
                    }
                });
            });
        });
    </script>
@endpush
