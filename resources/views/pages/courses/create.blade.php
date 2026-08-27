@extends('layouts.app')
@section('title', 'Tambah Course')
@section('main')
<div class="row justify-content-center"><div class="col-lg-10"><div class="d-flex align-items-center mb-4"><a href="{{ route('courses.index') }}" class="btn btn-sm btn-light mr-3"><i class="ti-arrow-left"></i></a><div><h3 class="font-weight-bold mb-1">Tambah Course</h3><p class="text-muted mb-0">Buat course baru, kemudian susun curriculum-nya.</p></div></div><div class="card"><div class="card-body"><form method="POST" action="{{ route('courses.store') }}">@csrf @include('pages.courses._form')</form></div></div></div></div>
@endsection
