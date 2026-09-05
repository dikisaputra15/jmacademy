@extends('layouts.app')

@section('title', 'Trial Schedules')

@push('style')
<style>
    .trial-form-card,.trial-list-card{border:0;border-radius:12px}.trial-table{margin-bottom:0;min-width:1000px}.trial-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.trial-table tbody td{border-color:#edf0f3;padding:15px 16px;vertical-align:middle}.date-box{background:#f39c3d;border-radius:8px;color:#fff;display:inline-block;min-width:58px;padding:7px;text-align:center}.date-box strong{display:block;font-size:18px;line-height:18px}.trial-pill{border-radius:20px;font-size:11px;padding:6px 10px}.one-trial-note{background:#fff7e9;border:1px solid #f5d7a9;border-radius:9px;color:#8a5a15;padding:12px 15px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div><h3 class="font-weight-bold mb-1">Trial Schedules</h3><p class="text-muted mb-0">Buat jadwal kelas percobaan untuk student baru.</p></div>
    <span class="badge badge-warning px-3 py-2">1 trial per student</span>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Jadwal trial belum dapat dibuat.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card trial-form-card mb-4"><div class="card-body p-4">
    <h5 class="font-weight-bold mb-1">Buat Jadwal Trial</h5>
    <p class="text-muted mb-3">Guru mengikuti penugasan pada course. Admin menentukan student, materi, waktu, dan link Zoom.</p>
    <div class="one-trial-note mb-4"><i class="ti-info-alt mr-1"></i><strong>Ketentuan:</strong> setiap student hanya dapat mengikuti trial satu kali untuk seluruh course.</div>
    @if($students->isEmpty())
        <div class="alert alert-info mb-0">Tidak ada student yang tersedia. Semua student saat ini sudah pernah mendapat jadwal trial.</div>
    @else
        <form method="POST" action="{{ route('trial-schedules.store') }}" id="trial-form">@csrf
            <div class="row">
                <div class="col-md-6 form-group"><label for="student_id">Student <span class="text-danger">*</span></label><select id="student_id" name="student_id" class="form-control" required><option value="">Pilih student</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected((int)old('student_id')===$student->id)>{{ $student->name }} · {{ $student->email }}</option>@endforeach</select></div>
                <div class="col-md-6 form-group"><label for="course_id">Course <span class="text-danger">*</span></label><select id="course_id" name="course_id" class="form-control" required><option value="">Pilih course</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((int)old('course_id')===$course->id)>{{ $course->name }} · {{ $course->code }}</option>@endforeach</select></div>
            </div>
            <div id="teacher-info" class="alert alert-light border d-none"></div>
            <div class="row">
                <div class="col-md-6 form-group"><label for="curriculum_lesson_id">Materi Trial</label><select id="curriculum_lesson_id" name="curriculum_lesson_id" class="form-control"><option value="">Pilih course terlebih dahulu</option></select></div>
                <div class="col-md-3 form-group"><label for="training_date">Tanggal <span class="text-danger">*</span></label><input id="training_date" type="date" name="training_date" value="{{ old('training_date') }}" min="{{ today()->toDateString() }}" class="form-control" required></div>
                <div class="col-md-3 form-group"><label for="zoom_url">Link Zoom <span class="text-danger">*</span></label><input id="zoom_url" type="url" name="zoom_url" value="{{ old('zoom_url') }}" class="form-control" placeholder="https://zoom.us/j/..." required></div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group"><label for="start_time">Jam Mulai <span class="text-danger">*</span></label><input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}" class="form-control" required></div>
                <div class="col-md-3 form-group"><label for="end_time">Jam Selesai <span class="text-danger">*</span></label><input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}" class="form-control" required></div>
                <div class="col-md-6 form-group"><label for="notes">Catatan</label><input id="notes" name="notes" value="{{ old('notes') }}" maxlength="1000" class="form-control" placeholder="Catatan untuk guru dan student"></div>
            </div>
            <div class="text-right"><button class="btn btn-warning px-4"><i class="ti-calendar mr-1"></i>Simpan Jadwal Trial</button></div>
        </form>
    @endif
</div></div>

<div class="card trial-list-card">
    <div class="card-body border-bottom"><div class="d-flex flex-wrap justify-content-between align-items-end"><div><h5 class="font-weight-bold mb-1">Daftar Jadwal Trial</h5><small class="text-muted">Setiap student hanya muncul satu kali.</small></div><form method="GET" action="{{ route('trial-schedules.index') }}" class="form-inline mt-3 mt-md-0"><input name="search" value="{{ $search }}" class="form-control form-control-sm mr-2" placeholder="Student, course, atau guru"><button class="btn btn-sm btn-outline-primary">Cari</button>@if($search)<a href="{{ route('trial-schedules.index') }}" class="btn btn-sm btn-light ml-1">Reset</a>@endif</form></div></div>
    @if($schedules->isNotEmpty())
        <div class="table-responsive"><table class="table trial-table"><thead><tr><th>Tanggal</th><th>Waktu</th><th>Student</th><th>Course / Materi</th><th>Guru</th><th>Zoom</th><th>Catatan</th></tr></thead><tbody>
            @foreach($schedules as $schedule)<tr>
                <td><div class="date-box"><strong>{{ $schedule->training_date->format('d') }}</strong><small>{{ strtoupper($schedule->training_date->translatedFormat('M')) }}</small></div><small class="text-muted d-block mt-1">{{ $schedule->training_date->format('Y') }}</small></td>
                <td><strong>{{ substr($schedule->start_time,0,5) }}–{{ substr($schedule->end_time,0,5) }}</strong><small class="text-muted d-block">WIB</small></td>
                <td><strong class="d-block">{{ $schedule->student->name }}</strong><small class="text-muted">{{ $schedule->student->email }}</small></td>
                <td><strong class="d-block">{{ $schedule->course->name }}</strong>@if($schedule->lesson)<small class="text-warning d-block">{{ $schedule->lesson->section->name }} · {{ $schedule->lesson->title }}</small>@endif<small class="text-muted">{{ $schedule->course->category->name }} · {{ $schedule->course->code }}</small></td>
                <td><span class="badge badge-info trial-pill"><i class="ti-user mr-1"></i>{{ $schedule->teacher->name }}</span></td>
                <td><a href="{{ $schedule->zoom_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-warning"><i class="ti-video-camera mr-1"></i>Buka Zoom</a></td>
                <td><span class="small text-muted">{{ $schedule->notes ?: '—' }}</span></td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="card-footer bg-white">@if($schedules->hasPages()){{ $schedules->links('pagination::bootstrap-4') }}@endif</div>
    @else<div class="card-body text-center py-5"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Belum ada jadwal trial</h5><p class="text-muted mb-0">Buat jadwal trial pertama melalui form di atas.</p></div>@endif
</div>
@endsection

@push('scripts')
<script>
(function(){
    const course=document.getElementById('course_id'); if(!course)return;
    const lesson=document.getElementById('curriculum_lesson_id');
    const teacher=document.getElementById('teacher-info');
    const options=@json($courseOptions); const oldLesson='{{ old('curriculum_lesson_id') }}';
    function render(){const selected=options[course.value]; lesson.innerHTML='<option value="">Tanpa materi khusus</option>'; teacher.classList.add('d-none'); if(!selected)return; if(selected.teacher){teacher.innerHTML='<strong>Guru pengajar:</strong> '+selected.teacher.name; teacher.classList.remove('d-none')}else{teacher.innerHTML='<span class="text-warning">Course belum memiliki guru pengajar.</span>';teacher.classList.remove('d-none')} selected.lessons.forEach(item=>{const option=document.createElement('option');option.value=item.id;option.textContent=item.name;option.selected=String(item.id)===oldLesson;lesson.appendChild(option)})}
    course.addEventListener('change',render);render();
})();
</script>
@endpush
