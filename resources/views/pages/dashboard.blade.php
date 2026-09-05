@extends('layouts.app')

@section('title', 'Dashboard')

@push('style')
<style>
    .overview-heading{font-size:26px}.teacher-stat{border:0;border-radius:14px;height:100%}.stat-icon{align-items:center;border-radius:12px;color:#fff;display:flex;font-size:24px;height:54px;justify-content:center;width:54px}.stat-icon.students{background:linear-gradient(135deg,#4b49ac,#7775db)}.stat-icon.courses{background:linear-gradient(135deg,#43b967,#68cf7f)}.stat-icon.schedules{background:linear-gradient(135deg,#ee9638,#f5b05d)}.stat-icon.teachers{background:linear-gradient(135deg,#308bd2,#58afe9)}.stat-icon.transactions{background:linear-gradient(135deg,#e58c2d,#f5b05d)}.stat-number{font-size:25px;font-weight:700;line-height:27px}.dashboard-schedule{border:1px solid #e1e5ec;border-radius:13px;display:flex;height:100%;min-height:245px;overflow:hidden;transition:box-shadow .2s,transform .2s}.dashboard-schedule:hover{box-shadow:0 9px 25px rgba(75,73,172,.11);transform:translateY(-2px)}.schedule-date{align-items:center;background:linear-gradient(155deg,#4b49ac,#7472d8);color:#fff;display:flex;flex-direction:column;justify-content:center;min-width:105px;padding:18px 12px;text-align:center}.schedule-date span{font-size:12px;font-weight:700;text-transform:uppercase}.schedule-date strong{font-size:32px;line-height:36px}.schedule-body{display:flex;flex:1;flex-direction:column;padding:20px}.lesson-label{color:#4b49ac;font-size:12px;font-weight:700}.lesson-title{font-size:18px}.schedule-meta{color:#717a88;font-size:13px;line-height:1.9}.schedule-meta i{display:inline-block;width:20px}.schedule-status{border-radius:20px;font-size:11px;padding:6px 10px}.zoom-locked{cursor:not-allowed}.nearest-card,.admin-calendar-card{border:0;border-radius:14px}.empty-schedule{padding:55px 20px;text-align:center}.admin-calendar-scroll{max-height:680px;overflow:auto}.admin-calendar{border-collapse:separate;border-spacing:0;min-width:1200px;table-layout:fixed;width:100%}.admin-calendar th,.admin-calendar td{border-bottom:1px solid #e2e5eb;border-right:1px solid #e2e5eb;min-height:68px;padding:0}.admin-calendar th:first-child,.admin-calendar td:first-child{border-left:1px solid #e2e5eb}.admin-calendar thead th{background:#f5f6f8;border-top:1px solid #e2e5eb;height:62px;position:sticky;text-align:center;top:0;z-index:4}.admin-calendar thead th:first-child{width:82px}.admin-calendar-time{background:#fafafa;color:#727b88;font-size:11px;padding-top:8px!important;text-align:center;vertical-align:top}.admin-calendar-cell{height:68px;padding:3px!important;vertical-align:top}.admin-event{border-radius:5px;color:#fff;font-size:9px;line-height:1.35;margin-bottom:3px;padding:5px}.admin-event.paid{background:#3b91cf}.admin-event.trial{background:#ed9a3f}.admin-event strong{display:block;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.admin-day{display:block;font-size:12px;font-weight:700}.admin-date{color:#8a93a1;display:block;font-size:10px}.admin-today{background:#edecff!important;color:#4b49ac}.calendar-week-title{font-weight:700;min-width:210px;text-align:center}.calendar-legend{font-size:11px}.legend-dot{border-radius:50%;display:inline-block;height:9px;margin-right:4px;width:9px}.legend-paid{background:#3b91cf}.legend-trial{background:#ed9a3f}@media(max-width:575px){.dashboard-schedule{min-height:220px}.schedule-date{min-width:82px}.schedule-body{padding:16px}}
</style>
@endpush

@section('main')
@if(auth()->user()->hasRole('guru'))
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h3 class="font-weight-bold overview-heading mb-1">Overview Guru</h3><p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}. Berikut ringkasan kelas Anda.</p></div>
        <a href="{{ route('teacher-paid-schedules.index') }}" class="btn btn-outline-primary mt-3 mt-md-0"><i class="ti-calendar mr-1"></i>Lihat Semua Jadwal</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon students mr-3"><i class="ti-user"></i></div><div><div class="stat-number">{{ $studentCount }}</div><div class="text-muted">Siswa yang Diajar</div></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon courses mr-3"><i class="ti-book"></i></div><div><div class="stat-number">{{ $courseCount }}</div><div class="text-muted">Course Ditugaskan</div></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon schedules mr-3"><i class="ti-calendar"></i></div><div><div class="stat-number">{{ $upcomingCount }}</div><div class="text-muted">Jadwal Mendatang</div></div></div></div></div>
    </div>

    <div class="card nearest-card"><div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h4 class="font-weight-bold mb-1">6 Jadwal Course Terdekat</h4><p class="text-muted mb-0">Diurutkan berdasarkan tanggal dan jam training terdekat.</p></div><span class="badge badge-primary px-3 py-2 mt-2 mt-md-0">{{ $nearestSchedules->count() }} jadwal ditampilkan</span></div>
        <div class="row">
            @forelse($nearestSchedules as $schedule)
                @php $canJoin=$schedule->canJoinZoom(); @endphp
                <div class="col-xl-6 mb-4"><div class="dashboard-schedule">
                    <div class="schedule-date"><span>{{ $schedule->training_date->translatedFormat('M') }}</span><strong>{{ $schedule->training_date->format('d') }}</strong><small>{{ $schedule->training_date->format('Y') }}</small></div>
                    <div class="schedule-body">
                        <div class="d-flex justify-content-between align-items-start mb-2"><div><div class="lesson-label">{{ $schedule->lesson?->section?->name ?? 'Materi' }} · Pertemuan {{ $schedule->meeting_number ?? '-' }}</div><div class="lesson-title font-weight-bold">{{ $schedule->lesson?->title ?? $schedule->transaction->course->name }}</div></div>@if($schedule->training_date->isToday())<span class="badge badge-success schedule-status">Hari Ini</span>@else<span class="badge badge-primary schedule-status">Terjadwal</span>@endif</div>
                        <div class="schedule-meta mb-3"><div><i class="ti-book"></i><strong>{{ $schedule->transaction->course->name }}</strong> · {{ $schedule->transaction->course->code }}</div><div><i class="ti-user"></i>{{ $schedule->transaction->student->name }} · {{ $schedule->transaction->student->email }}</div><div><i class="ti-time"></i>{{ substr($schedule->start_time,0,5) }}–{{ substr($schedule->end_time,0,5) }} WIB</div></div>
                        <div class="mt-auto">@if($canJoin)<a href="{{ route('teacher-paid-schedules.join',$schedule) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary"><i class="ti-video-camera mr-1"></i>Masuk Zoom</a>@elseif($schedule->hasEnded())<button class="btn btn-secondary zoom-locked" disabled><i class="ti-check mr-1"></i>Jadwal Selesai</button>@elseif($schedule->zoom_url)<button class="btn btn-secondary zoom-locked" disabled><i class="ti-lock mr-1"></i>Zoom Belum Aktif</button><small class="text-muted d-block mt-2">Aktif pada {{ $schedule->training_date->translatedFormat('d F Y') }}</small>@else<span class="text-warning small">Link Zoom belum tersedia</span>@endif</div>
                    </div>
                </div></div>
            @empty
                <div class="col-12"><div class="empty-schedule"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Belum ada jadwal mendatang</h5><p class="text-muted mb-0">Jadwal yang dibuat admin akan muncul di sini.</p></div></div>
            @endforelse
        </div>
    </div></div>
@elseif(auth()->user()->hasRole('student'))
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h3 class="font-weight-bold overview-heading mb-1">Overview Student</h3><p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}. Berikut jadwal training terdekat Anda.</p></div>
        <a href="{{ route('student-classes.index') }}" class="btn btn-outline-primary mt-3 mt-md-0"><i class="ti-book mr-1"></i>Lihat My Classes</a>
    </div>

    <div class="card nearest-card"><div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h4 class="font-weight-bold mb-1">4 Jadwal Training Terdekat</h4><p class="text-muted mb-0">Diurutkan berdasarkan tanggal dan jam training terdekat.</p></div><span class="badge badge-primary px-3 py-2 mt-2 mt-md-0">{{ $studentNearestSchedules->count() }} jadwal ditampilkan</span></div>
        <div class="row">
            @forelse($studentNearestSchedules as $schedule)
                @php $canJoin=$schedule->canJoinZoom(); @endphp
                <div class="col-xl-6 mb-4"><div class="dashboard-schedule">
                    <div class="schedule-date"><span>{{ $schedule->training_date->translatedFormat('M') }}</span><strong>{{ $schedule->training_date->format('d') }}</strong><small>{{ $schedule->training_date->format('Y') }}</small></div>
                    <div class="schedule-body">
                        <div class="d-flex justify-content-between align-items-start mb-2"><div><div class="lesson-label">{{ $schedule->lesson?->section?->name ?? 'Materi' }} · Pertemuan {{ $schedule->meeting_number ?? '-' }}</div><div class="lesson-title font-weight-bold">{{ $schedule->lesson?->title ?? $schedule->transaction->course->name }}</div></div>@if($schedule->training_date->isToday())<span class="badge badge-success schedule-status">Hari Ini</span>@else<span class="badge badge-primary schedule-status">Terjadwal</span>@endif</div>
                        <div class="schedule-meta mb-3"><div><i class="ti-book"></i><strong>{{ $schedule->transaction->course->name }}</strong> · {{ $schedule->transaction->course->code }}</div><div><i class="ti-user"></i>Guru: {{ $schedule->teacher?->name ?? 'Belum ditentukan' }}</div><div><i class="ti-time"></i>{{ substr($schedule->start_time,0,5) }}–{{ substr($schedule->end_time,0,5) }} WIB</div></div>
                        <div class="mt-auto">@if($canJoin)<a href="{{ route('student-classes.join',$schedule) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary"><i class="ti-video-camera mr-1"></i>Masuk Zoom</a>@elseif($schedule->hasEnded())<button class="btn btn-secondary zoom-locked" disabled><i class="ti-check mr-1"></i>Jadwal Selesai</button>@elseif($schedule->zoom_url)<button class="btn btn-secondary zoom-locked" disabled><i class="ti-lock mr-1"></i>Zoom Belum Aktif</button><small class="text-muted d-block mt-2">Aktif pada {{ $schedule->training_date->translatedFormat('d F Y') }}</small>@else<span class="text-warning small">Link Zoom belum tersedia</span>@endif</div>
                    </div>
                </div></div>
            @empty
                <div class="col-12"><div class="empty-schedule"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Belum ada jadwal training</h5><p class="text-muted mb-0">Jadwal dari kelas yang sudah dibayar akan muncul di sini.</p></div></div>
            @endforelse
        </div>
    </div></div>
@elseif(auth()->user()->hasRole('admin'))
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold overview-heading mb-1">Overview Admin</h3><p class="text-muted mb-0">Ringkasan pengguna, course, transaksi, dan jadwal seluruh guru.</p></div></div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon teachers mr-3"><i class="ti-user"></i></div><div><div class="stat-number">{{ $teacherCount }}</div><div class="text-muted">Jumlah Guru</div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon students mr-3"><i class="ti-id-badge"></i></div><div><div class="stat-number">{{ $adminStudentCount }}</div><div class="text-muted">Jumlah Siswa</div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon courses mr-3"><i class="ti-book"></i></div><div><div class="stat-number">{{ $activeCourseCount }}</div><div class="text-muted">Course Aktif</div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="card teacher-stat"><div class="card-body d-flex align-items-center"><div class="stat-icon transactions mr-3"><i class="ti-check-box"></i></div><div><div class="stat-number">{{ $successfulTransactionCount }}</div><div class="text-muted">Pembayaran Berhasil</div></div></div></div></div>
    </div>

    <div class="card admin-calendar-card"><div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h4 class="font-weight-bold mb-1">Kalender Jadwal Guru</h4><div class="calendar-legend text-muted"><span class="mr-3"><i class="legend-dot legend-paid"></i>Paid Schedule</span><span><i class="legend-dot legend-trial"></i>Trial Schedule</span></div></div><div class="d-flex align-items-center mt-3 mt-md-0"><a class="btn btn-sm btn-outline-primary" href="{{ route('home',['week'=>$weekStart->copy()->subWeek()->toDateString()]) }}"><i class="ti-angle-left"></i></a><div class="calendar-week-title mx-2">{{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}</div><a class="btn btn-sm btn-outline-primary" href="{{ route('home',['week'=>$weekStart->copy()->addWeek()->toDateString()]) }}"><i class="ti-angle-right"></i></a>@unless($weekStart->isSameDay(today()->startOfWeek()))<a href="{{ route('home') }}" class="btn btn-sm btn-light ml-2">Minggu Ini</a>@endunless</div></div>
        <div class="admin-calendar-scroll"><table class="admin-calendar"><thead><tr><th>Waktu</th>@foreach($adminCalendarDays as $day)<th class="{{ $day->isToday()?'admin-today':'' }}"><span class="admin-day">{{ $day->translatedFormat('l') }}</span><span class="admin-date">{{ $day->translatedFormat('d M') }}</span></th>@endforeach</tr></thead><tbody>
            @foreach($adminTimeSlots as $slot)
                @php $slotMinutes=((int)substr($slot,0,2)*60)+(int)substr($slot,3,2); @endphp
                <tr><td class="admin-calendar-time">{{ $slot }}</td>@foreach($adminCalendarDays as $day)
                    @php $events=$adminCalendarSchedules->filter(function($item)use($day,$slotMinutes){$start=((int)substr($item['start_time'],0,2)*60)+(int)substr($item['start_time'],3,2);return $item['date']->isSameDay($day)&&(intdiv($start,30)*30)===$slotMinutes;}); @endphp
                    <td class="admin-calendar-cell">@foreach($events as $event)<div class="admin-event {{ $event['type'] }}" title="{{ $event['teacher'] }} · {{ $event['course'] }} · {{ $event['student'] }} · {{ $event['start_time'] }}-{{ $event['end_time'] }}"><strong>{{ $event['teacher'] }}</strong>{{ $event['course'] }}<br>{{ $event['start_time'] }}–{{ $event['end_time'] }}</div>@endforeach</td>
                @endforeach</tr>
            @endforeach
        </tbody></table></div>
        @if($adminCalendarSchedules->isEmpty())<div class="text-center text-muted py-4"><i class="ti-calendar d-block h4"></i>Belum ada jadwal guru pada minggu ini.</div>@endif
    </div></div>
@else
    <div class="row"><div class="col-md-12 grid-margin"><h3 class="font-weight-bold">Overview</h3></div></div>
@endif
@endsection
