<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Layar panggilan ruang tunggu (PUBLIK, tanpa login — untuk TV)
Route::get('display', [QueueController::class, 'display'])->name('display');
Route::get('display/data', [QueueController::class, 'data'])->name('display.data');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin,perawat')->group(function () {
        Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
        Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
        Route::post('patients', [PatientController::class, 'store'])->name('patients.store');

        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');

        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    });

    Route::middleware('role:admin,perawat,dokter')->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::post('appointments/{appointment}/call', [QueueController::class, 'call'])->name('appointments.call');
    });

    Route::middleware('role:admin,dokter')->group(function () {
        Route::get('appointments/{appointment}/medical-record', [MedicalRecordController::class, 'show'])->name('medical_records.show');
        Route::post('appointments/{appointment}/medical-record', [MedicalRecordController::class, 'save'])->name('medical_records.save');
    });

    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('cashier', [CashierController::class, 'index'])->name('cashier.index');
        Route::get('cashier/appointment/{appointment}/create', [CashierController::class, 'create'])->name('cashier.create');
        Route::post('cashier/invoices', [CashierController::class, 'store'])->name('cashier.store');

        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('doctors', [DoctorController::class, 'index'])->name('doctors.index');
        Route::get('doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
        Route::post('doctors', [DoctorController::class, 'store'])->name('doctors.store');

        Route::get('schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('reports/{report}/csv', [ReportController::class, 'csv'])->name('reports.csv');
    });
});