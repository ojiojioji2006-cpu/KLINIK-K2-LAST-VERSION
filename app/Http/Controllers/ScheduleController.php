<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = DoctorSchedule::with('doctor')
            ->orderBy('doctor_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20);

        return view('schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('schedules.create', compact('doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'max_patients_per_slot' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $dup = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', $data['start_time'])
            ->where('is_active', true)
            ->exists();

        if ($dup) {
            return back()
                ->withErrors(['start_time' => 'Sudah ada jadwal aktif dokter ini di hari & jam mulai yang sama.'])
                ->withInput();
        }

        $data['is_active'] = $request->boolean('is_active');

        DoctorSchedule::create($data);

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal praktik berhasil ditambahkan.');
    }
}