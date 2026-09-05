@extends('layouts.app')

@section('title', 'Isi Report Pembelajaran')

@push('style')
<style>
    .report-form-card{border:0;border-radius:13px}.class-summary{background:#f4f4ff;border:1px solid #dedff5;border-radius:10px;padding:18px}.summary-item small{color:#7c8491;display:block;font-size:11px;margin-bottom:3px;text-transform:uppercase}.attendance-option{border:1px solid #dfe3e9;border-radius:9px;cursor:pointer;padding:13px 16px}.attendance-option input{margin-right:8px}.section-title{border-bottom:1px solid #e6e8ec;font-weight:700;margin-bottom:18px;padding-bottom:10px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Isi Report Pembelajaran</h3><p class="text-muted mb-0">Lengkapi hasil pembelajaran setelah sesi training selesai.</p></div><a href="{{ route('pending-reports.index') }}" class="btn btn-light mt-2 mt-md-0"><i class="ti-arrow-left mr-1"></i>Kembali</a></div>
@if($errors->any())<div class="alert alert-danger"><strong>Report belum dapat disimpan.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card report-form-card"><div class="card-body p-4 p-md-5">
    <div class="class-summary mb-4"><div class="row">
        <div class="col-md-3 summary-item mb-3 mb-md-0"><small>Course</small><strong>{{ $schedule->transaction->course->name }}</strong><div class="text-muted">{{ $schedule->transaction->course->code }}</div></div>
        <div class="col-md-3 summary-item mb-3 mb-md-0"><small>Student</small><strong>{{ $schedule->transaction->student->name }}</strong><div class="text-muted">{{ $schedule->transaction->student->email }}</div></div>
        <div class="col-md-3 summary-item mb-3 mb-md-0"><small>Materi</small><strong>{{ $schedule->lesson?->title ?? 'Materi belum ditentukan' }}</strong><div class="text-muted">{{ $schedule->lesson?->section?->name ?? 'Kurikulum' }} · Pertemuan {{ $schedule->meeting_number ?? '-' }}</div></div>
        <div class="col-md-3 summary-item"><small>Jadwal</small><strong>{{ $schedule->training_date->translatedFormat('d F Y') }}</strong><div class="text-muted">{{ substr($schedule->start_time,0,5) }}–{{ substr($schedule->end_time,0,5) }} WIB</div></div>
    </div></div>

    <form method="POST" action="{{ route('pending-reports.store',$schedule) }}">@csrf
        <div class="section-title">Kehadiran Student</div>
        <div class="row mb-4"><div class="col-md-6 mb-2"><label class="attendance-option d-block"><input type="radio" name="is_present" value="1" @checked(old('is_present','1')==='1') required><strong>Hadir</strong><small class="text-muted d-block ml-4">Student mengikuti sesi pembelajaran.</small></label></div><div class="col-md-6 mb-2"><label class="attendance-option d-block"><input type="radio" name="is_present" value="0" @checked(old('is_present')==='0') required><strong>Tidak Hadir</strong><small class="text-muted d-block ml-4">Student tidak mengikuti sesi pembelajaran.</small></label></div></div>

        <div class="section-title">Report Pembelajaran</div>
        <div class="form-group"><label for="learning_summary">Hasil Pembelajaran <span class="text-danger">*</span></label><textarea id="learning_summary" name="learning_summary" rows="5" maxlength="5000" class="form-control" placeholder="Jelaskan materi yang dipelajari, pemahaman student, latihan yang diselesaikan, dan perkembangan student" required>{{ old('learning_summary') }}</textarea></div>
        <div class="form-group"><label for="notes">Catatan Tambahan</label><textarea id="notes" name="notes" rows="3" maxlength="5000" class="form-control" placeholder="Kendala, tugas rumah, atau saran untuk pertemuan berikutnya">{{ old('notes') }}</textarea></div>

        <div class="section-title mt-4">Dokumentasi Video <small class="text-muted font-weight-normal">(opsional)</small></div>
        <div class="row"><div class="col-md-5 form-group"><label for="video_title">Judul Video</label><input id="video_title" name="video_title" value="{{ old('video_title') }}" maxlength="255" class="form-control" placeholder="Contoh: Latihan membuat variabel"></div><div class="col-md-7 form-group"><label for="video_url">Link Video</label><input id="video_url" type="url" name="video_url" value="{{ old('video_url') }}" maxlength="1000" class="form-control" placeholder="https://youtube.com/... atau https://drive.google.com/..."></div></div>
        <div class="text-right mt-3"><button class="btn btn-primary px-4"><i class="ti-save mr-1"></i>Simpan Report</button></div>
    </form>
</div></div>
@endsection
