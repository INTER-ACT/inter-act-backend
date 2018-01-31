<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_users', function (Blueprint $table) {
            $table->string('validation_token', 40)->unique();
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
            $table->timestamps();

            $table->primary('validation_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pending_users');
    }
}
