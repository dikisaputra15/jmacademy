@extends('layouts.app')

@section('title', 'Tambah User')

@section('main')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light mr-3"><i class="ti-arrow-left"></i></a>
            <div><h3 class="font-weight-bold mb-1">Tambah User</h3><p class="text-muted mb-0">Buat akun pengguna baru dan tentukan hak aksesnya.</p></div>
        </div>
        <div class="card"><div class="card-body"><form action="{{ route('users.store') }}" method="POST">@csrf @include('pages.users._form')</form></div></div>
    </div>
</div>
@endsection
