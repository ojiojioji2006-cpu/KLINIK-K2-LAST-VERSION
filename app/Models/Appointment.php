<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'menunggu';
    public const STATUS_IN_EXAM = 'dalam_pemeriksaan';
    public const STATUS_DONE = 'selesai';
    public const STATUS_CANCELLED = 'batal';
    public const STATUS_NO_SHOW = 'tidak_hadir';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'schedule_id',
        'appointment_date',
        'appointment_time',
        'status',
        'chief_complaint',
        'notes',
        'created_by',
        'called_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'called_at' => 'datetime',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}