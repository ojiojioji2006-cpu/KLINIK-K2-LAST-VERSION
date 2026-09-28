@extends('layouts.app')

@section('title', 'Detail Tagihan')

@section('content')
    @php
        $isPaid = $invoice->status === 'lunas';
        $isCancelled = $invoice->status === 'batal';
        $canPay = (! $isPaid) && (! $isCancelled) && $due > 0;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0">Tagihan <code>{{ $invoice->invoice_number }}</code></h4>
        <div class="d-flex gap-2">
            <a href="{{ route('cashier.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kasir</a>
            <a href="{{ route('invoices.receipt', $invoice) }}" class="btn btn-sm btn-outline-dark">Cetak Struk</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2 small">
                        <div class="col-md-6"><span class="text-muted">Pasien:</span> <span class="fw-semibold">{{ $invoice->patient->name ?? '-' }}</span></div>
                        <div class="col-md-6"><span class="text-muted">MRN:</span> <code>{{ $invoice->patient->mrn ?? '-' }}</code></div>
                        <div class="col-md-6"><span class="text-muted">Dokter:</span> {{ $invoice->appointment->doctor->name ?? '-' }}</div>
                        <div class="col-md-6"><span class="text-muted">Tanggal kunjungan:</span> {{ $invoice->appointment ? $invoice->appointment->appointment_date->format('d M Y') : '-' }}</div>
                        <div class="col-md-6"><span class="text-muted">Dibuat oleh:</span> {{ $invoice->createdBy->name ?? '-' }}</div>
                        <div class="col-md-6"><span class="text-muted">Dibuat:</span> {{ $invoice->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Status:</span>
                        @switch($invoice->status)
                            @case('draft')<span class="badge text-bg-secondary">Draft</span>@break
                            @case('belum_bayar')<span class="badge text-bg-warning">Belum Bayar</span>@break
                            @case('lunas')<span class="badge text-bg-success">Lunas</span>@break
                            @case('batal')<span class="badge text-bg-danger">Batal</span>@break
                        @endswitch
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Rincian Tagihan</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-end" style="width:80px">Qty</th>
                                <th class="text-end" style="width:120px">Harga</th>
                                <th class="text-end" style="width:130px">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoice->items as $it)
                                <tr>
                                    <td>
                                        {{ $it->description }}
                                        @if($it->item_type === 'medicine')
                                            <span class="badge text-bg-light border ms-1">obat</span>
                                        @elseif($it->item_type === 'service')
                                            <span class="badge text-bg-light border ms-1">layanan</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format((float)$it->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $it->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format((float) $it->total_price, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada item.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr><td colspan="3" class="text-end text-muted">Subtotal</td><td class="text-end">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td></tr>
                            <tr><td colspan="3" class="text-end text-muted">Diskon</td><td class="text-end">- Rp {{ number_format((float) $invoice->discount, 0, ',', '.') }}</td></tr>
                            <tr><td colspan="3" class="text-end text-muted">Pajak</td><td class="text-end">+ Rp {{ number_format((float) $invoice->tax, 0, ',', '.') }}</td></tr>
                            <tr class="fw-bold"><td colspan="3" class="text-end">TOTAL</td><td class="text-end">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">Pembayaran</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Total tagihan</span><span>Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Sudah dibayar</span><span>Rp {{ number_format($alreadyPaid, 0, ',', '.') }}</span></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span>Sisa</span>
                        <span class="{{ $due <= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($due, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">Riwayat Pembayaran</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Tanggal</th><th>Metode</th><th class="text-end">Nominal</th><th>Oleh</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($invoice->payments->sortByDesc('id') as $pay)
                                <tr>
                                    <td><small>{{ $pay->paid_at->format('d M Y H:i') }}</small></td>
                                    <td><span class="badge text-bg-light border">{{ ucfirst($pay->method) }}</span></td>
                                    <td class="text-end">Rp {{ number_format((float) $pay->amount, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">{{ $pay->createdBy->name ?? '-' }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($canPay)
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">Bayar Sekarang</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('invoices.pay', $invoice) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nominal Bayar (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" min="1" step="1" name="amount" value="{{ old('amount', (int) $due) }}"
                                           class="form-control @error('amount') is-invalid @enderror" required>
                                    <div class="form-text">Sisa saat ini: Rp {{ number_format($due, 0, ',', '.') }}. Isi tidak boleh melebihi sisa.</div>
                                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Metode <span class="text-danger">*</span></label>
                                    <select name="method" class="form-select @error('method') is-invalid @enderror" required>
                                        <option value="tunai" @selected(old('method','tunai')==='tunai')>Tunai</option>
                                        <option value="kartu" @selected(old('method')==='kartu')>Kartu</option>
                                        <option value="transfer" @selected(old('method')==='transfer')>Transfer</option>
                                        <option value="asuransi" @selected(old('method')==='asuransi')>Asuransi</option>
                                        <option value="lainnya" @selected(old('method')==='lainnya')>Lainnya</option>
                                    </select>
                                    @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Referensi / Struk</label>
                                    <input name="receipt_number" value="{{ old('receipt_number') }}" class="form-control @error('receipt_number') is-invalid @enderror" placeholder="opsional">
                                    @error('receipt_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <small>Stok obat <strong>baru dipotong</strong> saat pembayaran ini melunasi tagihan. Pembayaran parsial tidak menyentuh stok.</small>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-success w-100">Proses Pembayaran</button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($isPaid)
                <div class="alert alert-success">
                    <strong>Tagihan ini sudah LUNAS.</strong> Stok obat telah disesuaikan. Gunakan tombol <em>Cetak Struk</em> di atas untuk mencetak.
                </div>
            @elseif($isCancelled)
                <div class="alert alert-danger">Tagihan ini dibatalkan.</div>
            @else
                <div class="alert alert-secondary">Tidak ada sisa tagihan.</div>
            @endif
        </div>
    </div>
@endsection