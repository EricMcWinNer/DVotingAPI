<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vote extends Model
{
    use SoftDeletes;
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function party()
    {
        return $this->belongsTo(\App\Party::class);
    }

    public function election()
    {
        return $this->belongsTo(\App\Election::class);
    }
}
