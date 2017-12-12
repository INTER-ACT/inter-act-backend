<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatableRatingAspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratable_rating_aspects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rating_aspect_id')->unsigned();
            $table->morphs('ratable');
            $table->timestamps();

            $table->foreign('rating_aspect_id')->references('id')-> on('rating_aspects');
            $table->unique(['rating_aspect_id', 'ratable_id', 'ratable_type'], 'unique_index_jointable_ratable_rating_aspects');
        });

        Schema::create('rating_aspect_rating', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('ratable_rating_aspect_id')->unsigned();
            $table->timestamps();

            $table->foreign('ratable_rating_aspect_id')->references('id')-> on('ratable_rating_aspects');
            $table->foreign('user_id')->references('id')-> on('users');
            $table->primary(['user_id', 'ratable_rating_aspect_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratable_rating_aspects');
        Schema::dropIfExists('rating_aspect_rating');
    }
}
