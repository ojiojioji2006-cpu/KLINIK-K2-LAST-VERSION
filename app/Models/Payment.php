<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const METHOD_CASH = 'tunai';
    public const METHOD_CARD = 'kartu';
    public const METHOD_TRANSFER = 'transfer';
    public const METHOD_INSURANCE = 'asuransi';
    public const METHOD_OTHER = 'lainnya';

    protected $fillable = [
        'invoice_id',
        'amount',
        'method',
        'receipt_number',
        'paid_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}