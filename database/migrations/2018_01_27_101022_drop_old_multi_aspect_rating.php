<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropOldMultiAspectRating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ratable_rating_aspects', function (Blueprint $table) {
            $table->dropForeign('ratable_rating_aspects_rating_aspect_id_foreign');
        });
        Schema::table('rating_aspect_rating', function (Blueprint $table) {
            $table->dropForeign('rating_aspect_rating_ratable_rating_aspect_id_foreign');
            $table->dropForeign('rating_aspect_rating_user_id_foreign');
        });

        Schema::dropIfExists('ratable_rating_aspects');
        Schema::dropIfExists('rating_aspect_rating');
        Schema::dropIfExists('rating_aspects');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('rating_aspects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64);
            $table->timestamps();
        });
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
}
