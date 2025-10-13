<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable = [
        'hospital_id',
        'phone',
        'email',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'gender',
        'additional_details',
        'registration_id',
        'service_location_id',
        'unit_id',
        'consultant_id',
        'patient_type',
        'payment_party',
        'permanent_address',
        'city',
        'state',
        'zipcode',
        'nationality',
        'occupation',
        'marital_status',
        'religion',
        'nok_full_name',
        'nok_phone',
        'nok_address',
        'nok_relationship',
        'nok_occupation',
        'nok_gender',
        'status',
        'last_seen',
        'registered_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'last_seen' => 'datetime',
        'registered_at' => 'datetime',
    ];

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim("{$this->title} {$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getAgeAttribute(): int
    {
        return $this->dob->age;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Relationships
    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('hospital_id', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByConsultant($query, $consultantId)
    {
        return $query->where('consultant_id', $consultantId);
    }

    // Generate Hospital ID
    public static function generateHospitalId(): string
    {
        $prefix = 'PRIV';
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
        return "{$prefix}/{$random}";
    }

    // Boot method to auto-generate hospital_id
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->hospital_id)) {
                $patient->hospital_id = self::generateHospitalId();
            }

            if (empty($patient->registered_at)) {
                $patient->registered_at = now();
            }
        });
    }
}
