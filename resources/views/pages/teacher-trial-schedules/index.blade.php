@extends('layouts.app')

@section('title', 'Jadwal Trial Saya')

@push('style')
<style>
    .schedule-summary,.calendar-card{border:0;border-radius:12px}.summary-icon{align-items:center;border-radius:10px;display:flex;font-size:20px;height:44px;justify-content:center;width:44px}.teacher-schedule-card{border:1px solid #e3e8ef;border-radius:12px;overflow:hidden;transition:.2s}.teacher-schedule-card:hover{box-shadow:0 8px 22px rgba(243,156,61,.13);transform:translateY(-2px)}.schedule-date{background:linear-gradient(135deg,#e98c29,#f5b45f);color:#fff;padding:16px;text-align:center;width:92px}.schedule-date strong{display:block;font-size:28px;line-height:30px}.schedule-date span{font-size:12px;font-weight:700;text-transform:uppercase}.schedule-content{padding:18px}.lesson-label{color:#d77a16;font-size:12px;font-weight:700}.meta-row{color:#6c757d;font-size:13px}.past-card{opacity:.76}.calendar-scroll{max-height:620px;overflow:auto}.weekly-table{border-collapse:separate;border-spacing:0;min-width:1050px;table-layout:fixed;width:100%}.weekly-table th,.weekly-table td{border-bottom:1px solid #e2e5eb;border-right:1px solid #e2e5eb;height:54px;padding:0}.weekly-table th:first-child,.weekly-table td:first-child{border-left:1px solid #e2e5eb}.weekly-table thead th{background:#f5f5f5;border-top:1px solid #e2e5eb;position:sticky;text-align:center;top:0;z-index:4}.weekly-table thead th:first-child{width:88px}.calendar-day{display:block;font-size:13px;font-weight:700}.calendar-date{color:#8b94a2;display:block;font-size:10px}.calendar-today{background:#fff3e5!important;color:#d77a16}.calendar-time{background:#fafafa;color:#737b87;font-size:12px;padding-top:7px!important;text-align:center;vertical-align:top}.calendar-slot{position:relative}.calendar-event{background:#f09a3c;color:#fff;height:100%;left:1px;overflow:hidden;padding:5px 7px;position:absolute;top:0;width:calc(100% - 2px);z-index:2}.calendar-event strong,.calendar-event small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.calendar-event strong{font-size:10px}.calendar-event small{font-size:9px}.calendar-event.continued{border-top:1px solid rgba(255,255,255,.4)}.calendar-event.past{background:#aaa}.calendar-week-title{font-weight:700;min-width:210px;text-align:center}
    .calendar-event{background:#389bd7}.calendar-today{background:#edecff!important;color:#4b49ac}.schedule-list-card{border:0;border-radius:12px}.schedule-table{margin-bottom:0;min-width:1150px}.schedule-table thead th{background:#f5f6fa;border:0;color:#6c757d;font-size:11px;font-weight:700;letter-spacing:.04em;padding:14px 15px;text-transform:uppercase;white-space:nowrap}.schedule-table tbody td{border-color:#edf0f3;padding:14px 15px;vertical-align:middle}.table-date{background:#4b49ac;border-radius:8px;color:#fff;display:inline-block;min-width:58px;padding:7px;text-align:center}.table-date strong{display:block;font-size:18px;line-height:18px}.status-pill{border-radius:20px;font-size:11px;padding:6px 10px}.table-note{display:block;max-width:210px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.zoom-button{white-space:nowrap}
</style>
@endpush

@section('main')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h3 class="font-weight-bold mb-1">Trial Schedules</h3><p class="text-muted mb-0">Jadwal kelas trial yang diberikan kepada Anda.</p></div><span class="badge badge-warning px-3 py-2">{{ $counts['upcoming'] }} trial mendatang</span></div>
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="card calendar-card mb-4"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h5 class="font-weight-bold mb-1">Kalender Trial Mingguan</h5><small class="text-muted">Jadwal trial ditandai dengan warna biru.</small></div><div class="d-flex align-items-center mt-3 mt-md-0"><a class="btn btn-sm btn-outline-primary" href="{{ route('teacher-trial-schedules.index',['week'=>$weekStart->copy()->subWeek()->toDateString(),'filter'=>$filter,'search'=>$search?:null]) }}"><i class="ti-angle-left"></i></a><div class="calendar-week-title mx-2">{{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}</div><a class="btn btn-sm btn-outline-primary" href="{{ route('teacher-trial-schedules.index',['week'=>$weekStart->copy()->addWeek()->toDateString(),'filter'=>$filter,'search'=>$search?:null]) }}"><i class="ti-angle-right"></i></a>@unless($weekStart->isSameDay(today()->startOfWeek()))<a class="btn btn-sm btn-light ml-2" href="{{ route('teacher-trial-schedules.index',['filter'=>$filter]) }}">Hari Ini</a>@endunless</div></div>
    <div class="calendar-scroll"><table class="weekly-table"><thead><tr><th>Waktu</th>@foreach($days as $day)<th class="{{ $day->isToday()?'calendar-today':'' }}"><span class="calendar-day">{{ $day->translatedFormat('l') }}</span><span class="calendar-date">{{ $day->translatedFormat('d M') }}</span></th>@endforeach</tr></thead><tbody>
        @foreach($timeSlots as $slot) @php $slotMinutes=((int)substr($slot,0,2)*60)+(int)substr($slot,3,2); @endphp
            <tr><td class="calendar-time">{{ $slot }}</td>@foreach($days as $day)
                @php
                    $event=$calendarSchedules->first(function($item)use($day,$slotMinutes){$start=((int)substr($item->start_time,0,2)*60)+(int)substr($item->start_time,3,2);$end=((int)substr($item->end_time,0,2)*60)+(int)substr($item->end_time,3,2);return $item->training_date->isSameDay($day)&&$start<=$slotMinutes&&$end>$slotMinutes;});
                    $eventStart=$event?((int)substr($event->start_time,0,2)*60)+(int)substr($event->start_time,3,2):null;$startsHere=$event&&$slotMinutes===(intdiv($eventStart+29,30)*30);
                @endphp
                <td class="calendar-slot">@if($event)<div class="calendar-event {{ !$startsHere?'continued':'' }} {{ $event->training_date->isBefore(today())?'past':'' }}" title="{{ $event->course->name }} · {{ $event->student->name }}">@if($startsHere)<strong>{{ $event->course->name }}</strong><small>{{ substr($event->start_time,0,5) }}–{{ substr($event->end_time,0,5) }} · {{ $event->student->name }}</small>@endif</div>@endif</td>
            @endforeach</tr>
        @endforeach
    </tbody></table></div>
    @if($calendarSchedules->isEmpty())<div class="text-center text-muted py-4"><i class="ti-calendar d-block h4"></i>Belum ada trial pada minggu ini.</div>@endif
</div></div>

<div class="row mb-4">@foreach([['upcoming','Mendatang','warning','ti-calendar'],['today','Hari Ini','success','ti-alarm-clock'],['past','Selesai','secondary','ti-check-box']] as [$key,$label,$color,$icon])<div class="col-md-4 mb-3 mb-md-0"><a href="{{ route('teacher-trial-schedules.index',['filter'=>$key]) }}" class="text-decoration-none text-dark"><div class="card schedule-summary {{ $filter===$key?'border border-'.$color:'' }}"><div class="card-body d-flex align-items-center"><div class="summary-icon bg-{{ $color }} text-white mr-3"><i class="{{ $icon }}"></i></div><div><div class="h4 mb-0">{{ $counts[$key] }}</div><small class="text-muted">{{ $label }}</small></div></div></div></a></div>@endforeach</div>

<div class="card border-0 mb-4"><div class="card-body"><form method="GET" action="{{ route('teacher-trial-schedules.index') }}" class="row align-items-end"><input type="hidden" name="filter" value="{{ $filter }}"><div class="col-md-9 form-group mb-md-0"><label for="search">Cari Jadwal</label><input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama student, course, kode course, atau materi"></div><div class="col-md-3"><button class="btn btn-warning btn-block"><i class="ti-search mr-1"></i>Cari</button></div></form><div class="mt-3">@foreach(['upcoming'=>'Mendatang','today'=>'Hari Ini','past'=>'Selesai','all'=>'Semua'] as $key=>$label)<a href="{{ route('teacher-trial-schedules.index',['filter'=>$key,'search'=>$search?:null]) }}" class="btn btn-sm {{ $filter===$key?'btn-warning':'btn-outline-secondary' }} mr-1">{{ $label }}</a>@endforeach</div></div></div>

<div class="card schedule-list-card">
    <div class="card-body border-bottom"><h5 class="font-weight-bold mb-1">Daftar Trial Schedules</h5><small class="text-muted">Menampilkan maksimal 12 jadwal per halaman.</small></div>
    @if($schedules->isNotEmpty())
        <div class="table-responsive"><table class="table schedule-table"><thead><tr><th>Tanggal</th><th>Waktu</th><th>Course</th><th>Materi</th><th>Student</th><th>Status</th><th>Zoom</th><th>Catatan</th></tr></thead><tbody>
            @foreach($schedules as $schedule)
                @php $isPast=$schedule->hasEnded();$canJoin=$schedule->canJoinZoom(); @endphp
                <tr>
                    <td><div class="table-date"><strong>{{ $schedule->training_date->format('d') }}</strong><small>{{ strtoupper($schedule->training_date->translatedFormat('M')) }}</small></div><small class="text-muted d-block mt-1">{{ $schedule->training_date->format('Y') }}</small></td>
                    <td><strong>{{ substr($schedule->start_time,0,5) }}–{{ substr($schedule->end_time,0,5) }}</strong><small class="text-muted d-block">WIB</small></td>
                    <td><strong class="d-block">{{ $schedule->course->name }}</strong><small class="text-muted">{{ $schedule->course->code }}</small></td>
                    <td><strong class="d-block">{{ $schedule->lesson?->title ?? '—' }}</strong><small class="text-primary">{{ $schedule->lesson?->section?->name ?? 'Pengenalan' }} · Trial</small></td>
                    <td><strong class="d-block">{{ $schedule->student->name }}</strong><small class="text-muted">{{ $schedule->student->email }}</small></td>
                    <td>@if($isPast)<span class="badge badge-secondary status-pill">Selesai</span>@elseif($schedule->training_date->isToday())<span class="badge badge-success status-pill">Hari Ini</span>@else<span class="badge badge-primary status-pill">Terjadwal</span>@endif</td>
                    <td>@if($canJoin)<a href="{{ route('teacher-trial-schedules.join',$schedule) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary zoom-button"><i class="ti-video-camera mr-1"></i>Masuk Zoom</a>@elseif($isPast)<button class="btn btn-sm btn-secondary zoom-button" disabled><i class="ti-check mr-1"></i>Jadwal Selesai</button>@elseif($schedule->zoom_url)<button class="btn btn-sm btn-secondary zoom-button" disabled title="Aktif pada {{ $schedule->training_date->translatedFormat('d F Y') }}"><i class="ti-lock mr-1"></i>Zoom Belum Aktif</button>@else<span class="text-warning small">Belum tersedia</span>@endif</td>
                    <td><span class="small text-muted table-note" title="{{ $schedule->notes }}">{{ $schedule->notes ?: '—' }}</span></td>
                </tr>
            @endforeach
        </tbody></table></div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center"><small class="text-muted">Menampilkan {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }} dari {{ $schedules->total() }} jadwal</small>@if($schedules->hasPages()){{ $schedules->links('pagination::bootstrap-4') }}@endif</div>
    @else
        <div class="card-body text-center py-5"><i class="ti-calendar h2 text-muted"></i><h5 class="mt-3">Tidak ada jadwal trial</h5><p class="text-muted mb-0">Belum ada trial yang sesuai dengan filter ini.</p></div>
    @endif
</div>
@endsection
