@extends('layouts.app')
@section('title', 'Kategori Kelas')
@section('main')
<h3 class="font-weight-bold mb-2">Kategori Kelas</h3>
<p class="text-muted mb-4">Kelola nama, kapasitas siswa, dan biaya kategori kelas. Tarif berlaku untuk order baru; nominal transaksi sebelumnya tetap tersimpan.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card mb-4"><div class="card-body">
    <h4 class="mb-3">Tambah Kategori Kelas</h4>
    <form method="POST" action="{{ route('class-categories.store') }}">
        @csrf
        <input type="hidden" name="form_category" value="new">
        @include('pages.class-categories._fields', ['category' => null, 'formKey' => 'new'])
        <button class="btn btn-primary">Tambah Kategori</button>
    </form>
</div></div>
<div class="row">
@forelse($classCategories as $classCategory)
<div class="col-md-6 col-xl-4 mb-4"><div class="card h-100"><div class="card-body">
    <h4 class="mb-3">{{ $classCategory->name }}</h4>
    <form method="POST" action="{{ route('class-categories.update', $classCategory) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_category" value="{{ $classCategory->id }}">
        @include('pages.class-categories._fields', ['category' => $classCategory, 'formKey' => (string) $classCategory->id])
        <button class="btn btn-primary">Simpan Perubahan</button>
    </form>
    <form class="mt-3" method="POST" action="{{ route('class-categories.destroy', $classCategory) }}" onsubmit="return confirm('Hapus kategori kelas ini?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Hapus</button>
    </form>
</div></div></div>
@empty
<div class="col-12"><div class="alert alert-info">Belum ada kategori kelas. Tambahkan kategori pertama melalui form di atas.</div></div>
@endforelse
</div>
@endsection
