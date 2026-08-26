@extends('layouts.auth')

@section('title', 'Register')

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css">
@endpush

@section('main')
<div class="row w-100 mx-0">
    <div class="col-lg-4 mx-auto">
        <div class="auth-form-light text-left py-5 px-4 px-sm-5">

            <div class="brand-logo">
                <h3>Register</h3>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-lg"
                           name="name"
                           placeholder="Nama"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="form-group">
                    <input type="email"
                           class="form-control form-control-lg"
                           name="email"
                           placeholder="Email"
                           value="{{ old('email') }}"
                           required>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <input type="password"
                               class="form-control form-control-lg"
                               name="password"
                               id="inputPassword"
                               placeholder="Password"
                               required>
                        <div class="input-group-append">
                            <span class="input-group-text bg-white" id="toggle-password" style="cursor:pointer;">
                                <i class="mdi mdi-eye-off" id="toggle-password-icon"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-lg"
                           name="alamat_lengkap"
                           placeholder="Alamat Lengkap"
                           required>
                </div>

                 <div class="form-group">
                    <input type="text"
                           class="form-control form-control-lg"
                           name="no_hp"
                           placeholder="No Hp"
                           required>
                </div>

                <div class="mt-3">
                    <button type="submit"
                            class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                        Daftar
                    </button>
                </div>

                <!-- <div class="text-center mt-4 font-weight-light">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary">
                        Login
                    </a>
                </div> -->

            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-password').addEventListener('click', function () {
    var input = document.getElementById('inputPassword');
    var icon = document.getElementById('toggle-password-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('mdi-eye-off');
        icon.classList.add('mdi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('mdi-eye');
        icon.classList.add('mdi-eye-off');
    }
});
</script>
@endpush
