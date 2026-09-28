@extends('layouts.app')

@section('title', 'Antrean Pasien')

@section('content')
    @php $role = auth()->user()->role; @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Antrean &mdash; {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h4>
        @if(in_array($role, ['admin', 'perawat']))
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">+ Pendaftaran Pasien</a>
        @endif
    </div>

    <form method="GET" action="{{ route('appointments.index') }}" class="mb-3">
        <div class="input-group" style="max-width: 360px;">
            <input type="date" name="date" value="{{ $date }}" class="form-control">
            <button class="btn btn-outline-secondary" type="submit">Lihat</button>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary">Hari Ini</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Jam</th>
                        <th>Pasien</th>
                        <th>MRN</th>
                        <th>Dokter</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $a)
                        <tr>
                            <td><code>{{ substr((string) $a->appointment_time, 0, 5) }}</code></td>
                            <td class="fw-semibold">{{ $a->patient->name ?? '-' }}</td>
                            <td><small>{{ $a->patient->mrn ?? '-' }}</small></td>
                            <td>{{ $a->doctor->name ?? '-' }}</td>
                            <td><small class="text-muted">{{ $a->chief_complaint ? \Illuminate\Support\Str::limit($a->chief_complaint, 40) : '-' }}</small></td>
                            <td>
                                @switch($a->status)
                                    @case('menunggu')<span class="badge text-bg-warning">Menunggu</span>@break
                                    @case('dalam_pemeriksaan')<span class="badge text-bg-info">Diperiksa</span>@break
                                    @case('selesai')<span class="badge text-bg-success">Selesai</span>@break
                                    @case('batal')<span class="badge text-bg-secondary">Batal</span>@break
                                    @case('tidak_hadir')<span class="badge text-bg-danger">Tidak Hadir</span>@break
                                @endswitch
                                @if($a->called_at)
                                    <div class="mt-1"><span class="badge text-bg-light border">Dipanggil {{ $a->called_at->format('H:i') }}</span></div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">

                                    @if($a->status === 'menunggu')
                                        @if(in_array($role, ['admin', 'perawat', 'dokter']))
                                            <form method="POST" action="{{ route('appointments.call', $a) }}" class="d-inline">
                                                @csrf
                                                @if($a->called_at)
                                                    <button class="btn btn-sm btn-outline-secondary">Panggil Ulang</button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-info">Panggil</button>
                                                @endif
                                            </form>
                                        @endif
                                        @if(in_array($role, ['dokter', 'admin']))
                                            <form method="POST" action="{{ route('appointments.status', $a) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="to" value="dalam_pemeriksaan">
                                                <button class="btn btn-sm btn-info text-white">Mulai Periksa</button>
                                            </form>
                                        @endif
                                        @if(in_array($role, ['perawat', 'admin']))
                                            <form method="POST" action="{{ route('appointments.status', $a) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="to" value="batal">
                                                <button class="btn btn-sm btn-outline-secondary">Batal</button>
                                            </form>
                                            <form method="POST" action="{{ route('appointments.status', $a) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="to" value="tidak_hadir">
                                                <button class="btn btn-sm btn-outline-danger">Tidak Hadir</button>
                                            </form>
                                        @endif

                                    @elseif($a->status === 'dalam_pemeriksaan')
                                        @if(in_array($role, ['dokter', 'admin']))
                                            <a href="{{ route('medical_records.show', $a) }}" class="btn btn-sm btn-primary">Rekam Medis</a>
                                            <form method="POST" action="{{ route('appointments.status', $a) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="to" value="selesai">
                                                <button class="btn btn-sm btn-success">Selesai</button>
                                            </form>
                                        @endif

                                    @else
                                        @if($a->medicalRecord && in_array($role, ['dokter', 'admin']))
                                            <a href="{{ route('medical_records.show', $a) }}" class="btn btn-sm btn-outline-primary">Riwayat RM</a>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada antrean pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection