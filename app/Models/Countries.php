<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
     use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        "iso_code",
        "iso3_code",
        "flag_url",
        'status',
        'api_name',
        'region',
        'phone_code'
    ];

    protected $table = 'countries';

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function states(){
        return $this->hasMany(State::class,'country_id');
    }

    public function scopeFindByIso2($q,$code){
        return $q->where("iso_code",$code);
    }

    public function scopeFindByIso3($q,$code){
        return $q->where("iso3_code",$code);
    }
}
