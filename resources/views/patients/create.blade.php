@extends('layouts.app')

@section('title', 'Tambah Pasien Baru')

@section('content')
<div class="form-page-header">
    <a href="{{ route('patients.index') }}" class="form-back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke Daftar Pasien
    </a>
    <h1 class="form-page-title">Registrasi Pasien Baru</h1>
</div>

<form method="POST" action="{{ route('patients.store') }}" id="patient-form">
    @csrf
    
    <div class="form-card">
        <div class="form-card-body">
            
            <div class="form-section-title">Identitas Pasien</div>
            
            <div class="form-group">
                <label class="form-label-custom">Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" 
                       class="form-input @error('name') is-invalid @enderror" required autofocus 
                       placeholder="cth: Budi Santoso">
                @error('name')<div class="form-error-msg">{{ $message }}</div>@enderror
                <div class="form-helper-text">Sesuai kartu identitas resmi (KTP/Paspor/SIM).</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Jenis Kelamin</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">- Belum ditentukan -</option>
                        <option value="L" @selected(old('gender')==='L')>Laki-laki</option>
                        <option value="P" @selected(old('gender')==='P')>Perempuan</option>
                    </select>
                    @error('gender')<div class="form-error-msg">{{ $message }}</div>@enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label-custom">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" 
                           class="form-input @error('birth_date') is-invalid @enderror" max="{{ now()->toDateString() }}">
                    @error('birth_date')<div class="form-error-msg">{{ $message }}</div>@enderror
                    <div class="form-helper-text">Opsional namun disarankan untuk perhitungan usia otomatis.</div>
                </div>
            </div>
            
            <div class="form-section-title">Informasi Kontak</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">No. Telepon / WhatsApp</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" 
                           class="form-input @error('phone') is-invalid @enderror" placeholder="08xxxxxxxxxx">
                    @error('phone')<div class="form-error-msg">{{ $message }}</div>@enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label-custom">Email (Opsional)</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="form-input @error('email') is-invalid @enderror" placeholder="nama@email.com">
                    @error('email')<div class="form-error-msg">{{ $message }}</div>@enderror
                    <div class="form-helper-text">Untuk pengiriman hasil lab/resep digital jika tersedia.</div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label-custom">Alamat Tempat Tinggal</label>
                <textarea name="address" rows="3" class="form-textarea @error('address') is-invalid @enderror" 
                          placeholder="Jalan No., RT/RW, Kel/Desa, Kec., Kota/Kab., Prov., Kode Pos">{{ old('address') }}</textarea>
                @error('address')<div class="form-error-msg">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-section-title">Penjamin / Asuransi</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Penyedia Asuransi</label>
                    <input type="text" name="insurance_provider" value="{{ old('insurance_provider') }}" 
                           class="form-input @error('insurance_provider') is-invalid @enderror" 
                           placeholder="BPJS Kesehatan / Prudential / Mandiri Inhealth / dll">
                    @error('insurance_provider')<div class="form-error-msg">{{ $message }}</div>@enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label-custom">Nomor Peserta / Polis</label>
                    <input type="text" name="insurance_number" value="{{ old('insurance_number') }}" 
                           class="form-input @error('insurance_number') is-invalid @enderror" 
                           placeholder="0001234567890">
                    @error('insurance_number')<div class="form-error-msg">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="form-helper-text" style="margin-top:-.5rem;">
                💡 MRN (Medical Record Number) akan dibuat otomatis oleh sistem setelah formulir disimpan.
            </div>
            
        </div>{{-- /.form-card-body --}}
        
        <div class="form-action-bar">
            <a href="{{ route('patients.index') }}" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Daftarkan Pasien
            </button>
        </div>
        
    </div>{{-- /.form-card --}}
</form>
@endsection