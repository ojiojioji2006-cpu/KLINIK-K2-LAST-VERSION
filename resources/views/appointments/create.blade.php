@extends('layouts.app')

@section('title', 'Pendaftaran Pasien')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">

            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Langkah 1 &mdash; Pilih Dokter &amp; Tanggal</span>
                    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Antrean</a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('appointments.create') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Dokter</label>
                                <select name="doctor_id" class="form-select" required>
                                    <option value="">- pilih dokter -</option>
                                    @foreach ($doctors as $d)
                                        <option value="{{ $d->id }}" @selected((string) $doctorId === (string) $d->id)>
                                            {{ $d->name }}@if($d->specialty) ({{ $d->specialty }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="date" value="{{ $date }}" class="form-control" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Cari Slot</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if (! $searched)
                <div class="alert alert-info">
                    Pilih dokter &amp; tanggal di atas, lalu klik <strong>Cari Slot</strong> untuk melihat jam yang tersedia.
                </div>
            @elseif (! $schedule)
                <div class="alert alert-warning">
                    Dokter ini <strong>tidak praktik</strong> pada tanggal tersebut.
                    Coba hari lain, atau tambahkan jadwal lewat menu <em>Jadwal</em>.
                </div>
            @elseif (count($slots) === 0)
                <div class="alert alert-warning">
                    Semua slot pada tanggal ini <strong>sudah penuh</strong>. Coba tanggal lain.
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        Langkah 2 &mdash; Slot tersedia untuk
                        {{ $doctors->firstWhere('id', (int) $doctorId)?->name ?? 'dokter terpilih' }}
                        pada {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('appointments.store') }}">
                            @csrf
                            <input type="hidden" name="doctor_id" value="{{ old('doctor_id', $doctorId) }}">
                            <input type="hidden" name="appointment_date" value="{{ old('appointment_date', $date) }}">

                            <label class="form-label">Pilih Jam <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2 mb-1">
                                @foreach ($slots as $s)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="appointment_time"
                                               id="slot_{{ $loop->index }}" value="{{ $s['time'] }}"
                                               @checked(old('appointment_time') === $s['time']) required>
                                        <label class="form-check-label" for="slot_{{ $loop->index }}">
                                            <code>{{ $s['time'] }}</code>
                                            <small class="text-muted">(sisa {{ $s['remaining'] }})</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('appointment_time')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Pasien <span class="text-danger">*</span></label>
                                    <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                        <option value="">- pilih pasien -</option>
                                        @foreach ($patients as $p)
                                            <option value="{{ $p->id }}" @selected((string) old('patient_id') === (string) $p->id)>
                                                {{ $p->name }} ({{ $p->mrn }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Keluhan (opsional)</label>
                                    <textarea name="chief_complaint" rows="1"
                                              class="form-control @error('chief_complaint') is-invalid @enderror"
                                              placeholder="mis. demam 2 hari">{{ old('chief_complaint') }}</textarea>
                                    @error('chief_complaint')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="alert alert-info mt-3 mb-0">
                                <small>Belum ada pasien di daftar? <a href="{{ route('patients.create') }}">Daftarkan pasien baru dulu</a>, lalu kembali ke sini.</small>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">Daftarkan ke Antrean</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection