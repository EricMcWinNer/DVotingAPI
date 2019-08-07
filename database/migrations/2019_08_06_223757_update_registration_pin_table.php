<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRegistrationPinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('registration_pins', function (Blueprint $table) {
           $table->bigInteger('used_by')->nullable()->change();
           $table->dateTime('date_used')->nullable()->change();
           $table->bigInteger('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('registration_pins', function (Blueprint $table) {
            $table->bigInteger('used_by')->nullable(false)->change();
            $table->dateTime('date_used')->nullable(false)->change();
            $table->dropColumn('created_by');
        });
    }
}
