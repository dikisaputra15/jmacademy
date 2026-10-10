@props(['lesson'])
<form class="p-3 border-bottom" method="POST" action="{{ route('curriculum.lessons.resources', $lesson) }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="resource_lesson_id" value="{{ $lesson->id }}">
    <label for="resources-{{ $lesson->id }}">Link Project dan Modul — {{ $lesson->title }}</label>
    <textarea id="resources-{{ $lesson->id }}" name="resources" class="form-control lesson-resource-editor" rows="5" maxlength="20000" placeholder="Tulis keterangan, link project, dan link modul di sini.">{{ \App\Support\LessonResources::render((string) old('resource_lesson_id') === (string) $lesson->id ? old('resources') : $lesson->resources) }}</textarea>
    <small class="d-block text-muted my-2">Gunakan tombol tautan untuk memasukkan link project atau modul. Keduanya disimpan dalam satu editor.</small>
    <button class="btn btn-sm btn-primary">Simpan Project dan Modul</button>
</form>
@once
@push('scripts')
<script src="{{ asset('vendors/tinymce/tinymce.min.js') }}"></script>
<script>
    tinymce.init({
        selector: 'textarea.lesson-resource-editor',
        height: 280,
        menubar: false,
        branding: false,
        plugins: 'lists link table code fullscreen paste',
        toolbar: 'undo redo | formatselect | bold italic underline removeformat | forecolor backcolor | bullist numlist | alignleft aligncenter alignright alignjustify | table link | fullscreen code',
        default_link_target: '_blank',
        link_assume_external_targets: 'https',
        convert_urls: false,
        paste_data_images: false,
        setup: function (editor) {
            editor.on('change input', function () { editor.save(); });
        }
    });
</script>
@endpush
@endonce
