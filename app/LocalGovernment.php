<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocalGovernment extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "lgas";

    public function state()
    {
        return $this->belongsTo(\App\State::class, 'state_id', 'id', 'state_id');
    }

    public function users()
    {
        return $this->hasMany(\App\User::class, "lga_id");
    }
}
