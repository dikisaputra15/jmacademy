<div class="card mb-4"><div class="card-body">
    <h4>Tugas Admin: Pembagian Kelas</h4>
    <p class="text-muted">Siswa dengan pembayaran diterima dikelompokkan menurut course dan kategori kelas. Pilih guru dan jadwal setelah kapasitas penuh.</p>
    @forelse($waitingGroups as $members)
        @php
            $first = $members->first();
            $capacity = $first->classCategory->capacity;
            $ready = $members->count() === $capacity;
            $restore = (string) old('group_key') === (string) $first->id;
        @endphp
        <div class="border rounded p-3 mb-3">
            <h5>{{ $first->course->name }} · {{ $first->classCategory->name }}</h5>
            <span class="badge badge-{{ $ready ? 'success' : 'warning' }}">{{ $members->count() }}/{{ $capacity }} siswa · {{ $ready ? 'Siap dijadwalkan' : 'Menunggu siswa' }}</span>
            <ul class="mt-2">@foreach($members as $member)<li>{{ $member->student->name }} ({{ $member->student->email }}) · TRX-{{ $member->id }}</li>@endforeach</ul>
            @if($ready)
                <details @if($restore) open @endif><summary class="text-primary mb-3">Pilih Guru dan Jadwal Kelompok</summary>
                <form method="POST" action="{{ route('student-registrations.group-schedules') }}">
                    @csrf
                    <input type="hidden" name="group_key" value="{{ $first->id }}">
                    @foreach($members as $member)<input type="hidden" name="transaction_ids[]" value="{{ $member->id }}">@endforeach
                    <div class="form-group"><label>Guru pengajar</label><select name="teacher_id" class="form-control" required><option value="">Pilih guru aktif</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($restore && (int) old('teacher_id') === $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></div>
                    @php($slotIndex = 0)
                    @foreach($first->course->sections as $section)
                        @foreach($section->lessons as $lesson)
                            @for($meeting = 1; $meeting <= $lesson->meetings; $meeting++)
                                <div class="border rounded p-3 mb-2">
                                    <strong>{{ $section->name }} · {{ $lesson->title }} · Pertemuan {{ $meeting }}</strong>
                                    <input type="hidden" name="schedules[{{ $slotIndex }}][lesson_id]" value="{{ $lesson->id }}">
                                    <input type="hidden" name="schedules[{{ $slotIndex }}][meeting_number]" value="{{ $meeting }}">
                                    <div class="row mt-2">
                                    @foreach(['training_date' => ['Tanggal', 'date'], 'start_time' => ['Mulai (WIB)', 'time'], 'end_time' => ['Selesai (WIB)', 'time'], 'zoom_url' => ['Link Zoom', 'url']] as $field => [$label, $type])
                                        <div class="col-md-3 form-group"><label>{{ $label }}<input class="form-control" type="{{ $type }}" name="schedules[{{ $slotIndex }}][{{ $field }}]" value="{{ $restore ? old('schedules.'.$slotIndex.'.'.$field) : '' }}" @if($type === 'date') min="{{ now()->toDateString() }}" @endif required></label></div>
                                    @endforeach
                                    </div>
                                </div>
                                @php($slotIndex++)
                            @endfor
                        @endforeach
                    @endforeach
                    @if($slotIndex > 0)<button class="btn btn-primary">Simpan Guru dan Jadwal untuk {{ $members->count() }} Siswa</button>@else<p class="text-warning">Tambahkan kurikulum course sebelum membuat jadwal.</p>@endif
                </form></details>
            @else
                <small class="text-muted">Membutuhkan {{ $capacity - $members->count() }} siswa lagi.</small>
            @endif
        </div>
    @empty
        <p class="text-muted mb-0">Belum ada siswa terverifikasi yang menunggu pembagian kelas.</p>
    @endforelse
</div></div>
