@extends('layouts.app')
@section('title', 'Edit Course')
@section('main')
<div class="row justify-content-center"><div class="col-lg-10"><div class="d-flex align-items-center mb-4"><a href="{{ route('courses.index', ['category' => $course->course_category_id]) }}" class="btn btn-sm btn-light mr-3"><i class="ti-arrow-left"></i></a><div><h3 class="font-weight-bold mb-1">Edit Course</h3><p class="text-muted mb-0">Perbarui informasi {{ $course->name }}.</p></div></div><div class="card"><div class="card-body"><form method="POST" action="{{ route('courses.update', $course) }}">@csrf @method('PUT') @include('pages.courses._form')</form></div></div></div></div>
@endsection
