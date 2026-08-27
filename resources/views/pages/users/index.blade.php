@extends('layouts.app')

@section('title', 'Management User')

@push('style')
<style>
    .user-avatar {
        align-items: center;
        background: linear-gradient(135deg, #4b49ac, #7978e9);
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        font-size: 13px;
        font-weight: 700;
        height: 38px;
        justify-content: center;
        margin-right: 10px;
        width: 38px;
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
        <h3 class="font-weight-bold mb-1">Management User</h3>
        <p class="text-muted mb-0">Kelola akun, role, dan status pengguna JM Academy.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary mt-3 mt-md-0">
        <i class="ti-plus mr-1"></i> Tambah User
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
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Cari nama, email, atau role...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="ti-search"></i></button>
                            @if ($search)
                                <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-7 text-md-right mt-3 mt-md-0 text-muted small">
                Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} user
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                <span>
                                    <strong>{{ $user->name }}</strong>
                                    @if (auth()->id() === $user->id)
                                        <span class="badge badge-outline-primary ml-1">Anda</span>
                                    @endif
                                    <small class="d-block text-muted ml-5">{{ $user->email }}</small>
                                </span>
                            </td>
                            <td>
                                @forelse ($user->roles as $role)
                                    <span class="badge badge-info text-capitalize">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">Tanpa role</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($user->is_active)
                                    <span class="text-success"><i class="status-dot bg-success"></i>Aktif</span>
                                @else
                                    <span class="text-danger"><i class="status-dot bg-danger"></i>Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="ti-pencil"></i>
                                </a>

                                @if (auth()->id() !== $user->id)
                                    <form class="action-form" action="{{ route('users.toggle-status', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="{{ $user->is_active ? 'ti-lock' : 'ti-unlock' }}"></i>
                                        </button>
                                    </form>

                                    <form class="action-form delete-user-form" action="{{ route('users.destroy', $user) }}" method="POST" data-name="{{ $user->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="ti-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">User tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                <span class="text-muted small mb-3 mb-md-0">
                    Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}
                </span>
                {{ $users->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendors/sweetalert/sweetalert.min.js') }}"></script>
<script>
    document.querySelectorAll('.delete-user-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            swal({
                title: 'Yakin ingin menghapus?',
                text: 'User "' + form.dataset.name + '" akan dihapus permanen dan tidak dapat dikembalikan.',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        value: null,
                        visible: true,
                        className: 'btn btn-light',
                        closeModal: true
                    },
                    confirm: {
                        text: 'Ya, hapus',
                        value: true,
                        visible: true,
                        className: 'btn btn-danger',
                        closeModal: true
                    }
                },
                dangerMode: true
            }).then(function (confirmed) {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
