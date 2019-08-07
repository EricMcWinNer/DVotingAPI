<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->bigInteger('lga_id');
            $table->string('address1');
            $table->string('address2');
            $table->string('picture')->unique();
            $table->dateTime('dob');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone_number');
            $table->json('roles');
            $table->enum('marital_status', ['married', 'single', 'divorced', 'widowed']);
            $table->string('occupation');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
