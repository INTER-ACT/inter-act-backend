<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLawTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_texts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('law_id', 191)->unique();
            $table->string('articleParagraphUnit', 64)->default('-');
            $table->string('title')->default('-');
            $table->text('content')->default('-');
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
        Schema::dropIfExists('law_texts');
    }
}
