<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PositionFieldInRegistrationPins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE registration_pins CHANGE COLUMN user_type 
                        user_type varchar(255) AFTER id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE registration_pins CHANGE COLUMN
                  user_type user_type varchar(255) AFTER created_by');
    }
}
