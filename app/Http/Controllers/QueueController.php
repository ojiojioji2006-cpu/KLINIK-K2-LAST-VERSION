<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function call(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status !== Appointment::STATUS_WAITING) {
            return back()->withErrors(['call' => 'Hanya pasien berstatus MENUNGGU yang bisa dipanggil.']);
        }

        $appointment->update(['called_at' => now()]);

        $name = $appointment->patient->name ?? 'Pasien';

        return back()->with('success', "Pasien {$name} dipanggil ke ruang periksa.");
    }

    public function display(): View
    {
        return view('display.index');
    }

    public function data(): JsonResponse
    {
        $list = Appointment::with(['patient', 'doctor'])
            ->where('status', Appointment::STATUS_WAITING)
            ->whereNotNull('called_at')
            ->orderByDesc('called_at')
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'id'              => $a->id,
                'mrn'             => $a->patient->mrn ?? '-',
                'name'            => $a->patient->name ?? '-',
                'doctor'          => $a->doctor->name ?? '-',
                'called_at'       => $a->called_at?->format('Y-m-d H:i:s'),
                'called_at_label' => $a->called_at?->format('H:i'),
            ])
            ->values();

        return response()->json($list);
    }
}