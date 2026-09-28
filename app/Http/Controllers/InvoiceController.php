<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'patient',
            'appointment.doctor',
            'createdBy',
            'items',
            'payments.createdBy',
        ]);

        $alreadyPaid = (float) $invoice->payments()->sum('amount');
        $due = round((float) $invoice->total - $alreadyPaid, 2);
        if ($due < 0) { $due = 0.0; }

        return view('invoices.show', [
            'invoice'     => $invoice,
            'alreadyPaid' => $alreadyPaid,
            'due'         => $due,
        ]);
    }

    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED], true)) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->withErrors(['amount' => 'Tagihan ini sudah lunas atau dibatalkan.']);
        }

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1'],
            'method'         => ['required', 'in:tunai,kartu,transfer,asuransi,lainnya'],
            'receipt_number' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = round((float) $data['amount'], 2);

        $result = DB::transaction(function () use ($invoice, $amount, $data) {
            $inv = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if (in_array($inv->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages([
                    'amount' => 'Tagihan ini sudah lunas atau dibatalkan (terdeteksi saat diproses).',
                ]);
            }

            $alreadyPaid = (float) $inv->payments()->sum('amount');
            $total = (float) $inv->total;
            $due = round($total - $alreadyPaid, 2);
            if ($due <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Tidak ada sisa tagihan yang perlu dibayar.',
                ]);
            }
            if ($amount > $due) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah melebihi sisa tagihan (sisa Rp ' . number_format($due, 0, ',', '.') . ').',
                ]);
            }

            $willLunas = round($alreadyPaid + $amount, 2) >= $total;

            $medItems = $inv->items()->where('item_type', 'medicine')->get();

            if ($willLunas && $medItems->isNotEmpty()) {
                $refs = $medItems->pluck('reference_id')->filter()->unique()->values();
                $stocks = Medicine::whereIn('id', $refs)->get()->keyBy('id');

                $shortages = [];
                foreach ($medItems as $mi) {
                    $med = $stocks->get($mi->reference_id);
                    $need = (int) $mi->quantity;
                    $have = $med ? (int) $med->stock : 0;
                    if ($have < $need) {
                        $shortages[] = ($med->name ?? ('#' . $mi->reference_id)) . " butuh {$need}, tersedia {$have}";
                    }
                }
                if (! empty($shortages)) {
                    throw ValidationException::withMessages([
                        'amount' => 'Stok tidak cukup untuk melunasi: ' . implode('; ', $shortages) . '.',
                    ]);
                }
            }

            $inv->payments()->create([
                'amount'         => $amount,
                'method'         => $data['method'],
                'receipt_number' => $data['receipt_number'] ?? null,
                'paid_at'        => now(),
                'notes'          => $data['notes'] ?? null,
                'created_by'     => Auth::id(),
            ]);

            if ($willLunas) {
                foreach ($medItems as $mi) {
                    $need = (int) $mi->quantity;
                    $affected = DB::table('medicines')
                        ->where('id', $mi->reference_id)
                        ->where('stock', '>=', $need)
                        ->decrement('stock', $need);

                    if ($affected === 0) {
                        throw ValidationException::withMessages([
                            'amount' => 'Stok berubah saat diproses. Silakan coba lagi.',
                        ]);
                    }
                }

                $inv->status = Invoice::STATUS_PAID;
                $inv->save();
            }

            return [
                'lunas' => $willLunas,
                'due'   => round($total - ($alreadyPaid + $amount), 2),
            ];
        });

        if ($result['lunas']) {
            return redirect()
                ->route('invoices.receipt', $invoice)
                ->with('success', 'Tagihan LUNAS. Stok obat telah disesuaikan.');
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Pembayaran Rp ' . number_format($amount, 0, ',', '.') . ' tercatat. Sisa tagihan Rp ' . number_format($result['due'], 0, ',', '.') . '.');
    }

    public function receipt(Invoice $invoice): View
    {
        $invoice->load([
            'patient',
            'appointment.doctor',
            'createdBy',
            'items',
            'payments.createdBy',
        ]);

        $alreadyPaid = (float) $invoice->payments()->sum('amount');

        return view('invoices.receipt', [
            'invoice'     => $invoice,
            'alreadyPaid' => $alreadyPaid,
        ]);
    }
}