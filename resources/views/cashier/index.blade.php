@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
    <h4 class="mb-3">Kasir &mdash; Penagihan</h4>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Perlu Ditagih (kunjungan selesai, belum ada tagihan)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Pasien</th>
                        <th>MRN</th>
                        <th>Dokter</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pending as $a)
                        <tr>
                            <td>{{ $a->appointment_date->format('d M Y') }}</td>
                            <td><code>{{ substr((string) $a->appointment_time, 0, 5) }}</code></td>
                            <td class="fw-semibold">{{ $a->patient->name ?? '-' }}</td>
                            <td><small>{{ $a->patient->mrn ?? '-' }}</small></td>
                            <td>{{ $a->doctor->name ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('cashier.create', $a) }}" class="btn btn-sm btn-primary">Buat Tagihan</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada kunjungan selesai yang belum ditagih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Tagihan Terbaru</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pasien</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentInvoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}"><code>{{ $inv->invoice_number }}</code></a>
                            </td>
                            <td>{{ $inv->patient->name ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format((float) $inv->total, 0, ',', '.') }}</td>
                            <td>
                                @switch($inv->status)
                                    @case('draft')<span class="badge text-bg-secondary">Draft</span>@break
                                    @case('belum_bayar')<span class="badge text-bg-warning">Belum Bayar</span>@break
                                    @case('lunas')<span class="badge text-bg-success">Lunas</span>@break
                                    @case('batal')<span class="badge text-bg-danger">Batal</span>@break
                                @endswitch
                            </td>
                            <td><small class="text-muted">{{ $inv->created_at->format('d M Y H:i') }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">Detail / Bayar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection