@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('main')
    <div class="row w-100 mx-0">
        <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo">
                    <h3>Lupa Password</h3>
                </div>

                <p class="text-muted">
                    Masukkan email akun Anda. Kami akan mengirimkan link untuk membuat password baru.
                </p>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control form-control-lg"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                        Kirim Link Reset Password
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-primary">Kembali ke Login</a>
                </div>
            </div>
        </div>
    </div>
@endsection
