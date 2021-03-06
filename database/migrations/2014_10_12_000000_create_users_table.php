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
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->string('username', 64)->unique();
            $table->string('email', 254)->unique();
            $table->text('password');
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->boolean('is_male');
            $table->integer('postal_code')->unsigned();
            $table->string('city', 254);
            $table->string('job', 254);
            $table->string('graduation', 254);
            $table->integer('year_of_birth')->unsigned();
            $table->rememberToken();
            //$table->text('pending_password')->nullable();
            //$table->string('pending_token', 40)->nullable()->unique();
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
