<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->unique('name_unique');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->morphs('taggable');
            $table->integer('tag_id')->unsigned();

            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taggables', function (Blueprint $table) {
            $table->dropForeign('taggables_tag_id_foreign');
        });
        
        Schema::dropIfExists('tags');
        Schema::dropIfExists('taggables');
    }
}
