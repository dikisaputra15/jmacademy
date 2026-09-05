@if ($errors->any())
    <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><ul class="mb-0 mt-2 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row">
    <div class="col-md-7 form-group">
        <label for="name">Nama Course <span class="text-danger">*</span></label>
        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $course->name ?? '') }}" placeholder="Contoh: Scratch Junior" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5 form-group">
        <label for="code">Kode Course</label>
        <input id="code" class="form-control bg-light" value="{{ $course->code ?? $suggestedCode ?? 'Dibuat otomatis' }}" readonly tabindex="-1">
        <small class="form-text text-muted">Kode dibuat otomatis oleh sistem dan tidak dapat diubah.</small>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        <label for="course_category_id">Category Course <span class="text-danger">*</span></label>
        <select id="course_category_id" name="course_category_id" class="form-control @error('course_category_id') is-invalid @enderror" required>
            <option value="">Pilih category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('course_category_id', $course->course_category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('course_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 form-group">
        <label for="age_min">Usia Min.</label><input id="age_min" type="number" min="3" max="99" name="age_min" class="form-control" value="{{ old('age_min', $course->age_min ?? '') }}">
    </div>
    <div class="col-md-2 form-group">
        <label for="age_max">Usia Maks.</label><input id="age_max" type="number" min="3" max="99" name="age_max" class="form-control" value="{{ old('age_max', $course->age_max ?? '') }}">
    </div>
    <div class="col-md-2 form-group">
        <label for="sort_order">Urutan</label><input id="sort_order" type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $course->sort_order ?? 0) }}" required>
    </div>
</div>
<div class="form-group"><label for="description">Deskripsi</label><textarea id="description" name="description" rows="4" maxlength="2000" class="form-control">{{ old('description', $course->description ?? '') }}</textarea></div>
<div class="form-group"><label for="is_active">Status</label><select id="is_active" name="is_active" class="form-control" required><option value="1" @selected((string) old('is_active', isset($course) ? (int) $course->is_active : 1) === '1')>Aktif</option><option value="0" @selected((string) old('is_active', isset($course) ? (int) $course->is_active : 1) === '0')>Nonaktif</option></select></div>
<div class="form-group" id="teachers">
    <label for="teacher_ids">Guru Pengajar</label>
    @php($selectedTeachers = collect(old('teacher_ids', isset($course) ? $course->teachers->pluck('id')->all() : []))->map(fn($id) => (string) $id)->all())
    <select id="teacher_ids" name="teacher_ids[]" class="form-control @error('teacher_ids') is-invalid @enderror" multiple size="{{ min(max($teachers->count(), 3), 7) }}">
        @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}" @selected(in_array((string) $teacher->id, $selectedTeachers, true))>
                {{ $teacher->active_teaching_courses_count === 0 ? '[BELUM MENGAJAR]' : '[MENGAJAR '.$teacher->active_teaching_courses_count.' COURSE]' }} {{ $teacher->name }} — {{ $teacher->email }} · Spesialisasi: {{ $teacher->teachingCategories->pluck('name')->join(', ') ?: 'Belum dipilih' }}{{ $teacher->is_active ? '' : ' (Akun Nonaktif)' }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">Guru yang belum mengajar otomatis ditampilkan paling atas. Tahan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu guru.</small>
    @if($teachers->isNotEmpty())
        <div class="mt-2">
            <span class="badge badge-success mr-1">{{ $teachers->where('active_teaching_courses_count', 0)->count() }} belum mengajar</span>
            <span class="badge badge-info">{{ $teachers->where('active_teaching_courses_count', '>', 0)->count() }} sedang mengajar</span>
        </div>
    @endif
    @if($teachers->isEmpty())<small class="form-text text-warning">Belum ada user dengan role guru. Tambahkan melalui Management User.</small>@endif
    @error('teacher_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="d-flex justify-content-end mt-4"><a href="{{ route('courses.index') }}" class="btn btn-light mr-2">Batal</a><button class="btn btn-primary" type="submit"><i class="ti-save mr-1"></i> Simpan Course</button></div>
