<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


//POTENTIAL TROUBLE MAKER
class UpdateRegistrationPinTableAgain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registration_pins', function (Blueprint $table) {
            $table->string('content', 199)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registration_pins', function (Blueprint $table) {
            $table->dropUnique('content');
        });
    }
}
