@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <h4 class="mb-1">Laporan</h4>
    <p class="text-muted small mb-3">Pilih laporan. Tiap halaman punya filter rentang tanggal &amp; tombol ekspor CSV.</p>

    <div class="row g-3">
        @foreach ($cards as $c)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $c['title'] }}</h5>
                        <p class="card-text text-muted small flex-grow-1">{{ $c['desc'] }}</p>
                        <a href="{{ route('reports.show', $c['key']) }}" class="btn btn-sm btn-primary align-self-start">Buka Laporan</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection