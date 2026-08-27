@extends('layouts.auth')

@section('title', 'Login Admin')

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css">
@endpush

@section('main')
 <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
                <h3>Login</h3>
              </div>

              <form class="pt-3" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                  <input type="email" name="email" class="form-control form-control-lg" id="exampleInputEmail1" placeholder="Email">
                </div>
                <div class="form-group">
                  <div class="input-group">
                    <input type="password" name="password" class="form-control form-control-lg" id="exampleInputPassword1" placeholder="Password">
                    <div class="input-group-append">
                      <span class="input-group-text bg-white" id="toggle-password" style="cursor:pointer;">
                        <i class="mdi mdi-eye-off" id="toggle-password-icon"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">SIGN IN</button>
                </div>

              </form>

               <div class="text-center mt-4 font-weight-light">
                  Belum Punya Akun? <a href="{{ route('register') }}" class="text-primary">Create</a>
                </div>
            </div>
          </div>
        </div>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-password').addEventListener('click', function () {
    var input = document.getElementById('exampleInputPassword1');
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
