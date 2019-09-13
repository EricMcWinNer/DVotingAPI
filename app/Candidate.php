<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{

    protected $touches = ['user'];

    public function party()
    {
        return $this->belongsTo(\App\Party::class);
    }

    public function election()
    {
        return $this->belongsTo(\App\Election::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
