@extends('layouts.app')

@section('title', 'Paid Schedules')

@push('style')
<style>
    .schedule-form-card,.schedule-list-card{border:0;border-radius:12px}.transaction-option{font-size:13px}.slot-row{align-items:end;background:#f7f8fb;border:1px solid #e8ebf1;border-radius:9px;margin-bottom:10px;padding:12px}.schedule-table{margin-bottom:0;min-width:1000px}.schedule-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 16px;text-transform:uppercase}.schedule-table tbody td{border-color:#edf0f3;padding:15px 16px;vertical-align:middle}.date-box{background:#4b49ac;border-radius:8px;color:#fff;display:inline-block;min-width:58px;padding:7px;text-align:center}.date-box strong{display:block;font-size:18px;line-height:18px}.meeting-pill{border-radius:20px;font-size:11px;padding:6px 10px}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div><h3 class="font-weight-bold mb-1">Paid Schedules</h3><p class="text-muted mb-0">Buat jadwal training untuk pembayaran student yang sudah diverifikasi.</p></div>
    <span class="badge badge-success px-3 py-2">{{ $paidTransactions->count() }} pendaftaran terverifikasi</span>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Jadwal belum dapat dibuat.</strong><ul class="mb-0 mt-2 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card schedule-form-card mb-4">
    <div class="card-body p-4">
        <h5 class="font-weight-bold mb-1">Buat Jadwal Training</h5>
        <p class="text-muted mb-4">Materi dan guru mengikuti susunan course. Admin hanya menentukan waktu training dan link Zoom.</p>

        @if($paidTransactions->isEmpty())
            <div class="alert alert-info mb-0"><i class="ti-info-alt mr-1"></i> Belum ada pembayaran terverifikasi. Verifikasi pembayaran melalui menu Student Register terlebih dahulu.</div>
        @else
            <form method="POST" action="{{ route('paid-schedules.store') }}" id="schedule-form">
                @csrf
                <div class="row">
                    <div class="col-12 form-group">
                        <label for="course_transaction_id">Student dan Course <span class="text-danger">*</span></label>
                        <select id="course_transaction_id" name="course_transaction_id" class="form-control @error('course_transaction_id') is-invalid @enderror" required>
                            <option value="">Pilih pendaftaran terverifikasi</option>
                            @foreach($paidTransactions as $transaction)
                                @php($meetingTarget = $transaction->course->sections->sum(fn($section) => $section->lessons->sum('meetings')))
                                <option value="{{ $transaction->id }}" @selected((int)old('course_transaction_id') === $transaction->id)>
                                    TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }} · {{ $transaction->student->name }} · {{ $transaction->course->name }} ({{ $transaction->paid_schedules_count }}/{{ $meetingTarget }} jadwal)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="course-assignment" class="alert alert-light border d-none"></div>
                <div class="mb-2"><label class="mb-0 font-weight-bold">Materi dan Jadwal Training</label><small class="text-muted d-block">Isi jadwal pada pertemuan kurikulum yang belum dijadwalkan.</small></div>
                <div id="schedule-slots"><div class="text-muted text-center border rounded py-4">Pilih student dan course terlebih dahulu.</div></div>
                <div class="form-group mt-3"><label for="notes">Catatan</label><textarea id="notes" name="notes" class="form-control" rows="2" maxlength="1000" placeholder="Contoh: training online melalui Zoom, materi yang perlu dipersiapkan, dan lainnya">{{ old('notes') }}</textarea></div>
                <div class="text-right"><button class="btn btn-primary px-4"><i class="ti-calendar mr-1"></i> Simpan Jadwal</button></div>
            </form>
        @endif
    </div>
</div>

<div class="card schedule-list-card">
    <div class="card-body border-bottom"><div class="d-flex flex-wrap justify-content-between align-items-end"><div><h5 class="font-weight-bold mb-1">Daftar Jadwal Training</h5><small class="text-muted">Jadwal diurutkan berdasarkan tanggal terdekat.</small></div><form method="GET" action="{{ route('paid-schedules.index') }}" class="form-inline mt-3 mt-md-0"><input name="search" value="{{ $search }}" class="form-control form-control-sm mr-2" placeholder="Student, course, atau guru"><button class="btn btn-sm btn-outline-primary">Cari</button>@if($search)<a href="{{ route('paid-schedules.index') }}" class="btn btn-sm btn-light ml-1">Reset</a>@endif</form></div></div>
    @if($schedules->isNotEmpty())
        <div class="table-responsive"><table class="table schedule-table"><thead><tr><th>Tanggal</th><th>Waktu</th><th>Student</th><th>Course / Materi</th><th>Guru</th><th>Zoom</th><th>Catatan</th></tr></thead><tbody>
            @foreach($schedules as $schedule)
                <tr>
                    <td><div class="date-box"><strong>{{ $schedule->training_date->format('d') }}</strong><small>{{ strtoupper($schedule->training_date->translatedFormat('M')) }}</small></div><small class="text-muted d-block mt-1">{{ $schedule->training_date->format('Y') }}</small></td>
                    <td><strong>{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}</strong><small class="text-muted d-block">WIB</small></td>
                    <td><strong class="d-block">{{ $schedule->transaction->student->name }}</strong><small class="text-muted">{{ $schedule->transaction->student->email }}</small></td>
                    <td><strong class="d-block">{{ $schedule->transaction->course->name }}</strong>@if($schedule->lesson)<small class="text-primary d-block">{{ $schedule->lesson->section->name }} · {{ $schedule->lesson->title }} (Pertemuan {{ $schedule->meeting_number }})</small>@endif<small class="text-muted">{{ $schedule->transaction->course->category->name }} · {{ $schedule->transaction->course->code }}</small></td>
                    <td><span class="badge badge-info meeting-pill"><i class="ti-user mr-1"></i>{{ $schedule->teacher->name }}</span></td>
                    <td>@if($schedule->zoom_url)<a href="{{ $schedule->zoom_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="ti-video-camera mr-1"></i> Buka Zoom</a>@else<span class="text-muted">—</span>@endif</td>
                    <td><span class="small text-muted">{{ $schedule->notes ?: '—' }}</span></td>
                </tr>
            @endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }} dari {{ $schedules->total() }} jadwal</small>@if($schedules->hasPages()){{ $schedules->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Belum ada jadwal training</h5><p class="text-muted mb-0">Pilih pendaftaran terverifikasi di atas untuk membuat jadwal pertama.</p></div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const transactionSelect = document.getElementById('course_transaction_id');
    if (!transactionSelect) return;
    const slots = document.getElementById('schedule-slots');
    const assignment = document.getElementById('course-assignment');
    const options = @json($scheduleOptions);
    const oldSlots = @json(old('schedules', []));

    function escapeHtml(value) {
        const div = document.createElement('div'); div.textContent = value || ''; return div.innerHTML;
    }

    function renderSchedule() {
        const option = options[transactionSelect.value];
        slots.innerHTML = '';
        assignment.classList.add('d-none');
        if (!option) {
            slots.innerHTML = '<div class="text-muted text-center border rounded py-4">Pilih student dan course terlebih dahulu.</div>';
            return;
        }
        if (!option.teacher) {
            slots.innerHTML = '<div class="alert alert-warning mb-0">Course ini belum memiliki guru. Tetapkan guru pada menu Course terlebih dahulu.</div>';
            return;
        }
        assignment.innerHTML = '<strong>Guru:</strong> '+escapeHtml(option.teacher.name)+' <span class="mx-2">·</span> <strong>Progress:</strong> '+option.scheduled+'/'+option.total+' pertemuan dijadwalkan';
        assignment.classList.remove('d-none');
        if (!option.slots.length) {
            slots.innerHTML = '<div class="alert alert-success mb-0"><i class="ti-check mr-1"></i> Semua materi pada course ini sudah memiliki jadwal.</div>';
            return;
        }
        option.slots.forEach(function (slot, index) {
            const previous = oldSlots.find(item => String(item.lesson_id) === String(slot.lesson_id) && String(item.meeting_number) === String(slot.meeting_number)) || {};
            const row = document.createElement('div'); row.className = 'slot-row';
            row.innerHTML = '<input type="hidden" name="schedules['+index+'][lesson_id]" value="'+slot.lesson_id+'"><input type="hidden" name="schedules['+index+'][meeting_number]" value="'+slot.meeting_number+'"><div class="mb-2"><strong>'+escapeHtml(slot.section)+'</strong> <span class="text-muted">·</span> '+escapeHtml(slot.lesson)+' <span class="badge badge-primary ml-1">Pertemuan '+slot.meeting_number+'</span></div><div class="row"><div class="col-lg-3 form-group mb-lg-0"><label class="small">Tanggal <span class="text-danger">*</span></label><input type="date" name="schedules['+index+'][training_date]" value="'+escapeHtml(previous.training_date)+'" min="{{ now()->toDateString() }}" class="form-control" required></div><div class="col-lg-2 form-group mb-lg-0"><label class="small">Jam Mulai <span class="text-danger">*</span></label><input type="time" name="schedules['+index+'][start_time]" value="'+escapeHtml(previous.start_time)+'" class="form-control" required></div><div class="col-lg-2 form-group mb-lg-0"><label class="small">Jam Selesai <span class="text-danger">*</span></label><input type="time" name="schedules['+index+'][end_time]" value="'+escapeHtml(previous.end_time)+'" class="form-control" required></div><div class="col-lg-5 form-group mb-0"><label class="small">Link Zoom <span class="text-danger">*</span></label><input type="url" name="schedules['+index+'][zoom_url]" value="'+escapeHtml(previous.zoom_url)+'" class="form-control" placeholder="https://zoom.us/j/..." required></div></div>';
            slots.appendChild(row);
        });
    }

    transactionSelect.addEventListener('change', function () { renderSchedule(); });
    renderSchedule();
})();
</script>
@endpush
