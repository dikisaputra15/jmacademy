@extends('layouts.app')

@section('title', 'Laporan Pembelajaran')

@push('style')
<style>
    .parent-form-card{border:0;border-radius:13px}.student-summary{background:#f4f4ff;border:1px solid #dedff5;border-radius:10px;padding:18px}.summary-label{color:#7c8491;font-size:11px;text-transform:uppercase}.achievement-table{min-width:900px}.achievement-table thead th{background:#4b49ac;border-color:#6664bf;color:#fff;padding:14px;text-align:center}.achievement-table td{padding:16px;vertical-align:top}.meeting-report{border-bottom:1px solid #eceef2;margin-bottom:12px;padding-bottom:12px}.meeting-report:last-child{border:0;margin:0;padding:0}.skill-option{align-items:center;border:1px solid #dfe3e9;border-radius:9px;display:flex;min-height:52px;padding:13px}.section-title{border-bottom:1px solid #e6e8ec;font-weight:700;margin:25px 0 18px;padding-bottom:10px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Laporan Pembelajaran</h3><p class="text-muted mb-0">Ringkasan akhir perkembangan student untuk orang tua.</p></div><a href="{{ route('parent-reports.index') }}" class="btn btn-light"><i class="ti-arrow-left mr-1"></i>Kembali</a></div>
@if($errors->any())<div class="alert alert-danger"><strong>Parent report belum dapat disimpan.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card parent-form-card"><div class="card-body p-4 p-md-5">
    @php($schedules = $transaction->paidSchedules)
    <div class="student-summary"><div class="row">
        <div class="col-md-3 mb-3 mb-md-0"><span class="summary-label d-block">Nama Student</span><strong>{{ $transaction->student->name }}</strong><small class="text-muted d-block">{{ $transaction->student->email }}</small></div>
        <div class="col-md-3 mb-3 mb-md-0"><span class="summary-label d-block">Course</span><strong>{{ $transaction->course->name }}</strong><small class="text-muted d-block">{{ $transaction->course->category->name }}</small></div>
        <div class="col-md-3 mb-3 mb-md-0"><span class="summary-label d-block">Pembimbing</span><strong>{{ auth()->user()->name }}</strong><small class="text-muted d-block">{{ auth()->user()->email }}</small></div>
        <div class="col-md-3"><span class="summary-label d-block">Pertemuan</span><strong>1–{{ $schedules->count() }}</strong><small class="text-success d-block">Semua report lengkap</small></div>
    </div></div>

    <div class="section-title">Pencapaian dari Report Pertemuan</div>
    <div class="table-responsive"><table class="table table-bordered achievement-table"><thead><tr><th style="width:18%">Pertemuan</th><th style="width:25%">Materi Pembelajaran</th><th>Hasil Pembelajaran</th><th style="width:12%">Kehadiran</th></tr></thead><tbody>
        @foreach($schedules as $schedule)<tr><td><strong>Pertemuan {{ $schedule->meeting_number ?? $loop->iteration }}</strong><small class="text-muted d-block">{{ $schedule->training_date->translatedFormat('d M Y') }}</small></td><td><strong>{{ $schedule->lesson?->title ?? 'Materi' }}</strong><small class="text-muted d-block">{{ $schedule->lesson?->section?->name }}</small></td><td>{{ $schedule->learningReport->learning_summary }}@if($schedule->learningReport->notes)<small class="text-muted d-block mt-2">Catatan: {{ $schedule->learningReport->notes }}</small>@endif</td><td class="text-center">@if($schedule->learningReport->is_present)<span class="badge badge-success">Hadir</span>@else<span class="badge badge-danger">Tidak Hadir</span>@endif</td></tr>@endforeach
    </tbody></table></div>

    <form method="POST" action="{{ route('parent-reports.store', $transaction) }}">@csrf
        <div class="section-title">Penilaian Akhir</div>
        <div class="row"><div class="col-md-3 form-group"><label for="grade">Predikat <span class="text-danger">*</span></label><select id="grade" name="grade" class="form-control" required><option value="">Pilih Predikat</option>@foreach(['A'=>'A — Sangat Baik','B'=>'B — Baik','C'=>'C — Cukup','D'=>'D — Perlu Bimbingan'] as $value=>$label)<option value="{{ $value }}" @selected(old('grade')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-9"><label>Kompetensi yang Dicapai</label><div class="row"><div class="col-md-4 mb-2"><label class="skill-option"><input type="checkbox" name="understanding" value="1" class="mr-2" @checked(old('understanding'))><strong>Pemahaman</strong></label></div><div class="col-md-4 mb-2"><label class="skill-option"><input type="checkbox" name="logic" value="1" class="mr-2" @checked(old('logic'))><strong>Logika</strong></label></div><div class="col-md-4 mb-2"><label class="skill-option"><input type="checkbox" name="creativity" value="1" class="mr-2" @checked(old('creativity'))><strong>Kreativitas</strong></label></div></div></div></div>
        <div class="form-group"><label for="strengths">Pencapaian dan Kelebihan Student <span class="text-danger">*</span></label><textarea id="strengths" name="strengths" rows="4" maxlength="5000" class="form-control" placeholder="Jelaskan perkembangan, kemampuan, dan pencapaian student selama mengikuti course" required>{{ old('strengths') }}</textarea></div>
        <div class="form-group"><label for="improvements">Hal yang Perlu Ditingkatkan</label><textarea id="improvements" name="improvements" rows="3" maxlength="5000" class="form-control" placeholder="Jelaskan kemampuan atau kebiasaan belajar yang masih perlu ditingkatkan">{{ old('improvements') }}</textarea></div>
        <div class="form-group"><label for="recommendation">Rekomendasi untuk Orang Tua</label><textarea id="recommendation" name="recommendation" rows="3" maxlength="5000" class="form-control" placeholder="Saran latihan, pendampingan, atau course lanjutan">{{ old('recommendation') }}</textarea></div>
        <div class="text-right mt-4"><button class="btn btn-primary px-4"><i class="ti-save mr-1"></i>Simpan Parent Report</button></div>
    </form>
</div></div>
@endsection
