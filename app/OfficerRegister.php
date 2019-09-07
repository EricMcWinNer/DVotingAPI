<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OfficerRegister
 * @package App
 */
class OfficerRegister extends Model
{
    /**
     * @var string
     */
    protected $table = "officer_register";

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voter()
    {
        return $this->belongsTo(User::class, 'voter_id');
    }
}
