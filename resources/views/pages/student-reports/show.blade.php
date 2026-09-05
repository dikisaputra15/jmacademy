@extends('layouts.app')

@section('title', 'Detail Report Study')

@push('style')
<style>
    .study-card{border:0;border-radius:13px}.student-summary{background:#f4f4ff;border:1px solid #dedff5;border-radius:10px;padding:18px}.summary-label{color:#7c8491;font-size:11px;text-transform:uppercase}.study-table{min-width:850px}.study-table thead th{background:#4b49ac;border-color:#6664bf;color:#fff;padding:14px}.study-table td{padding:15px;vertical-align:top}.assessment-box{background:#fafbfc;border:1px solid #e4e7ed;border-radius:10px;padding:18px}.grade-large{align-items:center;background:#4b49ac;border-radius:50%;color:#fff;display:flex;font-size:32px;font-weight:700;height:72px;justify-content:center;width:72px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Detail Report Study</h3><p class="text-muted mb-0">Laporan perkembangan pembelajaran dari guru.</p></div><div><a href="{{ route('student-reports.index') }}" class="btn btn-light mr-1"><i class="ti-arrow-left mr-1"></i>Kembali</a><a href="{{ route('student-reports.certificate', $report) }}" class="btn btn-primary"><i class="ti-medall mr-1"></i>Lihat Sertifikat</a></div></div>

<div class="card study-card"><div class="card-body p-4 p-md-5">
    <div class="student-summary mb-4"><div class="row"><div class="col-md-3 mb-3 mb-md-0"><span class="summary-label d-block">Nama Student</span><strong>{{ $report->student->name }}</strong></div><div class="col-md-3 mb-3 mb-md-0"><span class="summary-label d-block">Course</span><strong>{{ $report->course->name }}</strong><small class="text-muted d-block">{{ $report->course->category->name }}</small></div><div class="col-md-3 mb-3 mb-md-0"><span class="summary-label d-block">Pembimbing</span><strong>{{ $report->teacher->name }}</strong></div><div class="col-md-3"><span class="summary-label d-block">Tanggal Report</span><strong>{{ $report->submitted_at->translatedFormat('d F Y') }}</strong></div></div></div>

    <h5 class="font-weight-bold mb-3">Riwayat Pembelajaran</h5><div class="table-responsive mb-4"><table class="table table-bordered study-table"><thead><tr><th>Pertemuan</th><th>Materi</th><th>Hasil Pembelajaran</th><th>Kehadiran</th></tr></thead><tbody>@foreach($report->transaction->paidSchedules as $schedule)<tr><td><strong>{{ $schedule->meeting_number ?? $loop->iteration }}</strong><small class="text-muted d-block">{{ $schedule->training_date->translatedFormat('d M Y') }}</small></td><td><strong>{{ $schedule->lesson?->title ?? 'Materi' }}</strong><small class="text-muted d-block">{{ $schedule->lesson?->section?->name }}</small></td><td>{{ $schedule->learningReport?->learning_summary }}@if($schedule->learningReport?->notes)<small class="text-muted d-block mt-2">Catatan: {{ $schedule->learningReport->notes }}</small>@endif</td><td>@if($schedule->learningReport?->is_present)<span class="badge badge-success">Hadir</span>@else<span class="badge badge-danger">Tidak Hadir</span>@endif</td></tr>@endforeach</tbody></table></div>

    <div class="assessment-box"><div class="row"><div class="col-md-2 d-flex justify-content-center align-items-start mb-3"><span class="grade-large">{{ $report->grade }}</span></div><div class="col-md-10"><h5 class="font-weight-bold">Penilaian Akhir</h5><div class="mb-3">@if($report->understanding)<span class="badge badge-success mr-1">Pemahaman</span>@endif @if($report->logic)<span class="badge badge-success mr-1">Logika</span>@endif @if($report->creativity)<span class="badge badge-success">Kreativitas</span>@endif</div><strong>Pencapaian dan Kelebihan</strong><p>{{ $report->strengths }}</p>@if($report->improvements)<strong>Hal yang Perlu Ditingkatkan</strong><p>{{ $report->improvements }}</p>@endif @if($report->recommendation)<strong>Rekomendasi Guru</strong><p class="mb-0">{{ $report->recommendation }}</p>@endif</div></div></div>
</div></div>
@endsection
