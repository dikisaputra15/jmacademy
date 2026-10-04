@php($restoreInput = (string) old('form_category') === $formKey)
<div class="form-group">
    <label for="name-{{ $formKey }}">Nama kategori</label>
    <input id="name-{{ $formKey }}" name="name" class="form-control" maxlength="255" value="{{ $restoreInput ? old('name') : $category?->name }}" required>
</div>
<div class="form-group">
    <label for="capacity-{{ $formKey }}">Kapasitas siswa per kelas</label>
    <input id="capacity-{{ $formKey }}" type="number" name="capacity" min="1" max="255" step="1" class="form-control" value="{{ $restoreInput ? old('capacity') : $category?->capacity }}" required>
</div>
<div class="form-group">
    <label for="fee-{{ $formKey }}">Biaya kategori kelas (Rp)</label>
    <input id="fee-{{ $formKey }}" type="number" name="fee_per_meeting" min="0" max="999999999" step="1" class="form-control" value="{{ $restoreInput ? old('fee_per_meeting') : $category?->fee_per_meeting }}" placeholder="Belum ditentukan" required>
</div>
