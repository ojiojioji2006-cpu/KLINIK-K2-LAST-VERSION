@extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Form Tambah Jadwal Praktik</span>
                    <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
                </div>
                <div class="card-body">
                    @if ($doctors->isEmpty())
                        <div class="alert alert-warning">
                            Belum ada dokter aktif. <a href="{{ route('doctors.create') }}">Tambah dokter dulu</a> sebelum membuat jadwal.
                        </div>
                    @else
                    <form method="POST" action="{{ route('schedules.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Dokter <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                    <option value="">- pilih dokter -</option>
                                    @foreach ($doctors as $d)
                                        <option value="{{ $d->id }}" @selected(old('doctor_id') == $d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hari <span class="text-danger">*</span></label>
                                <select name="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror" required>
                                    @foreach (['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $i => $nm)
                                        <option value="{{ $i }}" @selected((int) old('day_of_week', 1) === $i)>{{ $nm }}</option>
                                    @endforeach
                                </select>
                                @error('day_of_week')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}"
                                       class="form-control @error('start_time') is-invalid @enderror" required>
                                @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" value="{{ old('end_time', '12:00') }}"
                                       class="form-control @error('end_time') is-invalid @enderror" required>
                                @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Interval Slot (menit) <span class="text-danger">*</span></label>
                                <input type="number" name="slot_minutes" value="{{ old('slot_minutes', 15) }}" min="5" max="240"
                                       class="form-control @error('slot_minutes') is-invalid @enderror" required>
                                @error('slot_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maks Pasien / Slot <span class="text-danger">*</span></label>
                                <input type="number" name="max_patients_per_slot" value="{{ old('max_patients_per_slot', 1) }}" min="1" max="20"
                                       class="form-control @error('max_patients_per_slot') is-invalid @enderror" required>
                                @error('max_patients_per_slot')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                           @checked(old('is_active', true))>
                                    <label class="form-check-label" for="is_active">Jadwal aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <small>Slot antrean akan dihitung otomatis dari jam mulai, jam selesai, dan interval.</small>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection