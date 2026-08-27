@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Data belum dapat disimpan.</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="form-group">
    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required autofocus>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="email">Email <span class="text-danger">*</span></label>
    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="role">Role <span class="text-danger">*</span></label>
            <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                <option value="">Pilih role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', isset($user) ? $user->getRoleNames()->first() : '') === $role->name)>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="is_active">Status <span class="text-danger">*</span></label>
            <select id="is_active" name="is_active" class="form-control @error('is_active') is-invalid @enderror" required>
                <option value="1" @selected((string) old('is_active', isset($user) ? (int) $user->is_active : 1) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', isset($user) ? (int) $user->is_active : 1) === '0')>Nonaktif</option>
            </select>
            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="password">Password @unless(isset($user))<span class="text-danger">*</span>@endunless</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
            @if (isset($user))<small class="form-text text-muted">Kosongkan jika password tidak diubah.</small>@endif
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password @unless(isset($user))<span class="text-danger">*</span>@endunless</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('users.index') }}" class="btn btn-light mr-2">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="ti-save mr-1"></i> Simpan User</button>
</div>
