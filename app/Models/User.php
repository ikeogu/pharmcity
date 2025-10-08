<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUlids, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'username',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'city_id',
        'state_id',
        'country_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Countries::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
    
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    protected static function boot()
    {

        parent::boot();

        static::creating(function ($user) {
            $user->username = self::generateUsername($user->first_name, $user->last_name);
        });
    }

    protected static function generateUsername(string $firstName, string $lastName): string
    {
        // 1. Create base username
        $base = Str::slug(Str::lower($firstName . $lastName), '');

        // 2. Ensure minimum username length
        $base = substr($base, 0, 15);

        // 3. Initialize username
        $username = $base;

        // 4. Counter for duplicates
        $counter = 1;

        // 5. Check if username exists, if yes, append increment
        while (self::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
