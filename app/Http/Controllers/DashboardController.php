<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function index(): View
    {
        $todayStr = now()->toDateString();

        $today = fn () => Appointment::whereDate('appointment_date', $todayStr);

        $stats = [
            'waiting'     => $today()->where('status', Appointment::STATUS_WAITING)->count(),
            'in_exam'     => $today()->where('status', Appointment::STATUS_IN_EXAM)->count(),
            'done'        => $today()->where('status', Appointment::STATUS_DONE)->count(),
            'cancelled'   => $today()->where('status', Appointment::STATUS_CANCELLED)->count(),
            'no_show'     => $today()->where('status', Appointment::STATUS_NO_SHOW)->count(),
            'total_today' => $today()->count(),
        ];

        $cashToday = (float) Payment::whereDate('paid_at', $todayStr)->sum('amount');

        $openIds   = Invoice::whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_UNPAID])->pluck('id');
        $openCount = $openIds->count();
        $openTotal = (float) Invoice::whereIn('id', $openIds)->sum('total');
        $openPaid  = $openIds->isEmpty() ? 0.0 : (float) Payment::whereIn('invoice_id', $openIds)->sum('amount');
        $netReceivable = max(0.0, round($openTotal - $openPaid, 2));

        $totalPatients = Patient::count();

        $lowStock = Medicine::where('is_active', true)
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->orderBy('stock')
            ->get();

        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', $todayStr)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $recentInvoices = Invoice::with('patient')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'stats', 'cashToday', 'openCount', 'openTotal', 'netReceivable',
            'totalPatients', 'lowStock', 'recentAppointments', 'recentInvoices', 'todayStr'
        ));
    }
}