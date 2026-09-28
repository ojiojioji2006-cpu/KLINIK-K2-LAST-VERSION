<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        $pending = Appointment::with(['patient', 'doctor'])
            ->where('status', Appointment::STATUS_DONE)
            ->whereDoesntHave('invoices')
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $recentInvoices = Invoice::with('patient')
            ->latest('id')
            ->take(15)
            ->get();

        return view('cashier.index', compact('pending', 'recentInvoices'));
    }

    public function create(Appointment $appointment): View
    {
        if ($appointment->status !== Appointment::STATUS_DONE) {
            return redirect()
                ->route('cashier.index')
                ->withErrors(['appointment' => 'Hanya kunjungan berstatus SELESAI yang bisa ditagih.']);
        }

        if ($appointment->invoices()->exists()) {
            return redirect()
                ->route('cashier.index')
                ->withErrors(['appointment' => 'Kunjungan ini sudah punya tagihan.']);
        }

        $mr = $appointment->medicalRecord;
        $rx = $mr ? Prescription::where('medical_record_id', $mr->id)->with('items.medicine')->first() : null;

        $obatLines = [];
        $obatTotal = 0.0;
        if ($rx) {
            foreach ($rx->items as $it) {
                $med = $it->medicine;
                if (! $med) { continue; }
                $price = (float) $med->sell_price;
                $qty = (int) $it->quantity;
                $sub = $price * $qty;
                $obatLines[] = [
                    'name' => $med->name,
                    'unit' => $med->unit,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $sub,
                ];
                $obatTotal += $sub;
            }
        }

        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('cashier.create', [
            'appointment' => $appointment,
            'patient' => $appointment->patient,
            'doctor' => $appointment->doctor,
            'obatLines' => $obatLines,
            'obatTotal' => $obatTotal,
            'services' => $services,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.service_id' => ['nullable', 'integer', 'exists:services,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $appointment = Appointment::with('patient')->findOrFail($data['appointment_id']);

        if ($appointment->status !== Appointment::STATUS_DONE) {
            return back()->withErrors(['appointment_id' => 'Hanya kunjungan SELESAI yang bisa ditagih.'])->withInput();
        }
        if ($appointment->invoices()->exists()) {
            return back()->withErrors(['appointment_id' => 'Kunjungan ini sudah punya tagihan.'])->withInput();
        }

        $obatLines = $this->collectObatLines($appointment);
        $obatTotal = array_sum(array_column($obatLines, 'subtotal'));

        $serviceLines = [];
        $serviceTotal = 0.0;
        $rawItems = collect($data['items'] ?? [])
            ->filter(fn ($l) => is_array($l) && ! empty($l['service_id']))
            ->values();
        foreach ($rawItems as $l) {
            $svc = Service::find($l['service_id']);
            if (! $svc) { continue; }
            $qty = isset($l['quantity']) && (int) $l['quantity'] >= 1 ? (int) $l['quantity'] : 1;
            $price = (float) $svc->price;
            $sub = $price * $qty;
            $serviceLines[] = [
                'type' => 'service',
                'ref' => $svc->id,
                'desc' => $svc->name,
                'qty' => $qty,
                'price' => $price,
                'sub' => $sub,
            ];
            $serviceTotal += $sub;
        }

        $subtotal = $obatTotal + $serviceTotal;
        $discount = (float) ($data['discount'] ?? 0);
        $tax = (float) ($data['tax'] ?? 0);

        if ($discount > $subtotal) {
            return back()->withErrors(['discount' => 'Diskon tidak boleh melebihi subtotal.'])->withInput();
        }

        $total = $subtotal - $discount + $tax;
        $invoiceNumber = $this->generateInvoiceNumber();

        DB::transaction(function () use ($appointment, $invoiceNumber, $subtotal, $discount, $tax, $total, $obatLines, $serviceLines) {
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'status' => Invoice::STATUS_UNPAID,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'created_by' => Auth::id(),
            ]);

            foreach ($obatLines as $o) {
                $invoice->items()->create([
                    'item_type' => 'medicine',
                    'reference_id' => $o['medicine_id'],
                    'description' => $o['name'],
                    'quantity' => $o['qty'],
                    'unit_price' => $o['price'],
                    'total_price' => $o['subtotal'],
                ]);
            }

            foreach ($serviceLines as $s) {
                $invoice->items()->create([
                    'item_type' => $s['type'],
                    'reference_id' => $s['ref'],
                    'description' => $s['desc'],
                    'quantity' => $s['qty'],
                    'unit_price' => $s['price'],
                    'total_price' => $s['sub'],
                ]);
            }
        });

        return redirect()
            ->route('cashier.index')
            ->with('success', "Tagihan {$invoiceNumber} dibuat (Belum Bayar). Total Rp " . number_format($total, 0, ',', '.'));
    }

    private function collectObatLines(Appointment $appointment): array
    {
        $lines = [];
        $mr = $appointment->medicalRecord;
        $rx = $mr ? Prescription::where('medical_record_id', $mr->id)->with('items.medicine')->first() : null;
        if (! $rx) { return $lines; }

        foreach ($rx->items as $it) {
            $med = $it->medicine;
            if (! $med) { continue; }
            $price = (float) $med->sell_price;
            $qty = (int) $it->quantity;
            $lines[] = [
                'medicine_id' => $med->id,
                'name' => $med->name,
                'unit' => $med->unit,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        }
        return $lines;
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        $last = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}