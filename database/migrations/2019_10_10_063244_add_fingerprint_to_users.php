<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFingerprintToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->binary('left_index');
            $table->binary('left_thumb');
            $table->binary('right_index');
            $table->binary('right_thumb');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('left_index');
            $table->dropColumn('left_thumb');
            $table->dropColumn('right_index');
            $table->dropColumn('right_thumb');
        });
    }
}
