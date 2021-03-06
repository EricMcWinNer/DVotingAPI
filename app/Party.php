<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $table = "parties";

    public function candidates()
    {
        return $this->hasMany(\App\Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(\App\Vote::class);
    }
}
