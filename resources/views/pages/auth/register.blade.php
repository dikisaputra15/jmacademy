@extends('layouts.auth')

@section('title', 'Register')

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css">
<style>
    .role-options{display:grid;gap:12px;grid-template-columns:1fr 1fr}.role-option{margin:0;position:relative}.role-option input{opacity:0;position:absolute}.role-card{border:2px solid #e2e5ed;border-radius:10px;cursor:pointer;display:block;padding:16px;text-align:center;transition:.2s}.role-card i{color:#6c757d;display:block;font-size:28px;margin-bottom:5px}.role-option input:checked + .role-card{background:#f0efff;border-color:#4b49ac;color:#4b49ac}.role-option input:checked + .role-card i{color:#4b49ac}.role-option input:focus + .role-card{box-shadow:0 0 0 .2rem rgba(75,73,172,.2)}
</style>
@endpush

@section('main')
<div class="row w-100 mx-0">
    <div class="col-lg-5 mx-auto">
        <div class="auth-form-light text-left py-5 px-4 px-sm-5">

            <div class="brand-logo">
                <h3>Register</h3>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger"><strong>Pendaftaran belum berhasil.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="form-group">
                    <label class="font-weight-bold d-block">Daftar Sebagai <span class="text-danger">*</span></label>
                    <div class="role-options">
                        <label class="role-option"><input type="radio" name="role" value="guru" @checked(old('role') === 'guru') required><span class="role-card"><i class="mdi mdi-teach"></i><strong>Guru</strong><small class="d-block text-muted">Mengajar course</small></span></label>
                        <label class="role-option"><input type="radio" name="role" value="student" @checked(old('role') === 'student') required><span class="role-card"><i class="mdi mdi-school"></i><strong>Student</strong><small class="d-block text-muted">Mengikuti course</small></span></label>
                    </div>
                </div>

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
                    <input type="password" class="form-control form-control-lg" name="password_confirmation" placeholder="Konfirmasi Password" required>
                </div>

                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-lg"
                           name="address"
                           placeholder="Alamat Lengkap"
                           value="{{ old('address') }}"
                           required>
                </div>

                 <div class="form-group">
                    <input type="text"
                           class="form-control form-control-lg"
                           name="phone"
                           placeholder="No Hp"
                           value="{{ old('phone') }}"
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
