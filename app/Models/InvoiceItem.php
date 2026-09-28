<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    public const TYPE_SERVICE = 'service';
    public const TYPE_MEDICINE = 'medicine';
    public const TYPE_OTHER = 'other';

    // reference_id sengaja TANPA relasi Eloquent: polymorphic manual
    // (services.id jika service, medicines.id jika medicine, null jika other).
    // Pemetaan dilakukan di level aplikasi saat invoice dibuat.

    protected $fillable = [
        'invoice_id',
        'item_type',
        'reference_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}