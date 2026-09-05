<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat {{ $report->student->name }} - {{ $report->course->name }}</title>
    <style>
        *{box-sizing:border-box}body{background:#eef0fb;color:#24243b;font-family:Arial,sans-serif;margin:0;padding:32px}.toolbar{display:flex;gap:10px;justify-content:center;margin-bottom:22px}.button{background:#4b49ac;border:0;border-radius:7px;color:#fff;cursor:pointer;font-size:14px;padding:11px 20px;text-decoration:none}.button.secondary{background:#fff;color:#4b49ac}.certificate{aspect-ratio:1.414/1;background:#fff;border:14px solid #4b49ac;box-shadow:0 16px 45px rgba(44,44,88,.16);margin:auto;max-width:1120px;min-height:680px;padding:18px;position:relative}.inner{border:2px solid #b8a76a;height:100%;padding:48px 70px;text-align:center}.academy{color:#4b49ac;font-size:23px;font-weight:700;letter-spacing:3px}.title{font-family:Georgia,serif;font-size:54px;font-weight:400;letter-spacing:7px;margin:34px 0 6px;text-transform:uppercase}.subtitle{color:#777;font-size:15px;letter-spacing:3px;text-transform:uppercase}.presented{color:#777;margin:35px 0 10px}.student{border-bottom:1px solid #b8a76a;color:#4b49ac;display:inline-block;font-family:Georgia,serif;font-size:38px;font-style:italic;min-width:65%;padding:0 20px 8px}.description{font-size:17px;line-height:1.7;margin:22px auto;max-width:760px}.course{color:#4b49ac;font-size:24px;font-weight:700}.grade{background:#4b49ac;border-radius:50%;color:#fff;display:inline-flex;font-size:26px;font-weight:700;height:66px;justify-content:center;align-items:center;margin-left:12px;width:66px}.signatures{display:flex;justify-content:space-between;margin-top:48px}.signature{width:240px}.line{border-top:1px solid #444;margin-top:45px;padding-top:8px}.small{color:#777;font-size:12px}.code{bottom:27px;color:#999;font-size:11px;left:0;position:absolute;right:0}.corner{border-color:#b8a76a;border-style:solid;height:55px;position:absolute;width:55px}.top-left{border-width:4px 0 0 4px;left:32px;top:32px}.top-right{border-width:4px 4px 0 0;right:32px;top:32px}.bottom-left{border-width:0 0 4px 4px;bottom:32px;left:32px}.bottom-right{border-width:0 4px 4px 0;bottom:32px;right:32px}@media print{@page{size:A4 landscape;margin:0}body{background:#fff;padding:0}.toolbar{display:none}.certificate{border-width:10px;box-shadow:none;height:210mm;max-width:none;min-height:0;width:297mm}.inner{padding:35px 65px}.title{margin-top:20px}}
    </style>
</head>
<body>
    <div class="toolbar"><a class="button secondary" href="{{ route('student-reports.index') }}">Kembali</a><button class="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
    <main class="certificate"><div class="inner"><span class="corner top-left"></span><span class="corner top-right"></span><span class="corner bottom-left"></span><span class="corner bottom-right"></span>
        <div class="academy">JM ACADEMY</div><h1 class="title">Sertifikat</h1><div class="subtitle">Penyelesaian Course</div>
        <p class="presented">Sertifikat ini diberikan kepada</p><div class="student">{{ $report->student->name }}</div>
        <p class="description">atas keberhasilan menyelesaikan seluruh rangkaian pembelajaran course<br><span class="course">{{ $report->course->name }}</span><span class="grade">{{ $report->grade }}</span></p>
        <div class="signatures"><div class="signature"><div class="line"><strong>{{ $report->teacher->name }}</strong><div class="small">Guru / Pembimbing</div></div></div><div class="signature"><div class="line"><strong>JM Academy</strong><div class="small">Penyelenggara</div></div></div></div>
        <div class="code">Diterbitkan {{ $report->submitted_at->translatedFormat('d F Y') }} · CERT-{{ $report->submitted_at->format('Y') }}-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div></main>
</body>
</html>
