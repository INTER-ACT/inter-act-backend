<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->morphs('commentable');
            $table->text('content');
            $table->integer('sentiment');
            $table->timestamps();
        });

        Schema::create('comment_ratings', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('comment_id')->unsigned();
            $table->boolean('rating_score');
            $table->timestamps();

            $table->primary(['user_id', 'comment_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('comment_id')->references('id')->on('comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comment_ratings');
    }
}
