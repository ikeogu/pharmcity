<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        "name",
        "symbol",
        "logo",
        "active",
        "supported",
        "code"
    ];

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    public function scopeGetActive($q, $active)
    {
        return $q->where("active", $active);
    }


    public function scopeFindByCode($r, $search)
    {
        return $r->where("code", $search);
    }
}
