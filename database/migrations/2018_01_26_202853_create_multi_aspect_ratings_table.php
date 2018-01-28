<?php

use App\MultiAspectRating;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMultiAspectRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MultiAspectRating::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->morphs('ratable');
            $table->boolean(MultiAspectRating::ASPECT1_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT2_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT3_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT4_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT5_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT6_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT7_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT8_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT9_COLUMN);
            $table->boolean(MultiAspectRating::ASPECT10_COLUMN);
            $table->timestamps();

            //$table->primary(['user_id', 'ratable_id', 'ratable_type']);
            $table->foreign('user_id', 'ma_rating_user_id_foreign')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('multi_aspect_ratings', function(Blueprint $table){
            $table->dropForeign('ma_rating_user_id_foreign');
        });
        Schema::dropIfExists('multi_aspect_ratings');
    }
}
