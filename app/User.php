<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function lga()
    {
        return $this->belongsTo(\App\LocalGovernment::class);
    }

    public function state()
    {
        return $this->belongsTo(\App\State::class);
    }

    public function candidate()
    {
        return $this->hasOne(\App\Candidate::class);
    }

    public function pinsCreated()
    {
        return $this->hasMany(\App\RegistrationPin::class, "created_by");
    }

    public function pinUsed()
    {
        return $this->hasOne(\App\RegistrationPin::class, 'used_by');
    }

    public function officerRegisterFromOfficer()
    {
        return $this->hasMany(\App\OfficerRegister::class, 'officer_id');
    }

    public function officerRegisterFromVoter()
    {
        return $this->hasMany(\App\OfficerRegister::class, 'voter_id');
    }


    #Accessors and Mutators

    public function getDobAttribute($value)
    {
        return [
            "dob_string" => Carbon::parse($value)->format('jS M, Y'),
            "age"        => Carbon::parse($value)->age,
            "dob"        => $value
        ];
    }

    public function getCreatedAtAttribute($value)
    {
        return [
            "created_at"         => Carbon::parse($value)->format('jS M, Y'),
            "created_at_default" => $value
        ];
    }


}
