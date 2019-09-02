<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RegistrationPin extends Model
{
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(\App\User::class, 'used_by');
    }

    public function getCreatedAtAttribute($value)
    {
        return [
            "created_at"         => Carbon::parse($value)->format('jS M, Y'),
            "created_at_default" => $value
        ];
    }

}
