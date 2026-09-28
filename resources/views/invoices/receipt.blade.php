<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; }
        .struk { max-width: 380px; margin: 24px auto; font-size: 13px; }
        .struk .brand { text-align: center; font-weight: 700; font-size: 16px; }
        .struk .meta { text-align: center; color: #666; font-size: 12px; }
        .struk table { font-size: 12px; }
        .struk .totals td { padding-top: 2px; padding-bottom: 2px; }
        .no-print { text-align: center; margin: 16px auto; max-width: 380px; }
        @media print {
            .no-print { display: none !important; }
            .struk { margin: 0; max-width: 100%; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali ke Detail</a>
        <span class="text-muted ms-2">Tekan <kbd>Ctrl</kbd>+<kbd>P</kbd> (atau <kbd>Cmd</kbd>+<kbd>P</kbd>) untuk mencetak / simpan PDF.</span>
    </div>

    <div class="struk">
        <div class="brand">KLINIK SEDERHANA</div>
        <div class="meta">Struk Pembayaran</div>
        <hr class="my-2">

        <div class="d-flex justify-content-between"><span>No. Invoice</span><strong>{{ $invoice->invoice_number }}</strong></div>
        <div class="d-flex justify-content-between"><span>Tanggal</span><span>{{ $invoice->created_at->format('d M Y H:i') }}</span></div>
        <div class="d-flex justify-content-between"><span>Pasien</span><span>{{ $invoice->patient->name ?? '-' }}</span></div>
        <div class="d-flex justify-content-between"><span>MRN</span><span>{{ $invoice->patient->mrn ?? '-' }}</span></div>
        <div class="d-flex justify-content-between"><span>Dokter</span><span>{{ $invoice->appointment->doctor->name ?? '-' }}</span></div>
        <hr class="my-2">

        <table class="table table-sm mb-1">
            <thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Jml</th></tr></thead>
            <tbody>
                @foreach ($invoice->items as $it)
                    <tr>
                        <td>{{ $it->description }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format((float)$it->quantity, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="text-end">{{ number_format((float) $it->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr class="my-1">

        <table class="table table-sm totals mb-0">
            <tbody>
                <tr><td>Subtotal</td><td class="text-end">{{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td></tr>
                <tr><td>Diskon</td><td class="text-end">- {{ number_format((float) $invoice->discount, 0, ',', '.') }}</td></tr>
                <tr><td>Pajak</td><td class="text-end">+ {{ number_format((float) $invoice->tax, 0, ',', '.') }}</td></tr>
                <tr class="fw-bold"><td>TOTAL</td><td class="text-end">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td></tr>
                <tr><td>Dibayar</td><td class="text-end">Rp {{ number_format($alreadyPaid, 0, ',', '.') }}</td></tr>
                <tr class="fw-bold"><td>Sisa</td><td class="text-end">Rp {{ number_format(max(0,(float)$invoice->total - $alreadyPaid), 0, ',', '.') }}</td></tr>
            </tbody>
        </table>

        @if($invoice->payments->isNotEmpty())
            <hr class="my-2">
            <div class="text-muted" style="font-size:11px">Riwayat pembayaran:</div>
            @foreach ($invoice->payments->sortBy('id') as $pay)
                <div class="d-flex justify-content-between" style="font-size:11px">
                    <span>{{ $pay->paid_at->format('d/m H:i') }} &middot; {{ ucfirst($pay->method) }}</span>
                    <span>Rp {{ number_format((float) $pay->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
        @endif

        <hr class="my-2">
        <div class="meta">Terima kasih atas kunjungan Anda.</div>
        <div class="meta">Dicas oleh: {{ $invoice->createdBy->name ?? '-' }} &middot; {{ now()->format('d M Y H:i') }}</div>
    </div>
</body>
</html>