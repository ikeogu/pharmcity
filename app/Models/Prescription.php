<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{

    use HasUlids;

    protected $fillable = [
        'prescription_number',
        'customer_id',
        'doctor_name',
        'doctor_license',
        'prescription_date',
        'expiry_date',
        'diagnosis',
        'status',
        'image_path',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'expiry_date' => 'date',
    ];


    public function scopeActive($query){

        return $query->where('status', 'active');
    }
}
