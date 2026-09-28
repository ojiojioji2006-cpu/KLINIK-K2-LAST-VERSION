@extends('layouts.app')

@section('title', 'Buat Tagihan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-semibold">Tagihan &mdash; {{ $patient->name ?? '-' }}</span>
                    <a href="{{ route('cashier.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kasir</a>
                </div>
                <div class="card-body">
                    <div class="row g-2 small">
                        <div class="col-md-3"><span class="text-muted">MRN:</span> <code>{{ $patient->mrn ?? '-' }}</code></div>
                        <div class="col-md-3"><span class="text-muted">Dokter:</span> {{ $doctor->name ?? '-' }}</div>
                        <div class="col-md-3"><span class="text-muted">Tanggal:</span> {{ $appointment->appointment_date->format('d M Y') }}</div>
                        <div class="col-md-3"><span class="text-muted">Jam:</span> <code>{{ substr((string) $appointment->appointment_time, 0, 5) }}</code></div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('cashier.store') }}" id="inv-form">
                @csrf
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold">Obat dari Resep <span class="text-muted small">(read-only &mdash; ditentukan dokter)</span></div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Obat</th>
                                    <th class="text-end" style="width:90px">Qty</th>
                                    <th class="text-end" style="width:120px">Harga</th>
                                    <th class="text-end" style="width:130px">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($obatLines as $o)
                                    <tr>
                                        <td>{{ $o['name'] }} <small class="text-muted">({{ $o['unit'] }})</small></td>
                                        <td class="text-end">{{ $o['qty'] }}</td>
                                        <td class="text-end">Rp {{ number_format($o['price'], 0, ',', '.') }}</td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($o['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Tidak ada resep pada kunjungan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" id="inv-obat-sum" data-value="{{ $obatTotal }}">
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Layanan / Tindakan Tambahan</span>
                        <button type="button" id="inv-add" class="btn btn-sm btn-outline-primary">+ Tambah Layanan</button>
                    </div>
                    <div class="card-body">
                        @if ($services->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Belum ada layanan aktif. <a href="{{ route('doctors.index') }}">(master layanan dikelola admin)</a>
                            </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:220px">Layanan</th>
                                        <th style="width:90px">Qty</th>
                                        <th class="text-end" style="width:120px">Harga</th>
                                        <th class="text-end" style="width:130px">Subtotal</th>
                                        <th class="text-center" style="width:48px"></th>
                                    </tr>
                                </thead>
                                <tbody id="inv-services">
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <template id="invoice-row-template">
                            <tr class="inv-row">
                                <td>
                                    <select name="items[__IDX__][service_id]" class="form-select form-select-sm" data-inv-select>
                                        <option value="">- pilih layanan -</option>
                                        @foreach ($services as $s)
                                            <option value="{{ $s->id }}" data-price="{{ $s->price }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" min="1" value="1" name="items[__IDX__][quantity]" class="form-control form-control-sm" data-inv-qty></td>
                                <td class="text-end inv-price" data-inv-price>-</td>
                                <td class="text-end fw-semibold inv-subtotal" data-inv-subtotal>-</td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger inv-remove" title="Hapus baris">&times;</button></td>
                            </tr>
                        </template>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Diskon (Rp)</label>
                                <input type="number" min="0" step="1" name="discount" value="{{ old('discount', 0) }}" id="inv-discount" class="form-control @error('discount') is-invalid @enderror">
                                @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pajak (Rp)</label>
                                <input type="number" min="0" step="1" name="tax" value="{{ old('tax', 0) }}" id="inv-tax" class="form-control @error('tax') is-invalid @enderror">
                                @error('tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="text-muted small">Subtotal</div>
                                <div class="fs-5" id="inv-subtotal">Rp 0</div>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="text-muted small">Total</div>
                                <div class="fs-4 fw-bold text-primary" id="inv-total">Rp 0</div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <small>Harga diambil otomatis dari master (obat &amp; layanan). <strong>Stok belum dipotong &amp; pembayaran belum diproses</strong> &mdash; itu terjadi di ronde 9B saat tagihan dilunasi.</small>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">Simpan Tagihan (Belum Bayar)</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script src="{{ asset('js/invoice.js') }}"></script>
@endsection