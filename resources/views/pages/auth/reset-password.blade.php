@extends('layouts.auth')

@section('title', 'Reset Password')

@section('main')
    <div class="row w-100 mx-0">
        <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo">
                    <h3>Buat Password Baru</h3>
                </div>

                <p class="text-muted">Gunakan password baru yang kuat dan mudah Anda ingat.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            class="form-control form-control-lg"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control form-control-lg"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control form-control-lg"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                        Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
