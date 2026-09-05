@extends('layouts.app')

@section('title', 'Pilih Pembelajaran')

@push('style')
<style>
    .select-report-card{border:0;border-radius:13px;max-width:900px}.report-guide{background:#f4f4ff;border:1px solid #dedff5;border-radius:10px;padding:16px}.schedule-preview{background:#fafbfc;border:1px solid #e4e7ed;border-radius:9px;padding:15px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Buat Report Pembelajaran</h3><p class="text-muted mb-0">Pilih pembelajaran yang telah selesai untuk membuka form report.</p></div><a href="{{ route('pending-reports.index') }}" class="btn btn-light"><i class="ti-arrow-left mr-1"></i>Kembali</a></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card select-report-card mx-auto"><div class="card-body p-4 p-md-5">
    <div class="report-guide mb-4"><i class="ti-info-alt text-primary mr-1"></i>Form report hanya tersedia setelah tanggal dan jam pembelajaran selesai. Setiap pembelajaran hanya dapat dilaporkan satu kali.</div>
    @if($schedules->isNotEmpty())
        <form method="GET" action="{{ route('pending-reports.select') }}">
            <div class="form-group"><label for="schedule_id" class="font-weight-bold">Pembelajaran <span class="text-danger">*</span></label><select id="schedule_id" name="schedule_id" class="form-control" required><option value="">Pilih pembelajaran</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}">{{ $schedule->training_date->translatedFormat('d M Y') }} · {{ substr($schedule->start_time,0,5) }} · {{ $schedule->transaction->student->name }} · {{ $schedule->transaction->course->name }} · {{ $schedule->lesson?->title ?? 'Materi' }}</option>@endforeach</select></div>
            <div class="text-right"><button class="btn btn-primary px-4"><i class="ti-arrow-right mr-1"></i>Buka Form Report</button></div>
        </form>
    @else
        <div class="schedule-preview text-center py-5"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Belum ada pembelajaran yang dapat dilaporkan</h5><p class="text-muted mb-0">Pembelajaran akan tersedia setelah jadwal training selesai.</p></div>
    @endif
</div></div>
@endsection
