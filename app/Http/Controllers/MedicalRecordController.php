<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function show(Appointment $appointment): View
    {
        if (! in_array($appointment->status, [Appointment::STATUS_IN_EXAM, Appointment::STATUS_DONE], true)) {
            return redirect()
                ->route('appointments.index', ['date' => $appointment->appointment_date->toDateString()])
                ->withErrors(['status' => 'Mulai pemeriksaan dulu sebelum membuka rekam medis.']);
        }

        $record = MedicalRecord::firstOrNew(['appointment_id' => $appointment->id]);

        if (! $record->exists && ! $record->complaint) {
            $record->complaint = $appointment->chief_complaint;
        }

        $rx = Prescription::where('medical_record_id', $record->getKey())
            ->with('items.medicine')
            ->first();

        return view('medical-records.form', [
            'appointment'   => $appointment,
            'record'        => $record,
            'patient'       => $appointment->patient,
            'doctor'        => $appointment->doctor,
            'existingItems' => $rx ? $rx->items : collect(),
            'medicines'     => Medicine::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function save(Request $request, Appointment $appointment): RedirectResponse
    {
        if (! in_array($appointment->status, [Appointment::STATUS_IN_EXAM, Appointment::STATUS_DONE], true)) {
            return redirect()
                ->route('appointments.index', ['date' => $appointment->appointment_date->toDateString()])
                ->withErrors(['status' => 'Kunjungan ini belum/sudah tidak dalam tahap pemeriksaan.']);
        }

        $data = $request->validate([
            'complaint'               => ['nullable', 'string', 'max:2000'],
            'anamnesis'               => ['nullable', 'string', 'max:4000'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'between:50,300'],
            'blood_pressure_diastolic'=> ['nullable', 'integer', 'between:30,200'],
            'heart_rate'              => ['nullable', 'integer', 'between:20,250'],
            'respiratory_rate'        => ['nullable', 'integer', 'between:5,80'],
            'temperature'             => ['nullable', 'numeric', 'between:25,45'],
            'weight'                  => ['nullable', 'numeric', 'between:0,500'],
            'height'                  => ['nullable', 'numeric', 'between:0,250'],
            'physical_examination'    => ['nullable', 'string', 'max:4000'],
            'diagnosis_text'          => ['nullable', 'string', 'max:2000'],
            'treatment_plan'          => ['nullable', 'string', 'max:4000'],
            'additional_notes'        => ['nullable', 'string', 'max:4000'],

            'items'                   => ['nullable', 'array'],
            'items.*.medicine_id'     => ['nullable', 'integer', 'exists:medicines,id'],
            'items.*.quantity'        => ['nullable', 'integer', 'min:1', 'max:9999'],
            'items.*.dose'            => ['nullable', 'string', 'max:100'],
            'items.*.frequency'       => ['nullable', 'string', 'max:100'],
            'items.*.instruction'     => ['nullable', 'string', 'max:255'],
        ]);

        $finalize = $request->boolean('finalize') && $appointment->status === Appointment::STATUS_IN_EXAM;
        $rmData = Arr::except($data, 'items');

        DB::transaction(function () use ($request, $appointment, $rmData, $data, $finalize) {
            $record = MedicalRecord::firstOrNew(['appointment_id' => $appointment->id]);
            $isNew = ! $record->exists;

            $record->fill($rmData);

            if ($isNew) {
                $record->patient_id = $appointment->patient_id;
                $record->doctor_id  = $appointment->doctor_id;
                $record->visit_date = now();
                $record->created_by = Auth::id();
            }

            $record->save();

            $lines = collect($data['items'] ?? [])
                ->filter(fn ($l) => is_array($l) && ! empty($l['medicine_id']))
                ->values();

            $rx = Prescription::firstOrNew(['medical_record_id' => $record->id]);

            if ($lines->isEmpty()) {
                if ($rx->exists) {
                    $rx->delete();
                }
            } else {
                $rx->doctor_id  = $appointment->doctor_id;
                $rx->patient_id = $appointment->patient_id;
                if (! $rx->exists) {
                    $rx->status = Prescription::STATUS_DRAFT;
                }
                $rx->save();

                $rx->items()->delete();

                foreach ($lines as $l) {
                    $med = Medicine::find($l['medicine_id']);
                    $qty = isset($l['quantity']) && (int) $l['quantity'] >= 1 ? (int) $l['quantity'] : 1;
                    $unit = $med ? (float) $med->sell_price : 0.0;

                    $rx->items()->create([
                        'medicine_id' => $l['medicine_id'],
                        'quantity'    => $qty,
                        'dose'        => $l['dose'] ?? null,
                        'frequency'   => $l['frequency'] ?? null,
                        'instruction' => $l['instruction'] ?? null,
                        'unit_price'  => $unit,
                        'total_price' => $unit * $qty,
                    ]);
                }
            }

            if ($finalize) {
                $appointment->update(['status' => Appointment::STATUS_DONE]);
            }
        });

        if ($finalize) {
            return redirect()
                ->route('appointments.index', ['date' => $appointment->appointment_date->toDateString()])
                ->with('success', 'Rekam medis & resep tersimpan. Kunjungan ditandai SELESAI.');
        }

        return redirect()
            ->route('medical_records.show', $appointment)
            ->with('success', 'Rekam medis & resep tersimpan.');
    }
}