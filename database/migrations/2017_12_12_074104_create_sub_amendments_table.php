<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubAmendmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_amendments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amendment_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->text('updated_text');
            $table->text('explanation');
            $table->enum('status', ['pending', 'accepted', 'rejected']);    //TODO: document possible values
            $table->integer('amendment_version')->unsigned();
            $table->timestamp('handled_at');
            $table->text('handle_explanation');
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
        Schema::dropIfExists('sub_amendments');
    }
}
