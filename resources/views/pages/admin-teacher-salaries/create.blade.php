@extends('layouts.app')

@section('title', 'Bayar Gaji Guru')

@push('style')
<style>
    .payment-card{border:0;border-radius:13px}.teacher-summary{background:#f4f4ff;border:1px solid #dedff5;border-radius:10px;padding:18px}.salary-list{max-height:360px;overflow:auto}.salary-row{border-bottom:1px solid #eceef2;padding:13px 3px}.salary-row:last-child{border:0}.total-panel{background:#4b49ac;border-radius:10px;color:#fff;padding:18px}.section-title{border-bottom:1px solid #e6e8ec;font-weight:700;margin:25px 0 18px;padding-bottom:10px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Bayar Gaji Guru</h3><p class="text-muted mb-0">Konfirmasi pembayaran seluruh honor yang belum dibayar.</p></div><a href="{{ route('admin-teacher-salaries.index') }}" class="btn btn-light"><i class="ti-arrow-left mr-1"></i>Kembali</a></div>
@if($errors->any())<div class="alert alert-danger"><strong>Pembayaran belum dapat disimpan.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card payment-card"><div class="card-body p-4 p-md-5">
    <div class="teacher-summary"><div class="row align-items-center"><div class="col-md-5 mb-3 mb-md-0"><small class="text-muted d-block">Guru</small><h5 class="font-weight-bold mb-1">{{ $teacher->name }}</h5><span class="text-muted">{{ $teacher->email }}</span></div><div class="col-md-3 mb-3 mb-md-0"><small class="text-muted d-block">Jumlah Pertemuan</small><h5 class="font-weight-bold mb-0">{{ $salaries->count() }} pertemuan</h5></div><div class="col-md-4"><div class="total-panel"><small>Total yang Dibayarkan</small><h4 class="font-weight-bold mb-0">Rp {{ number_format($total,0,',','.') }}</h4></div></div></div></div>

    <div class="section-title">Rincian Honor</div><div class="salary-list">@foreach($salaries as $salary)<div class="salary-row"><div class="row"><div class="col-md-3"><strong>{{ $salary->schedule->training_date->translatedFormat('d M Y') }}</strong><small class="text-muted d-block">{{ substr($salary->schedule->start_time,0,5) }} WIB</small></div><div class="col-md-4"><strong>{{ $salary->course->name }}</strong><small class="text-muted d-block">{{ $salary->schedule->lesson?->title ?? 'Materi' }} · Pertemuan {{ $salary->schedule->meeting_number ?? '-' }}</small></div><div class="col-md-3"><strong>{{ $salary->student->name }}</strong></div><div class="col-md-2 text-md-right"><strong class="text-primary">Rp {{ number_format($salary->amount,0,',','.') }}</strong></div></div></div>@endforeach</div>

    <form method="POST" action="{{ route('admin-teacher-salaries.store',$teacher) }}" enctype="multipart/form-data">@csrf
        <div class="section-title">Data Transfer</div><div class="row"><div class="col-md-4 form-group"><label for="bank_name">Nama Bank <span class="text-danger">*</span></label><input id="bank_name" name="bank_name" value="{{ old('bank_name',$teacher->bank_name) }}" maxlength="100" class="form-control" required></div><div class="col-md-4 form-group"><label for="bank_account_number">Nomor Rekening <span class="text-danger">*</span></label><input id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number',$teacher->bank_account_number) }}" maxlength="100" class="form-control" required></div><div class="col-md-4 form-group"><label for="bank_account_holder">Nama Pemilik Rekening <span class="text-danger">*</span></label><input id="bank_account_holder" name="bank_account_holder" value="{{ old('bank_account_holder',$teacher->bank_account_holder) }}" maxlength="255" class="form-control" required></div></div>
        <div class="row"><div class="col-md-4 form-group"><label for="transfer_date">Tanggal Transfer <span class="text-danger">*</span></label><input id="transfer_date" type="date" name="transfer_date" value="{{ old('transfer_date',today()->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" class="form-control" required></div><div class="col-md-8 form-group"><label for="transfer_proof">Bukti Transfer <span class="text-danger">*</span></label><input id="transfer_proof" type="file" name="transfer_proof" accept=".jpg,.jpeg,.png,.pdf" class="form-control-file" required><small class="text-muted">Format JPG, JPEG, PNG, atau PDF. Maksimal 2 MB.</small></div></div>
        <div class="form-group"><label for="notes">Catatan Pembayaran</label><textarea id="notes" name="notes" rows="3" maxlength="2000" class="form-control" placeholder="Contoh: Pembayaran honor periode September 2026">{{ old('notes') }}</textarea></div>
        <div class="alert alert-info"><i class="ti-info-alt mr-1"></i>Setelah disimpan, {{ $salaries->count() }} salary senilai <strong>Rp {{ number_format($total,0,',','.') }}</strong> akan ditandai sudah dibayar.</div><div class="text-right"><button class="btn btn-primary px-4"><i class="ti-check mr-1"></i>Konfirmasi Pembayaran</button></div>
    </form>
</div></div>
@endsection
