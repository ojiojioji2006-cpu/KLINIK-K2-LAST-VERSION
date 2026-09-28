@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $role = auth()->user()->role;
    $hour = (int) now()->format('G');
    $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
    $firstName = trim(explode(' ', (string) auth()->user()->name)[0]);
@endphp

{{-- ===== HERO GREETING ===== --}}
<section class="dash-hero">
    <h2>{{ $greet }}, {{ $firstName }} 👋</h2>
    <div class="sub">
        <span>{{ \Carbon\Carbon::parse($todayStr)->translatedFormat('l, d F Y') }}</span>
        <span class="dash-clock" id="dashClock">--:--:--</span>
        <span style="opacity:.7;">· Panel kendali Klinik Sederhana</span>
    </div>
</section>

{{-- ===== KPI OPERASIONAL ===== --}}
<div class="kpi-grid">
    <div class="kpi-card tone-warn">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Antrean Menunggu</div>
            <div class="kpi-value">{{ $stats['waiting'] }}</div>
            <div class="kpi-foot">hari ini</div>
        </div>
    </div>

    <div class="kpi-card tone-info">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Sedang Diperiksa</div>
            <div class="kpi-value">{{ $stats['in_exam'] }}</div>
            <div class="kpi-foot">di ruang periksa</div>
        </div>
    </div>

    <div class="kpi-card tone-ok">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Selesai Hari Ini</div>
            <div class="kpi-value">{{ $stats['done'] }}</div>
            <div class="kpi-foot">{{ $stats['cancelled'] }} batal · {{ $stats['no_show'] }} absen</div>
        </div>
    </div>

    <div class="kpi-card tone-neutral">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Total Kunjungan</div>
            <div class="kpi-value">{{ $stats['total_today'] }}</div>
            <div class="kpi-foot">terdaftar hari ini</div>
        </div>
    </div>
</div>

{{-- ===== FINANSIAL ===== --}}
<div class="fin-grid">
    <div class="kpi-card tone-money">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Kas Masuk Hari Ini</div>
            <div class="kpi-value" style="font-size:1.4rem;">Rp {{ number_format($cashToday, 0, ',', '.') }}</div>
            <div class="kpi-foot">dari seluruh pembayaran</div>
        </div>
    </div>

    <div class="kpi-card tone-debt">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Piutang Bersih</div>
            <div class="kpi-value" style="font-size:1.4rem;">Rp {{ number_format($netReceivable, 0, ',', '.') }}</div>
            <div class="kpi-foot">{{ $openCount }} tagihan terbuka</div>
        </div>
    </div>

    <div class="kpi-card tone-patient">
        <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-label">Total Pasien</div>
            <div class="kpi-value">{{ $totalPatients }}</div>
            <div class="kpi-foot">terdaftar sepanjang masa</div>
        </div>
    </div>
</div>

{{-- ===== ALARM STOK RENDAH ===== --}}
@if($lowStock->isEmpty())
    <div class="stock-alert ok">
        <div class="sa-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="sa-title">Persediaan obat aman</div>
            <div style="font-size:.82rem;color:#067647;">Tidak ada obat dengan stok ≤ 10 saat ini.</div>
        </div>
    </div>
@else
    <div class="stock-alert danger">
        <div class="sa-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div style="flex-grow:1;">
            <div class="sa-title">Stok rendah — perlu restock ({{ $lowStock->count() }} item)</div>
            <div class="stock-chips">
                @foreach($lowStock->take(8) as $m)
                    <span class="stock-chip">{{ $m->name }} <b>{{ $m->stock }}</b> {{ $m->unit }}</span>
                @endforeach
                @if($lowStock->count() > 8)
                    <span class="stock-chip" style="background:#fee2e2;">+{{ $lowStock->count() - 8 }} lainnya</span>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- ===== AKTIVITAS TERAKHIR ===== --}}
<div class="act-grid">
    {{-- Antrean terakhir --}}
    <div class="act-panel">
        <div class="act-head">
            <span>Antrean Terakhir Hari Ini</span>
            @if(in_array($role, ['admin','perawat','dokter']))
                <a href="{{ route('appointments.index') }}" class="see-all">Lihat semua →</a>
            @endif
        </div>
        @forelse($recentAppointments as $a)
            <div class="act-row">
                <div class="act-avatar">{{ strtoupper(substr((string)($a->patient->name ?? '?'),0,1)) }}</div>
                <div class="act-main">
                    <div class="act-name">{{ $a->patient->name ?? '-' }}</div>
                    <div class="act-meta">{{ substr((string)$a->appointment_time,0,5) }} · {{ $a->doctor->name ?? '-' }}</div>
                </div>
                <div class="act-side">
                    @switch($a->status)
                        @case('menunggu')<span class="badge badge-warning">Menunggu</span>@break
                        @case('dalam_pemeriksaan')<span class="badge badge-info">Diperiksa</span>@break
                        @case('selesai')<span class="badge badge-success">Selesai</span>@break
                        @case('batal')<span class="badge badge-secondary">Batal</span>@break
                        @case('tidak_hadir')<span class="badge badge-danger">Absen</span>@break
                    @endswitch
                </div>
            </div>
        @empty
            <div class="empty-note">Belum ada aktivitas antrean hari ini.</div>
        @endforelse
    </div>

    {{-- Tagihan terakhir --}}
    <div class="act-panel">
        <div class="act-head">
            <span>Tagihan Terakhir</span>
            @if(in_array($role, ['admin','kasir']))
                <a href="{{ route('cashier.index') }}" class="see-all">Ke kasir →</a>
            @endif
        </div>
        @forelse($recentInvoices as $inv)
            <div class="act-row">
                <div class="act-avatar" style="background:#ecfdf3;color:#15803d;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                <div class="act-main">
                    <div class="act-name">{{ $inv->invoice_number }}</div>
                    <div class="act-meta">{{ $inv->patient->name ?? '-' }} · {{ $inv->created_at->format('d M H:i') }}</div>
                </div>
                <div class="act-side">
                    <div class="act-amt">Rp {{ number_format((float)$inv->total,0,',','.') }}</div>
                    @switch($inv->status)
                        @case('lunas')<span class="badge badge-success">Lunas</span>@break
                        @case('belum_bayar')<span class="badge badge-warning">Belum Bayar</span>@break
                        @case('draft')<span class="badge badge-secondary">Draft</span>@break
                        @case('batal')<span class="badge badge-danger">Batal</span>@break
                    @endswitch
                </div>
            </div>
        @empty
            <div class="empty-note">Belum ada tagihan tercatat.</div>
        @endforelse
    </div>
</div>

<script>
(function(){
    var el=document.getElementById('dashClock');
    if(!el)return;
    function pad(n){return (n<10?'0':'')+n;}
    function tick(){var d=new Date();el.textContent=pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds());}
    tick();setInterval(tick,1000);
})();
</script>
@endsection