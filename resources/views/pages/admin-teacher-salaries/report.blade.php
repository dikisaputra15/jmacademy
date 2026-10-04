<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Gaji Guru — {{ $month }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#222;margin:32px;font-size:13px}
        h1{font-size:24px;margin-bottom:8px}h2{font-size:18px;margin-top:30px}
        table{width:100%;border-collapse:collapse;margin:16px 0}th,td{border:1px solid #ddd;padding:9px;text-align:left}th{background:#eee}
        .money{text-align:right;white-space:nowrap}.muted{color:#666}.toolbar{margin-bottom:24px;display:flex;gap:12px}.toolbar a,.toolbar button{padding:10px;border:1px solid #ccc;background:white;color:#333;text-decoration:none;cursor:pointer}tfoot{font-weight:bold}
        @media print{@page{size:A4 landscape;margin:12mm}body{margin:0;font-size:11px}.toolbar{display:none}thead{display:table-header-group}tr{break-inside:avoid}h2{break-after:avoid}}
    </style>
</head>
<body>
<div class="toolbar"><a href="{{ route('admin-teacher-salaries.index', ['month' => $month]) }}">Kembali</a><button onclick="window.print()">Cetak / Simpan PDF</button></div>
<h1>JM Education — Laporan Gaji Guru</h1>
<p>Periode honor: <strong>{{ \Carbon\Carbon::createFromFormat('!Y-m', $month)->translatedFormat('F Y') }}</strong></p>
<p class="muted">Dikelompokkan berdasarkan bulan honor tercatat. Dicetak {{ now()->format('d/m/Y H:i') }}. Status pembayaran sesuai data saat laporan dibuat.</p>
<table>
    <thead><tr><th>Guru</th><th>Jumlah Honor Pertemuan</th><th>Total Gaji</th><th>Sudah Ditransfer</th><th>Belum Dibayar</th></tr></thead>
    <tbody>
    @forelse($salaries->groupBy('teacher_id') as $items)
        <tr><td>{{ $items->first()->teacher->name }}</td><td>{{ $items->count() }}</td><td class="money">Rp {{ number_format($items->sum('amount'),0,',','.') }}</td><td class="money">Rp {{ number_format($items->whereNotNull('teacher_payout_id')->sum('amount'),0,',','.') }}</td><td class="money">Rp {{ number_format($items->whereNull('teacher_payout_id')->sum('amount'),0,',','.') }}</td></tr>
    @empty
        <tr><td colspan="5">Belum ada honor pada periode ini.</td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><td>Total</td><td>{{ $salaries->count() }}</td><td class="money">Rp {{ number_format($salaries->sum('amount'),0,',','.') }}</td><td class="money">Rp {{ number_format($salaries->whereNotNull('teacher_payout_id')->sum('amount'),0,',','.') }}</td><td class="money">Rp {{ number_format($salaries->whereNull('teacher_payout_id')->sum('amount'),0,',','.') }}</td></tr></tfoot>
</table>
@foreach($salaries->groupBy('teacher_id') as $items)
<h2>{{ $items->first()->teacher->name }}</h2>
<table>
    <thead><tr><th>Tanggal Honor</th><th>Tanggal Kelas</th><th>Course / Materi</th><th>Siswa</th><th>Honor</th><th>Status / Transfer</th></tr></thead>
    <tbody>
    @foreach($items as $salary)
        <tr>
            <td>{{ $salary->earned_at->format('d/m/Y') }}</td>
            <td>{{ $salary->schedule?->training_date?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $salary->course->name }}<br><span class="muted">{{ $salary->schedule?->lesson?->title ?? '—' }}</span></td>
            <td>{{ $salary->student->name }}</td>
            <td class="money">Rp {{ number_format($salary->amount,0,',','.') }}</td>
            <td>@if($salary->payout)Sudah ditransfer · PAY-{{ str_pad($salary->payout->id,6,'0',STR_PAD_LEFT) }}<br>{{ $salary->payout->transfer_date->format('d/m/Y') }} · {{ $salary->payout->bank_name }} {{ $salary->payout->bank_account_number }}<br>a.n. {{ $salary->payout->bank_account_holder }}<br>Admin: {{ $salary->payout->payer?->name ?? '—' }}<br><a href="{{ route('admin-teacher-salaries.proof', $salary->payout) }}">Bukti transfer</a>@else Belum dibayar @endif</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endforeach
</body>
</html>
