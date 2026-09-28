<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_minutes',
        'max_patients_per_slot',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_minutes' => 'integer',
            'max_patients_per_slot' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'schedule_id');
    }

    public function dayName(): string
    {
        $names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $names[(int) $this->day_of_week] ?? '-';
    }

    public function slots(): array
    {
        $slots = [];
        try {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
        } catch (\Throwable $e) {
            return $slots;
        }

        $step = max(1, (int) $this->slot_minutes);
        $cur = $start->copy();
        while ($cur->lt($end)) {
            $slots[] = $cur->format('H:i');
            $cur->addMinutes($step);
        }

        return $slots;
    }

    public function slotCount(): int
    {
        return count($this->slots());
    }
}