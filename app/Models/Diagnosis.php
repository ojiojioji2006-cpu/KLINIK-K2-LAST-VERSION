<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    use HasFactory;

    public const TYPE_PRIMARY = 'primer';
    public const TYPE_SECONDARY = 'sekunder';

    protected $fillable = [
        'medical_record_id',
        'icd_code',
        'description',
        'type',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}