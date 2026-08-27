@extends('layouts.app')

@section('title', 'Profil Saya')

@push('style')
<style>
    .profile-card { max-width: 920px; margin: 0 auto; }
    .profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: #4b49ac; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 600; }
    .section-title { font-size: 16px; font-weight: 600; padding-bottom: 12px; border-bottom: 1px solid #e5e5e5; margin-bottom: 22px; }
    .required { color: #dc3545; }
</style>
@endpush

@section('main')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="font-weight-bold mb-1">Profil Saya</h3>
        <p class="text-muted mb-0">Kelola data diri dan alamat akun Anda.</p>
    </div>
</div>

<div class="card profile-card">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center mb-4">
            <div class="profile-avatar mr-3">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <span class="text-muted">{{ $user->email }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Profil belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="section-title">Data Diri</div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="name">Nama Lengkap <span class="required">*</span></label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="phone">No. HP</label>
                    <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
                </div>
                <div class="col-md-3 form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror">
                        <option value="">Pilih</option>
                        <option value="male" @selected(old('gender', $user->gender) === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender', $user->gender) === 'female')>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="date_of_birth">Tanggal Lahir</label>
                    <input id="date_of_birth" name="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                </div>
            </div>

            @role('student')
            <div class="section-title mt-3">Data Orang Tua</div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="parent_name">Nama Orang Tua</label>
                    <input id="parent_name" name="parent_name" type="text" class="form-control @error('parent_name') is-invalid @enderror" value="{{ old('parent_name', $user->parent_name) }}" placeholder="Nama lengkap orang tua / wali">
                    @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="parent_phone">No. HP Orang Tua</label>
                    <input id="parent_phone" name="parent_phone" type="tel" class="form-control @error('parent_phone') is-invalid @enderror" value="{{ old('parent_phone', $user->parent_phone) }}" placeholder="Contoh: 081234567890">
                    @error('parent_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            @endrole

            <div class="section-title mt-3">Alamat</div>
            <div id="region-alert" class="alert alert-warning d-none" role="alert"></div>
            <div class="row">
                @php
                    $regions = [
                        ['province', 'Provinsi'], ['regency', 'Kabupaten / Kota'],
                    ];
                @endphp
                @foreach ($regions as [$key, $label])
                    <div class="col-md-6 form-group">
                        <label for="{{ $key }}_id">{{ $label }}</label>
                        <select id="{{ $key }}_id" name="{{ $key }}_id" class="form-control region-select @error($key.'_id') is-invalid @enderror" data-current="{{ old($key.'_id', $user->{$key.'_id'}) }}">
                            <option value="">{{ $key === 'province' ? 'Memuat data...' : 'Pilih '.$label }}</option>
                        </select>
                        <input type="hidden" id="{{ $key }}_name" name="{{ $key }}_name" value="{{ old($key.'_name', $user->{$key.'_name'}) }}">
                    </div>
                @endforeach
                <div class="col-md-4 form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input id="postal_code" name="postal_code" type="text" inputmode="numeric" maxlength="5" class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code', $user->postal_code) }}">
                </div>
                <div class="col-md-8 form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Nama jalan, nomor rumah, RT/RW, dan patokan">{{ old('address', $user->address) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4"><i class="ti-save mr-1"></i> Simpan Profil</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const levels = ['province', 'regency'];
    const endpoints = { province: 'provinces', regency: 'regencies' };
    const baseUrl = @json(url('/regions'));
    const alertBox = document.getElementById('region-alert');

    function select(level) { return document.getElementById(level + '_id'); }
    function nameInput(level) { return document.getElementById(level + '_name'); }

    function resetAfter(level) {
        levels.slice(levels.indexOf(level) + 1).forEach(function (child) {
            select(child).innerHTML = '<option value="">Pilih ' + label(child) + '</option>';
            nameInput(child).value = '';
        });
    }

    function label(level) {
        return { province: 'Provinsi', regency: 'Kabupaten / Kota' }[level];
    }

    async function load(level, parentId, restore) {
        const element = select(level);
        if (level !== 'province' && !parentId) return;
        const savedId = element.dataset.current;
        const savedName = nameInput(level).value;
        element.disabled = true;
        element.innerHTML = savedId
            ? '<option value="' + savedId + '">' + savedName + ' (memuat...)</option>'
            : '<option value="">Memuat data...</option>';

        try {
            const path = baseUrl + '/' + endpoints[level] + (parentId ? '/' + parentId : '');
            const response = await fetch(path, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Gagal memuat data wilayah.');

            element.innerHTML = '<option value="">Pilih ' + label(level) + '</option>';
            payload.data.forEach(function (region) {
                const option = new Option(region.name, region.code || region.id);
                element.add(option);
            });
            element.disabled = false;
            alertBox.classList.add('d-none');

            const current = restore ? element.dataset.current : '';
            if (current && Array.from(element.options).some(option => option.value === current)) {
                element.value = current;
                nameInput(level).value = element.options[element.selectedIndex].text;
                const next = levels[levels.indexOf(level) + 1];
                if (next) await load(next, current, true);
            }
        } catch (error) {
            element.innerHTML = savedId
                ? '<option value="' + savedId + '">' + savedName + '</option>'
                : '<option value="">Data tidak tersedia</option>';
            element.value = savedId || '';
            element.disabled = false;
            alertBox.textContent = error.message + ' Data diri lainnya tetap dapat disimpan.';
            alertBox.classList.remove('d-none');
        }
    }

    levels.forEach(function (level, index) {
        select(level).addEventListener('change', function () {
            nameInput(level).value = this.value ? this.options[this.selectedIndex].text : '';
            resetAfter(level);
            const next = levels[index + 1];
            if (next && this.value) load(next, this.value, false);
        });
    });

    load('province', null, true);
})();
</script>
@endpush
