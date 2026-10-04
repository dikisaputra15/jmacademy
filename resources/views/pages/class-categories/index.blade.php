@extends('layouts.app')
@section('title', 'Category Kelas')
@section('main')
<h3 class="font-weight-bold mb-2">Category Kelas</h3>
<p class="text-muted mb-4">Atur biaya per pertemuan. Perubahan tarif otomatis diterapkan pada materi course yang memakai kategori kelas ini. Nominal transaksi yang sudah dibuat tetap tersimpan.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row">
@foreach($classCategories as $classCategory)
<div class="col-md-4 mb-4"><div class="card h-100"><div class="card-body">
    <h4>{{ $classCategory->name }}</h4>
    <p class="text-muted">{{ $classCategory->capacity }} siswa per kelas</p>
    <form method="POST" action="{{ route('class-categories.update', $classCategory) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="fee-{{ $classCategory->id }}">Biaya per pertemuan (Rp)</label>
            <input id="fee-{{ $classCategory->id }}" type="number" name="fee_per_meeting" min="0" max="999999999" step="1" class="form-control" value="{{ $classCategory->fee_per_meeting }}" placeholder="Belum ditentukan" required>
        </div>
        <button class="btn btn-primary">Simpan Biaya</button>
    </form>
</div></div></div>
@endforeach
</div>
@endsection
