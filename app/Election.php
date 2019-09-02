<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Election extends Model
{
    use SoftDeletes;

    public function candidates()
    {
        return $this->hasMany(\App\Candidate::class);
    }
}
