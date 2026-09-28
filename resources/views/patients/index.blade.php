@extends('layouts.app')

@section('title', 'Data Pasien')

@section('content')
<div class="lst-header">
    <h1 class="lst-title">Data Pasien</h1>
    <a href="{{ route('patients.create') }}" class="lst-add-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah
    </a>
</div>

<div class="lst-search-wrap">
    <form method="GET" action="{{ route('patients.index') }}">
        <input type="text" name="q" value="{{ request('q') }}" 
               class="lst-search-input" placeholder="Cari nama atau MRN...">
    </form>
</div>

<div class="lst-table-wrap">
    <table class="lst-table">
        <thead>
            <tr>
                <th style="width:130px">MRN</th>
                <th>Nama Lengkap</th>
                <th style="width:50px;text-align:center">JK</th>
                <th style="width:110px">Tgl Lahir</th>
                <th style="width:130px">No. HP</th>
                <th>Asuransi</th>
                <th style="width:80px;text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($patients as $p)
                <tr>
                    <td class="lst-cell-mono">{{ $p->mrn }}</td>
                    <td class="lst-cell-strong">{{ $p->name }}</td>
                    <td class="text-center lst-cell-muted">{{ strtoupper(substr($p->gender ?? '-',0,1)) ?: '-' }}</td>
                    <td class="lst-cell-muted">{{ $p->birth_date?->format('d M Y') ?? '-' }}</td>
                    <td class="lst-cell-muted">{{ $p->phone ?? '-' }}</td>
                    <td class="lst-cell-muted">{{ $p->insurance_provider ? $p->insurance_provider . ' (' . ($p->insurance_number ?? '') . ')' : 'Umum' }}</td>
                    <td class="text-right lst-cell-action">
                        <a href="#">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="lst-empty">Belum ada pasien terdaftar.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    @if($patients->hasPages())
        <div class="lst-pagination">
            {!! $patients->links() !!}
        </div>
    @endif
</div>
@endsection