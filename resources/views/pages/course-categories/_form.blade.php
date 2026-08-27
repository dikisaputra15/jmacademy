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

<div class="form-group">
    <label for="name">Nama Category <span class="text-danger">*</span></label>
    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $courseCategory->name ?? '') }}" placeholder="Contoh: Robotics" required autofocus>
    <small class="form-text text-muted">Slug URL akan dibuat otomatis dari nama category.</small>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="description">Deskripsi</label>
    <textarea id="description" name="description" rows="5" maxlength="1000" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan isi dan tujuan category ini...">{{ old('description', $courseCategory->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="is_active">Status <span class="text-danger">*</span></label>
    <select id="is_active" name="is_active" class="form-control @error('is_active') is-invalid @enderror" required>
        <option value="1" @selected((string) old('is_active', isset($courseCategory) ? (int) $courseCategory->is_active : 1) === '1')>Aktif</option>
        <option value="0" @selected((string) old('is_active', isset($courseCategory) ? (int) $courseCategory->is_active : 1) === '0')>Nonaktif</option>
    </select>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('course-categories.index') }}" class="btn btn-light mr-2">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="ti-save mr-1"></i> Simpan Category</button>
</div>
