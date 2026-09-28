@extends('layouts.app')

@section('title', 'Data Dokter')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Dokter</h4>
        <a href="{{ route('doctors.create') }}" class="btn btn-primary">+ Tambah Dokter</a>
    </div>

    <form method="GET" action="{{ route('doctors.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="q" value="{{ $q }}" class="form-control"
                   placeholder="Cari nama / spesialis / SIP...">
            <button class="btn btn-outline-secondary" type="submit">Cari</button>
            @if ($q !== '')
                <a href="{{ route('doctors.index') }}" class="btn btn-outline-danger">Reset</a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>SIP</th>
                        <th>Spesialis</th>
                        <th>No. HP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $d)
                        <tr>
                            <td class="fw-semibold">{{ $d->name }}</td>
                            <td>{{ $d->sip ?? '-' }}</td>
                            <td>{{ $d->specialty ?? '-' }}</td>
                            <td>{{ $d->phone ?? '-' }}</td>
                            <td>
                                @if ($d->is_active)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada dokter. Klik "+ Tambah Dokter" untuk mulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $doctors->links('pagination::bootstrap-5') }}</div>
@endsection