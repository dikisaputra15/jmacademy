@extends('layouts.app')

@section('title', 'Tambah Category Course')

@section('main')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('course-categories.index') }}" class="btn btn-sm btn-light mr-3"><i class="ti-arrow-left"></i></a>
            <div><h3 class="font-weight-bold mb-1">Tambah Category Course</h3><p class="text-muted mb-0">Buat pengelompokan baru untuk program belajar.</p></div>
        </div>
        <div class="card"><div class="card-body"><form action="{{ route('course-categories.store') }}" method="POST">@csrf @include('pages.course-categories._form')</form></div></div>
    </div>
</div>
@endsection
