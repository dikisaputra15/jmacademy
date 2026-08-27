@extends('layouts.app')

@section('title', 'Category Course')

@push('style')
<style>
    .category-icon {
        align-items: center;
        background: linear-gradient(135deg, #4b49ac, #7978e9);
        border-radius: 12px;
        color: #fff;
        display: inline-flex;
        height: 40px;
        justify-content: center;
        margin-right: 10px;
        width: 40px;
    }

    .status-dot {
        border-radius: 50%;
        display: inline-block;
        height: 8px;
        margin-right: 6px;
        width: 8px;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .action-form {
        display: inline-block;
    }
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h3 class="font-weight-bold mb-1">Category Course</h3>
        <p class="text-muted mb-0">Kelola pengelompokan program dan kelas JM Academy.</p>
    </div>
    <a href="{{ route('course-categories.create') }}" class="btn btn-primary mt-3 mt-md-0">
        <i class="ti-plus mr-1"></i> Tambah Category
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span>&times;</span></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span>&times;</span></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="row align-items-center mb-4">
            <div class="col-md-5">
                <form action="{{ route('course-categories.index') }}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Cari nama atau deskripsi...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="ti-search"></i></button>
                            @if ($search)
                                <a class="btn btn-outline-secondary" href="{{ route('course-categories.index') }}">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-7 text-md-right mt-3 mt-md-0 text-muted small">
                Menampilkan {{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} category
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="text-nowrap">
                                <span class="category-icon"><i class="ti-folder"></i></span>
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td class="text-muted">{{ str($category->description ?: '-')->limit(60) }}</td>
                            <td class="text-nowrap">
                                @if ($category->is_active)
                                    <span class="text-success"><i class="status-dot bg-success"></i>Aktif</span>
                                @else
                                    <span class="text-danger"><i class="status-dot bg-danger"></i>Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('course-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="ti-pencil"></i>
                                </a>
                                <form class="action-form" action="{{ route('course-categories.toggle-status', $category) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" type="submit" title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="{{ $category->is_active ? 'ti-lock' : 'ti-unlock' }}"></i>
                                    </button>
                                </form>
                                <form class="action-form delete-category-form" action="{{ route('course-categories.destroy', $category) }}" method="POST" data-name="{{ $category->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="ti-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Category course tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                <span class="text-muted small mb-3 mb-md-0">Halaman {{ $categories->currentPage() }} dari {{ $categories->lastPage() }}</span>
                {{ $categories->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendors/sweetalert/sweetalert.min.js') }}"></script>
<script>
    document.querySelectorAll('.delete-category-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            swal({
                title: 'Yakin ingin menghapus?',
                text: 'Category "' + form.dataset.name + '" akan dihapus permanen.',
                icon: 'warning',
                buttons: {
                    cancel: { text: 'Batal', value: null, visible: true, className: 'btn btn-light', closeModal: true },
                    confirm: { text: 'Ya, hapus', value: true, visible: true, className: 'btn btn-danger', closeModal: true }
                },
                dangerMode: true
            }).then(function (confirmed) {
                if (confirmed) form.submit();
            });
        });
    });
</script>
@endpush
