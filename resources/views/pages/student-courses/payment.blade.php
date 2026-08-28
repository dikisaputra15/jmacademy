@extends('layouts.app')

@section('title', 'Pembayaran Kelas')

@push('style')
<style>
    .payment-card{border:0;border-radius:14px;overflow:hidden}.payment-summary{background:linear-gradient(135deg,#4b49ac,#7978d8);color:#fff;padding:28px}.bank-box{background:#f4f5ff;border:1px solid #dedff7;border-radius:10px;padding:18px}.payment-total{font-size:28px;font-weight:700}.upload-box{border:2px dashed #d8dce5;border-radius:10px;padding:18px}
</style>
@endpush

@section('main')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('student-courses.index') }}" class="btn btn-sm btn-light mr-3"><i class="ti-arrow-left"></i></a>
            <div><h3 class="font-weight-bold mb-1">Pembayaran Kelas</h3><p class="text-muted mb-0">Transfer biaya kelas, kemudian unggah bukti pembayaran.</p></div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="card payment-card">
            <div class="row no-gutters">
                <div class="col-lg-5 payment-summary">
                    <span class="badge badge-light mb-3">{{ $course->code }}</span>
                    <h4>{{ $course->name }}</h4>
                    <p class="mb-4 text-white-50">{{ $course->category->name }}</p>
                    <small class="d-block text-white-50">Total yang harus ditransfer</small>
                    <div class="payment-total">Rp {{ number_format($courseTotal, 0, ',', '.') }}</div>
                    <hr class="border-light my-4">
                    <small>{{ $course->sections->sum(fn ($section) => $section->lessons->sum('meetings')) }} pertemuan</small>
                </div>
                <div class="col-lg-7">
                    <div class="card-body p-4 p-lg-5">
                        <h5 class="font-weight-bold mb-3">Transfer ke rekening</h5>
                        <div class="bank-box mb-4">
                            <small class="text-muted d-block">Bank</small><strong class="d-block mb-2">{{ config('payment.bank_name') }}</strong>
                            <small class="text-muted d-block">Nomor rekening</small><strong class="h5 d-block mb-2">{{ config('payment.account_number') }}</strong>
                            <small class="text-muted d-block">Atas nama</small><strong>{{ config('payment.account_holder') }}</strong>
                        </div>

                        <form method="POST" action="{{ route('student-courses.enroll', $course) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group"><label for="sender_name">Nama pemilik rekening pengirim</label><input id="sender_name" name="sender_name" value="{{ old('sender_name', auth()->user()->name) }}" class="form-control" required></div>
                            <div class="form-group"><label for="sender_bank">Bank pengirim</label><input id="sender_bank" name="sender_bank" value="{{ old('sender_bank') }}" class="form-control" placeholder="Contoh: BCA, BRI, Mandiri" required></div>
                            <div class="form-group"><label for="transfer_date">Tanggal transfer</label><input id="transfer_date" type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="form-control" required></div>
                            <div class="form-group upload-box"><label for="payment_proof" class="font-weight-bold">Upload bukti transfer</label><input id="payment_proof" type="file" name="payment_proof" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf" required><small class="form-text text-muted">Format JPG, JPEG, PNG, atau PDF. Maksimal 2 MB.</small></div>
                            <button class="btn btn-primary btn-block"><i class="ti-upload mr-1"></i> Kirim Bukti Pembayaran</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
