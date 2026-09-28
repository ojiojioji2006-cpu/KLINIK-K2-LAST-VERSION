@extends('layouts.app')

@section('title', $report['title'])

@section('content')
    @php
        $base = url()->current();
        $now = \Carbon\Carbon::today();
        $presets = [
            'Bulan ini'  => [$now->copy()->startOfMonth()->toDateString(), $now->toDateString()],
            'Bulan lalu' => [$now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            '30 hari'    => [$now->copy()->subDays(29)->toDateString(), $now->toDateString()],
            'Tahun ini'  => [$now->copy()->startOfYear()->toDateString(), $now->toDateString()],
        ];
        $exportUrl = route('reports.csv', $report['key']) . '?from=' . $range['from'] . '&to=' . $range['to'];
    @endphp

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0">{{ $report['title'] }}</h4>
            <span class="text-muted small">{{ $range['label'] }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Semua Laporan</a>
            <a href="{{ $exportUrl }}" class="btn btn-sm btn-dark">⬇ Ekspor CSV</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ $base }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $range['from'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $range['to'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                    @foreach ($presets as $nm => $pr)
                        <a href="{{ $base }}?from={{ $pr[0] }}&to={{ $pr[1] }}" class="btn btn-sm btn-outline-secondary">{{ $nm }}</a>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

    @if (! empty($report['note']))
        <div class="alert alert-info small">{{ $report['note'] }}</div>
    @endif

    @if (! empty($report['summary']))
        <div class="row g-3 mb-3">
            @foreach ($report['summary'] as $s)
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small">{{ $s['label'] }}</div>
                            <div class="fs-5 fw-bold">{{ $s['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach ($report['columns'] as $col)
                            <th class="text-{{ $col['align'] ?? 'start' }}">{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($display as $row)
                        <tr>
                            @foreach ($row as $i => $cell)
                                <td class="text-{{ $report['columns'][$i]['align'] ?? 'start' }}">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columns']) }}" class="text-center text-muted py-4">
                                Tidak ada data pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            {{ count($display) }} baris &middot; periode {{ $range['label'] }}
        </div>
    </div>
@endsection