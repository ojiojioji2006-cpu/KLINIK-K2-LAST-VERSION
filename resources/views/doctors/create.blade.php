@extends('layouts.app')

@section('title', 'Tambah Dokter')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Form Tambah Dokter</span>
                    <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('doctors.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Dokter <span class="text-danger">*</span></label>
                                <input name="name" value="{{ old('name') }}"
                                       class="form-control @error('name') is-invalid @enderror" required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SIP</label>
                                <input name="sip" value="{{ old('sip') }}"
                                       class="form-control @error('sip') is-invalid @enderror">
                                @error('sip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Spesialis</label>
                                <input name="specialty" value="{{ old('specialty') }}"
                                       class="form-control @error('specialty') is-invalid @enderror"
                                       placeholder="mis. Umum / Anak / Gigi">
                                @error('specialty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. HP</label>
                                <input name="phone" value="{{ old('phone') }}"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" rows="2"
                                          class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                           @checked(old('is_active', true))>
                                    <label class="form-check-label" for="is_active">Dokter aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Dokter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection