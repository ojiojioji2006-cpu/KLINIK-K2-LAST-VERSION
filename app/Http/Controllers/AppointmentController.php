<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    private const FILLING = [
        Appointment::STATUS_WAITING,
        Appointment::STATUS_IN_EXAM,
        Appointment::STATUS_DONE,
    ];

    private const TRANSITIONS = [
        Appointment::STATUS_WAITING => [
            Appointment::STATUS_IN_EXAM,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_NO_SHOW,
        ],
        Appointment::STATUS_IN_EXAM => [
            Appointment::STATUS_DONE,
        ],
        Appointment::STATUS_DONE => [],
        Appointment::STATUS_CANCELLED => [],
        Appointment::STATUS_NO_SHOW => [],
    ];

    private const LABELS = [
        Appointment::STATUS_WAITING => 'Menunggu',
        Appointment::STATUS_IN_EXAM => 'Dalam Pemeriksaan',
        Appointment::STATUS_DONE => 'Selesai',
        Appointment::STATUS_CANCELLED => 'Batal',
        Appointment::STATUS_NO_SHOW => 'Tidak Hadir',
    ];

    public function index(Request $request): View
    {
        $date = $request->query('date') ?: now()->toDateString();

        try {
            $carbon = Carbon::parse($date);
        } catch (\Throwable $e) {
            $carbon = now();
        }
        $date = $carbon->toDateString();

        $appointments = Appointment::with(['patient', 'doctor', 'medicalRecord'])
            ->whereDate('appointment_date', $date)
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->get();

        return view('appointments.index', compact('appointments', 'date'));
    }

    public function create(Request $request): View
    {
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        $patients = Patient::orderBy('name')->get();

        $doctorId = $request->query('doctor_id');
        $date = $request->query('date');

        $slots = [];
        $schedule = null;
        $searched = false;

        if ($doctorId && $date) {
            $searched = true;
            try {
                $carbon = Carbon::parse($date);
                $dayOfWeek = (int) $carbon->dayOfWeek;
                $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->first();

                if ($schedule) {
                    $used = Appointment::where('doctor_id', $doctorId)
                        ->whereDate('appointment_date', $carbon->toDateString())
                        ->whereIn('status', self::FILLING)
                        ->pluck('appointment_time')
                        ->map(fn ($t) => substr((string) $t, 0, 5))
                        ->countBy()
                        ->toArray();

                    foreach ($schedule->slots() as $time) {
                        $remaining = $schedule->max_patients_per_slot - ($used[$time] ?? 0);
                        if ($remaining > 0) {
                            $slots[] = ['time' => $time, 'remaining' => $remaining];
                        }
                    }
                }
            } catch (\Throwable $e) {
                $schedule = null;
            }
        }

        return view('appointments.create', compact(
            'doctors', 'patients', 'slots', 'schedule', 'searched', 'doctorId', 'date'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'chief_complaint' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $carbon = Carbon::parse($data['appointment_date']);
        } catch (\Throwable $e) {
            return back()->withErrors(['appointment_date' => 'Tanggal tidak valid.'])->withInput();
        }

        $dayOfWeek = (int) $carbon->dayOfWeek;
        $schedule = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return back()->withErrors(['appointment_time' => 'Dokter tidak punya jadwal aktif pada hari tersebut.'])->withInput();
        }

        if (! in_array($data['appointment_time'], $schedule->slots(), true)) {
            return back()->withErrors(['appointment_time' => 'Jam tersebut bukan slot valid untuk jadwal ini.'])->withInput();
        }

        $used = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $carbon->toDateString())
            ->where('appointment_time', $data['appointment_time'])
            ->whereIn('status', self::FILLING)
            ->count();

        if ($used >= $schedule->max_patients_per_slot) {
            return back()->withErrors(['appointment_time' => 'Slot ' . $data['appointment_time'] . ' sudah penuh. Pilih jam lain.'])->withInput();
        }

        Appointment::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'schedule_id' => $schedule->id,
            'appointment_date' => $carbon->toDateString(),
            'appointment_time' => $data['appointment_time'],
            'status' => Appointment::STATUS_WAITING,
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $patientName = Patient::where('id', $data['patient_id'])->value('name');

        return redirect()
            ->route('appointments.index', ['date' => $carbon->toDateString()])
            ->with('success', "Pasien {$patientName} terdaftar di antrean jam {$data['appointment_time']}.");
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $to = (string) $request->input('to');
        $allowed = self::TRANSITIONS[$appointment->status] ?? [];

        if (! in_array($to, $allowed, true)) {
            return redirect()
                ->route('appointments.index', ['date' => $appointment->appointment_date->toDateString()])
                ->withErrors(['status' => "Status '" . (self::LABELS[$appointment->status] ?? $appointment->status) . "' tidak bisa diubah ke '" . (self::LABELS[$to] ?? $to) . "'."]);
        }

        $appointment->update(['status' => $to]);

        $label = self::LABELS[$to] ?? $to;

        if ($to === Appointment::STATUS_IN_EXAM) {
            return redirect()
                ->route('medical_records.show', $appointment)
                ->with('success', "Pemeriksaan dimulai untuk {$label}.");
        }

        return redirect()
            ->route('appointments.index', ['date' => $appointment->appointment_date->toDateString()])
            ->with('success', "Antrean diubah ke {$label}.");
    }
}