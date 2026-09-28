<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\InvoiceItem;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const KEYS = ['visits', 'income', 'medicines', 'doctors', 'diagnoses'];

    private const METHODS = ['tunai', 'kartu', 'transfer', 'asuransi', 'lainnya'];

    public function index(): View
    {
        $cards = [
            ['key' => 'visits',    'title' => 'Kunjungan & Pasien',   'desc' => 'Pasien baru + arus antrean per hari (masuk/selesai/batal/tidak hadir).'],
            ['key' => 'income',    'title' => 'Pendapatan',           'desc' => 'Kas masuk per hari, dipecah per metode pembayaran.'],
            ['key' => 'medicines', 'title' => 'Obat Keluar',          'desc' => 'Obat yang terdistribusi dari tagihan LUNAS + pendapatannya.'],
            ['key' => 'doctors',   'title' => 'Kinerja Dokter',       'desc' => 'Beban kunjungan & tingkat penyelesaian per dokter.'],
            ['key' => 'diagnoses', 'title' => 'Diagnosis Tersering',  'desc' => 'Pemetaan diagnosis dari teks rekam medis (lihat catatan di halaman).'],
        ];
        return view('reports.index', compact('cards'));
    }

    public function show(Request $request, string $report): View
    {
        abort_unless(in_array($report, self::KEYS, true), 404);
        $range  = $this->resolveRange($request);
        $data   = $this->aggregate($report, $range);
        $display = array_map(
            fn (array $row) => array_map(
                fn ($v, $i) => $this->fmtDisplay($v, $data['columns'][$i]['type'] ?? 'text'),
                $row,
                array_keys($row)
            ),
            $data['rows']
        );
        return view('reports.show', [
            'report'  => $data,
            'range'   => $range,
            'display' => $display,
        ]);
    }

    public function csv(Request $request, string $report): StreamedResponse
    {
        abort_unless(in_array($report, self::KEYS, true), 404);
        $range = $this->resolveRange($request);
        $data  = $this->aggregate($report, $range);
        $cols  = $data['columns'];
        $rows  = $data['rows'];

        $filename = "laporan_{$report}_{$range['from']}_{$range['to']}.csv";

        return response()->stream(function () use ($cols, $rows) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: biar Excel (ID) baca UTF-8 dengan benar
            fputcsv($out, array_map(fn ($c) => $c['label'], $cols));
            foreach ($rows as $row) {
                $line = [];
                foreach ($row as $i => $v) {
                    $line[] = $this->fmtCsv($v, $cols[$i]['type'] ?? 'text');
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function aggregate(string $key, array $r): array
    {
        return match ($key) {
            'visits'    => $this->aggVisits($r),
            'income'    => $this->aggIncome($r),
            'medicines' => $this->aggMedicines($r),
            'doctors'   => $this->aggDoctors($r),
            'diagnoses' => $this->aggDiagnoses($r),
        };
    }

    // ---------- AGREGAT 1: KUNJUNGAN & PASIEN ----------
    private function aggVisits(array $r): array
    {
        $appts = Appointment::whereBetween('appointment_date', [$r['from'], $r['to']])
            ->selectRaw("DATE(appointment_date) AS d, COUNT(*) AS total,
                SUM(status='selesai') AS selesai,
                SUM(status='batal') AS batal,
                SUM(status='tidak_hadir') AS no_show")
            ->groupBy('d')->orderBy('d')->get();

        $newP = Patient::whereBetween('created_at', [$r['fromStart'], $r['toEnd']])
            ->selectRaw("DATE(created_at) AS d, COUNT(*) AS c")
            ->groupBy('d')->pluck('c', 'd');

        $byDate = [];
        foreach ($appts as $a) {
            $byDate[$a->d] = ['date' => $a->d, 'new' => 0, 'total' => (int) $a->total,
                'selesai' => (int) $a->selesai, 'batal' => (int) $a->batal, 'no_show' => (int) $a->no_show];
        }
        foreach ($newP as $d => $c) {
            if (! isset($byDate[$d])) {
                $byDate[$d] = ['date' => $d, 'new' => 0, 'total' => 0, 'selesai' => 0, 'batal' => 0, 'no_show' => 0];
            }
            $byDate[$d]['new'] = (int) $c;
        }
        ksort($byDate);

        $rows = []; $tNew = 0; $tTot = 0; $tSe = 0;
        foreach ($byDate as $b) {
            $rows[] = [$b['date'], $b['new'], $b['total'], $b['selesai'], $b['batal'], $b['no_show']];
            $tNew += $b['new']; $tTot += $b['total']; $tSe += $b['selesai'];
        }
        $rate = $tTot > 0 ? round($tSe / $tTot * 100, 1) : 0.0;

        return [
            'key' => 'visits', 'title' => 'Laporan Kunjungan & Pasien',
            'columns' => [
                ['label' => 'Tanggal', 'type' => 'date', 'align' => 'start'],
                ['label' => 'Pasien Baru', 'type' => 'int', 'align' => 'end'],
                ['label' => 'Kunjungan', 'type' => 'int', 'align' => 'end'],
                ['label' => 'Selesai', 'type' => 'int', 'align' => 'end'],
                ['label' => 'Batal', 'type' => 'int', 'align' => 'end'],
                ['label' => 'Tidak Hadir', 'type' => 'int', 'align' => 'end'],
            ],
            'rows' => $rows,
            'summary' => [
                ['label' => 'Pasien Baru', 'value' => $this->fmtDisplay($tNew, 'int')],
                ['label' => 'Total Kunjungan', 'value' => $this->fmtDisplay($tTot, 'int')],
                ['label' => 'Selesai', 'value' => $this->fmtDisplay($tSe, 'int')],
                ['label' => 'Tingkat Selesai', 'value' => $this->fmtDisplay($rate, 'percent')],
            ],
            'note' => null,
        ];
    }

    // ---------- AGREGAT 2: PENDAPATAN ----------
    private function aggIncome(array $r): array
    {
        $ps = Payment::whereBetween('paid_at', [$r['fromStart'], $r['toEnd']])
            ->selectRaw("DATE(paid_at) AS d, method, COUNT(*) AS c, SUM(amount) AS s")
            ->groupBy('d', 'method')->orderBy('d')->get();

        $byDate = [];
        foreach ($ps as $p) {
            if (! isset($byDate[$p->d])) {
                $byDate[$p->d] = ['date' => $p->d, 'tx' => 0, 'total' => 0.0] + array_fill_keys(self::METHODS, 0.0);
            }
            $m = in_array($p->method, self::METHODS, true) ? $p->method : 'lainnya';
            $byDate[$p->d][$m] += (float) $p->s;
            $byDate[$p->d]['tx'] += (int) $p->c;
            $byDate[$p->d]['total'] += (float) $p->s;
        }
        ksort($byDate);

        $rows = []; $sumM = array_fill_keys(self::METHODS, 0.0); $gTx = 0; $gTot = 0.0;
        foreach ($byDate as $b) {
            $row = [$b['date'], $b['tx']];
            foreach (self::METHODS as $m) { $row[] = $b[$m]; $sumM[$m] += $b[$m]; }
            $row[] = $b['total'];
            $rows[] = $row;
            $gTx += $b['tx']; $gTot += $b['total'];
        }
        $topMethod = $sumM ? array_search(max($sumM), $sumM, true) : '-';

        $cols = [['label' => 'Tanggal', 'type' => 'date', 'align' => 'start'],
                 ['label' => 'Transaksi', 'type' => 'int', 'align' => 'end']];
        foreach (self::METHODS as $m) {
            $cols[] = ['label' => ucfirst($m), 'type' => 'money', 'align' => 'end'];
        }
        $cols[] = ['label' => 'Total', 'type' => 'money', 'align' => 'end'];

        return [
            'key' => 'income', 'title' => 'Laporan Pendapatan (Kas Masuk)',
            'columns' => $cols, 'rows' => $rows,
            'summary' => [
                ['label' => 'Total Kas Masuk', 'value' => $this->fmtDisplay($gTot, 'money')],
                ['label' => 'Jumlah Transaksi', 'value' => $this->fmtDisplay($gTx, 'int')],
                ['label' => 'Metode Terbanyak', 'value' => $topMethod ? ucfirst($topMethod) : '-'],
            ],
            'note' => 'Menghitung seluruh pembayaran yang tercatat pada periode (kas riil masuk), lintas status tagihan.',
        ];
    }

    // ---------- AGREGAT 3: OBAT KELUAR ----------
    private function aggMedicines(array $r): array
    {
        $qs = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.status', 'lunas')
            ->whereBetween('invoices.updated_at', [$r['fromStart'], $r['toEnd']])
            ->where('invoice_items.item_type', 'medicine')
            ->selectRaw("invoice_items.description AS name,
                SUM(invoice_items.quantity) AS qty,
                SUM(invoice_items.total_price) AS rev")
            ->groupBy('invoice_items.description')
            ->orderByDesc('rev')->get();

        $rows = []; $tQty = 0; $tRev = 0.0;
        foreach ($qs as $q) {
            $rows[] = [$q->name, (int) $q->qty, (float) $q->rev];
            $tQty += (int) $q->qty; $tRev += (float) $q->rev;
        }

        return [
            'key' => 'medicines', 'title' => 'Laporan Obat Keluar',
            'columns' => [
                ['label' => 'Obat', 'type' => 'text', 'align' => 'start'],
                ['label' => 'Qty Keluar', 'type' => 'int', 'align' => 'end'],
                ['label' => 'Pendapatan', 'type' => 'money', 'align' => 'end'],
            ],
            'rows' => $rows,
            'summary' => [
                ['label' => 'Total Qty Keluar', 'value' => $this->fmtDisplay($tQty, 'int')],
                ['label' => 'Pendapatan Obat', 'value' => $this->fmtDisplay($tRev, 'money')],
                ['label' => 'Jenis Obat', 'value' => $this->fmtDisplay(count($rows), 'int')],
            ],
            'note' => 'Atribusi waktu = saat tagihan DILUNASI (invoices.updated_at), mengikuti momen stok dipotong. Obat pada tagihan yang belum lunas tidak dihitung.',
        ];
    }

    // ---------- AGREGAT 4: KINERJA DOKTER ----------
    private function aggDoctors(array $r): array
    {
        $ag = Appointment::whereBetween('appointment_date', [$r['from'], $r['to']])
            ->selectRaw("doctor_id, COUNT(*) AS total, SUM(status='selesai') AS selesai")
            ->groupBy('doctor_id')->get();

        $docs = Doctor::whereIn('id', $ag->pluck('doctor_id'))->get()->keyBy('id');

        $sorted = $ag->sortByDesc(fn ($x) => (int) $x->selesai)->values();
        $rows = []; $tTot = 0; $tSe = 0;
        foreach ($sorted as $a) {
            $d = $docs->get($a->doctor_id);
            $tot = (int) $a->total; $se = (int) $a->selesai;
            $rate = $tot > 0 ? round($se / $tot * 100, 1) : 0.0;
            $rows[] = [$d->name ?? ('#' . $a->doctor_id), $d->specialty ?? '-', $tot, $se, $rate];
            $tTot += $tot; $tSe += $se;
        }

        return [
            'key' => 'doctors', 'title' => 'Laporan Kinerja Dokter',
            'columns' => [
                ['label' => 'Dokter', 'type' => 'text', 'align' => 'start'],
                ['label' => 'Spesialis', 'type' => 'text', 'align' => 'start'],
                ['label' => 'Kunjungan', 'type' => 'int', 'align' => 'end'],
                ['label' => 'Selesai', 'type' => 'int', 'align' => 'end'],
                ['label' => '% Selesai', 'type' => 'percent', 'align' => 'end'],
            ],
            'rows' => $rows,
            'summary' => [
                ['label' => 'Dokter Bervisit', 'value' => $this->fmtDisplay(count($rows), 'int')],
                ['label' => 'Total Kunjungan', 'value' => $this->fmtDisplay($tTot, 'int')],
                ['label' => 'Total Selesai', 'value' => $this->fmtDisplay($tSe, 'int')],
            ],
            'note' => null,
        ];
    }

    // ---------- AGREGAT 5: DIAGNOSIS (TEKS BEBAS) ----------
    private function aggDiagnoses(array $r): array
    {
        $texts = MedicalRecord::whereBetween('visit_date', [$r['fromStart'], $r['toEnd']])
            ->whereNotNull('diagnosis_text')
            ->whereRaw("TRIM(diagnosis_text) <> ''")
            ->pluck('diagnosis_text');

        $map = [];
        foreach ($texts as $t) {
            $norm = trim(preg_replace('/\s+/', ' ', (string) $t));
            if ($norm === '') { continue; }
            $key = mb_strtolower($norm);
            if (! isset($map[$key])) { $map[$key] = ['label' => $norm, 'count' => 0]; }
            $map[$key]['count']++;
        }
        $list = array_values($map);
        usort($list, fn ($a, $b) => $b['count'] <=> $a['count']);
        $list = array_slice($list, 0, 20);

        $rows = []; $total = 0;
        foreach ($list as $it) { $rows[] = [$it['label'], $it['count']]; $total += $it['count']; }

        return [
            'key' => 'diagnoses', 'title' => 'Laporan Diagnosis Tersering',
            'columns' => [
                ['label' => 'Diagnosis', 'type' => 'text', 'align' => 'start'],
                ['label' => 'Jumlah', 'type' => 'int', 'align' => 'end'],
            ],
            'rows' => $rows,
            'summary' => [
                ['label' => 'Total Entri', 'value' => $this->fmtDisplay($total, 'int')],
                ['label' => 'Variasi Diagnosis', 'value' => $this->fmtDisplay(count($list), 'int')],
            ],
            'note' => 'Sumber: kolom teks bebas diagnosis_text pada rekam medis (dinormalisasi spasi & huruf besar-kecil). Karena teks bebas, penulisan berbeda ("ISPA" vs "ispa") dapat terhitung terpisah. Diagnosis terstruktur (kode ICD, tabel diagnoses) akan dipanen pada ronde polish saat input diagnosis terstruktur ditambahkan.',
        ];
    }

    // ---------- RANGE ----------
    private function resolveRange(Request $request): array
    {
        $defFrom = Carbon::today()->startOfMonth()->toDateString();
        $defTo   = Carbon::today()->toDateString();

        $from = $this->parseDate($request->query('from'), $defFrom);
        $to   = $this->parseDate($request->query('to'), $defTo);
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        return [
            'from' => $from, 'to' => $to,
            'fromStart' => $from . ' 00:00:00',
            'toEnd'     => $to . ' 23:59:59',
            'label'     => Carbon::parse($from)->format('d M Y') . ' – ' . Carbon::parse($to)->format('d M Y'),
        ];
    }

    private function parseDate(?string $v, string $fallback): string
    {
        if (! $v) { return $fallback; }
        try { return Carbon::parse($v)->toDateString(); } catch (\Throwable $e) { return $fallback; }
    }

    // ---------- FORMATTER ----------
    private function fmtDisplay($v, string $type): string
    {
        if ($v === null || $v === '') { return '-'; }
        return match ($type) {
            'money'   => 'Rp ' . number_format((float) $v, 0, ',', '.'),
            'int'     => number_format((int) $v, 0, ',', '.'),
            'percent' => number_format((float) $v, 1, ',', '.') . '%',
            'date'    => Carbon::parse((string) $v)->format('d M Y'),
            default   => (string) $v,
        };
    }

    private function fmtCsv($v, string $type)
    {
        if ($v === null) { return ''; }
        return match ($type) {
            'date'    => Carbon::parse((string) $v)->format('Y-m-d'),
            'money', 'int' => (string) (is_numeric($v) ? $v + 0 : 0),
            'percent' => (string) (is_numeric($v) ? $v + 0 : 0),
            default   => (string) $v,
        };
    }
}