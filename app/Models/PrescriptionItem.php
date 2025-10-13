<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasUlids;


    protected $fillable = [
        'prescription_id',
        'drug_id',
        'drug_name',
        'dosage',
        'quantity_prescribed',
        'quantity_dispensed',
        'frequency',
        'instructions',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'quantity_dispensed' => 'integer',
    ];
}
