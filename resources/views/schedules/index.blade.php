@extends('layouts.app')

@section('title', 'Jadwal Dokter')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Jadwal Praktik Dokter</h4>
        <a href="{{ route('schedules.create') }}" class="btn btn-primary">+ Tambah Jadwal</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Dokter</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Slot ({{ (int) $schedules->first()?->slot_minutes ?? 15 }} mnt)</th>
                        <th>Maks/slot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s->doctor->name ?? '-' }}</td>
                            <td>{{ $s->dayName() }}</td>
                            <td><code>{{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}</code></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($s->slots() as $t)
                                        <span class="badge text-bg-light border">{{ $t }}</span>
                                    @endforeach
                                </div>
                                <small class="text-muted">{{ $s->slotCount() }} slot</small>
                            </td>
                            <td>{{ $s->max_patients_per_slot }}</td>
                            <td>
                                @if ($s->is_active)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada jadwal. Klik "+ Tambah Jadwal" untuk mulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $schedules->links('pagination::bootstrap-5') }}</div>
@endsection