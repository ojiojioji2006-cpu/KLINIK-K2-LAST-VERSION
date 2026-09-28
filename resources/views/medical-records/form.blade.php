@extends('layouts.app')

@section('title', 'Rekam Medis')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-semibold">Rekam Medis &mdash; {{ $patient->name ?? '-' }}</span>
                    <a href="{{ route('appointments.index', ['date' => $appointment->appointment_date->toDateString()]) }}"
                       class="btn btn-sm btn-outline-secondary">&larr; Antrean</a>
                </div>
                <div class="card-body">
                    <div class="row g-2 small">
                        <div class="col-md-3"><span class="text-muted">MRN:</span> <code>{{ $patient->mrn ?? '-' }}</code></div>
                        <div class="col-md-3"><span class="text-muted">Dokter:</span> {{ $doctor->name ?? '-' }}</div>
                        <div class="col-md-3"><span class="text-muted">Tanggal:</span> {{ $appointment->appointment_date->format('d M Y') }}</div>
                        <div class="col-md-3"><span class="text-muted">Jam:</span> <code>{{ substr((string) $appointment->appointment_time, 0, 5) }}</code>
                            @if($appointment->status === 'selesai')
                                <span class="badge text-bg-success ms-1">Selesai</span>
                            @else
                                <span class="badge text-bg-info ms-1">Diperiksa</span>
                            @endif
                        </div>
                    </div>
                    @if($record->exists && $record->visit_date)
                        <div class="text-muted small mt-2">Dibuat: {{ $record->visit_date->format('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('medical_records.save', $appointment) }}">
                        @csrf

                        <h6 class="text-muted mb-3">Anamnesis</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Keluhan Utama</label>
                                <textarea name="complaint" rows="2" class="form-control @error('complaint') is-invalid @enderror">{{ old('complaint', $record->complaint) }}</textarea>
                                @error('complaint')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Riwayat Penyakit / Alergi</label>
                                <textarea name="anamnesis" rows="2" class="form-control @error('anamnesis') is-invalid @enderror">{{ old('anamnesis', $record->anamnesis) }}</textarea>
                                @error('anamnesis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h6 class="text-muted mb-3">Tanda Vital</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label">TD Sistol</label>
                                <input type="number" name="blood_pressure_systolic" value="{{ old('blood_pressure_systolic', $record->blood_pressure_systolic) }}" class="form-control @error('blood_pressure_systolic') is-invalid @enderror" placeholder="mmHg">
                                @error('blood_pressure_systolic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">TD Diastol</label>
                                <input type="number" name="blood_pressure_diastolic" value="{{ old('blood_pressure_diastolic', $record->blood_pressure_diastolic) }}" class="form-control @error('blood_pressure_diastolic') is-invalid @enderror" placeholder="mmHg">
                                @error('blood_pressure_diastolic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Nadi</label>
                                <input type="number" name="heart_rate" value="{{ old('heart_rate', $record->heart_rate) }}" class="form-control @error('heart_rate') is-invalid @enderror" placeholder="/mnt">
                                @error('heart_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Napas</label>
                                <input type="number" name="respiratory_rate" value="{{ old('respiratory_rate', $record->respiratory_rate) }}" class="form-control @error('respiratory_rate') is-invalid @enderror" placeholder="/mnt">
                                @error('respiratory_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Suhu</label>
                                <input type="number" step="0.1" name="temperature" value="{{ old('temperature', $record->temperature) }}" class="form-control @error('temperature') is-invalid @enderror" placeholder="&deg;C">
                                @error('temperature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">BB (kg)</label>
                                <input type="number" step="0.01" name="weight" value="{{ old('weight', $record->weight) }}" class="form-control @error('weight') is-invalid @enderror">
                                @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">TB (cm)</label>
                                <input type="number" step="0.01" name="height" value="{{ old('height', $record->height) }}" class="form-control @error('height') is-invalid @enderror">
                                @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h6 class="text-muted mb-3">Pemeriksaan &amp; Diagnosis</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label">Pemeriksaan Fisik</label>
                                <textarea name="physical_examination" rows="2" class="form-control @error('physical_examination') is-invalid @enderror">{{ old('physical_examination', $record->physical_examination) }}</textarea>
                                @error('physical_examination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Diagnosis</label>
                                <textarea name="diagnosis_text" rows="2" class="form-control @error('diagnosis_text') is-invalid @enderror" placeholder="mis. ISPA, gastroenteritis akut">{{ old('diagnosis_text', $record->diagnosis_text) }}</textarea>
                                @error('diagnosis_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rencana Terapi</label>
                                <textarea name="treatment_plan" rows="2" class="form-control @error('treatment_plan') is-invalid @enderror">{{ old('treatment_plan', $record->treatment_plan) }}</textarea>
                                @error('treatment_plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea name="additional_notes" rows="2" class="form-control @error('additional_notes') is-invalid @enderror">{{ old('additional_notes', $record->additional_notes) }}</textarea>
                                @error('additional_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-muted mb-0">Resep Obat</h6>
                            <button type="button" id="rx-add" class="btn btn-sm btn-outline-primary">+ Tambah Obat</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:200px">Obat</th>
                                        <th style="width:90px">Qty</th>
                                        <th style="width:120px">Dosis</th>
                                        <th style="width:130px">Frekuensi</th>
                                        <th style="min-width:140px">Instruksi</th>
                                        <th class="text-end" style="width:110px">Harga</th>
                                        <th class="text-end" style="width:120px">Subtotal</th>
                                        <th class="text-center" style="width:48px"></th>
                                    </tr>
                                </thead>
                                <tbody id="rx-items">
                                    @foreach ($existingItems as $i => $it)
                                        <tr class="rx-row">
                                            <td>
                                                <select name="items[{{ $i }}][medicine_id]" class="form-select form-select-sm" data-rx-select>
                                                    <option value="">- pilih obat -</option>
                                                    @foreach ($medicines as $m)
                                                        <option value="{{ $m->id }}" data-price="{{ $m->sell_price }}" data-unit="{{ $m->unit }}" @selected((int) $it->medicine_id === (int) $m->id)>{{ $m->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ $it->quantity }}" class="form-control form-control-sm" data-rx-qty></td>
                                            <td><input name="items[{{ $i }}][dose]" value="{{ $it->dose }}" class="form-control form-control-sm" data-rx-dose></td>
                                            <td><input name="items[{{ $i }}][frequency]" value="{{ $it->frequency }}" class="form-control form-control-sm" data-rx-freq></td>
                                            <td><input name="items[{{ $i }}][instruction]" value="{{ $it->instruction }}" class="form-control form-control-sm" data-rx-instr></td>
                                            <td class="text-end rx-price" data-rx-price>Rp {{ number_format((float) $it->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-end fw-semibold rx-subtotal" data-rx-subtotal>Rp {{ number_format((float) $it->total_price, 0, ',', '.') }}</td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger rx-remove" title="Hapus baris">&times;</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <template id="rx-row-template">
                            <tr class="rx-row">
                                <td>
                                    <select name="items[__IDX__][medicine_id]" class="form-select form-select-sm" data-rx-select>
                                        <option value="">- pilih obat -</option>
                                        @foreach ($medicines as $m)
                                            <option value="{{ $m->id }}" data-price="{{ $m->sell_price }}" data-unit="{{ $m->unit }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" min="1" value="1" name="items[__IDX__][quantity]" class="form-control form-control-sm" data-rx-qty></td>
                                <td><input name="items[__IDX__][dose]" class="form-control form-control-sm" data-rx-dose></td>
                                <td><input name="items[__IDX__][frequency]" class="form-control form-control-sm" data-rx-freq></td>
                                <td><input name="items[__IDX__][instruction]" class="form-control form-control-sm" data-rx-instr></td>
                                <td class="text-end rx-price" data-rx-price>-</td>
                                <td class="text-end fw-semibold rx-subtotal" data-rx-subtotal>-</td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger rx-remove" title="Hapus baris">&times;</button></td>
                            </tr>
                        </template>

                        <div class="alert alert-info mt-3 mb-0">
                            <small>Harga diambil otomatis dari master obat. <strong>Stok belum dipotong</strong> &mdash; potongan stok terjadi saat pasien membayar lunas di kasir.</small>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mt-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            @if($appointment->status === 'dalam_pemeriksaan')
                                <button type="submit" name="finalize" value="1" class="btn btn-success">Simpan &amp; Selesai</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ asset('js/prescription.js') }}"></script>
@endsection