<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditorTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'creditor_id',
        'type',
        'amount',
        'description',
        'running_balance',
    ];

    public function creditor()
    {
        return $this->belongsTo(Creditor::class);
    }
}
